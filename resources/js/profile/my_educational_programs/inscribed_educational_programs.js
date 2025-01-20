import {
    apiFetch,
    updateInputFile,
    getDateShort,
    updatePagination,
    handlePagination,
    accordionControls,
    fillRedsysForm,
    downloadFileBackend,
    moreOptionsBtnHandler,
} from "../../app.js";
import {
    hideModal,
    showModal,
    showModalConfirmation,
} from "../../modal_handler.js";

let educationalProgramsDocuments = [];
document.addEventListener("DOMContentLoaded", function () {
    getInscribedEducationalPrograms();
    initHandlers();
    updateInputFile();
    handlePaginationEducationalPrograms();
    moreOptionsBtnHandler();
});

function handlePaginationEducationalPrograms() {
    const containerPagination = document.getElementById(
        "pagination-inscribed-educational-programs"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getInscribedEducationalPrograms(pageNumber, undefined, search);
    });
}

function getSearchInput() {
    const search = document.getElementById(
        "search-educational-program-input"
    ).value;
    return search;
}

function initHandlers() {
    document
        .getElementById("search-educational-program-btn")
        .addEventListener("click", function () {
            handleSearchEdu();
        });

    document
        .getElementById("search-educational-program-input")
        .addEventListener("keyup", function (event) {
            if (event.key === "Enter") handleSearchEducationalPrograms();
        });

    document.body.addEventListener("click", function (event) {
        const classClicked = event.target.classList;

        if (classClicked.contains("btn-blocked")) return;

        if (classClicked.contains("documentation-btn")) {
            let educationalProgramUid =
                event.target.dataset.educational_program_uid;
            loadUploadFilesModal(educationalProgramUid);
        } else if (classClicked.contains("download-document")) {
            const documentUid = event.target.dataset.document_uid;
            downloadDocumentEducationalProgram(documentUid);
        } else if (classClicked.contains("upload-files")) {
            loadUploadFilesModal(courseUid);
        } else if (classClicked.contains("btn-action-educational-program")) {
            const educationalProgramUid =
                event.target.dataset.educational_program_uid;
            enrollEducationalProgram(educationalProgramUid);
        } else if (classClicked.contains("cancel-inscription-btn")) {
            const educationalProgramUid =
                event.target.closest(".more-options-btn").dataset
                    .educational_program_uid;

            showModalConfirmation(
                "Cancelar inscripción",
                "Vas a cancelar la inscripción a este programa formativo. ¿Deseas continuar?",
                "cancelInscription",
                []
            ).then((resultado) => {
                if (resultado) cancelInscription(educationalProgramUid);
            });
        }
    });

    document
        .getElementById("upload-documents-form")
        .addEventListener("submit", saveDocumentsCourse);
}

function handleSearchEducationalPrograms() {
    const search = document.getElementById(
        "search-educational-program-input"
    ).value;
    getInscribedEducationalPrograms(1, 3, search);
}

function cancelInscription(educationalProgramUid) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/inscribed/cancel_inscription",
        body: {
            educationalProgramUid: educationalProgramUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then(() => {
        getInscribedEducationalPrograms();
    });
}

function saveDocumentsCourse() {
    const formData = new FormData(this);

    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/save_documents_educational_program",
        body: formData,
        loader: true,
    };

    apiFetch(params).then((response) => {
        hideModal("upload-documents-modal");
        getInscribedEducationalPrograms();
    });
}

function enrollEducationalProgram(educationalProgramUid) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/inscribed/enroll_educational_program",
        body: {
            educationalProgramUid: educationalProgramUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        if (response.requirePayment) {
            fillRedsysForm(response.redsysParams);
        } else {
            getInscribedEducationalPrograms();
        }
    });
}

function setPaymentTerms(template, educationalProgram) {
    // Mostrar el contenedor de términos de pago
    const paymentTermsContainer = template.querySelector(
        ".payment-terms-container"
    );
    paymentTermsContainer.classList.remove("hidden");

    // Obtener la plantilla de términos de pago
    const paymentTermsTemplate = document.getElementById("payment-terms");

    // Iterar sobre los términos de pago del curso
    educationalProgram.payment_terms.forEach((paymentTerm) => {
        // Clonar la plantilla de términos de pago
        const paymentTermsCloned = paymentTermsTemplate.content.cloneNode(true);

        // Rellenar los datos del término de pago
        const paymentTermsNames =
            paymentTermsCloned.querySelectorAll(".payment-term-name");
        paymentTermsNames.forEach((paymentTermName) => {
            paymentTermName.textContent = paymentTerm.name;
        });

        paymentTermsCloned.querySelector(
            ".payment-term-date"
        ).textContent = `${getDateShort(
            paymentTerm.start_date
        )} - ${getDateShort(paymentTerm.finish_date)}`;

        paymentTermsCloned.querySelector(
            ".payment-term-date-mobile"
        ).textContent = `${formatDate(paymentTerm.start_date)} - ${formatDate(
            paymentTerm.finish_date
        )}`;

        const paymentTermsCosts =
            paymentTermsCloned.querySelectorAll(".payment-term-cost");
        paymentTermsCosts.forEach((paymentTermCost) => {
            paymentTermCost.textContent = paymentTerm.cost;
        });

        const startDate = new Date(paymentTerm.start_date);
        const finishDate = new Date(paymentTerm.finish_date);
        const currentDate = new Date();

        if (paymentTerm.user_payment) {
            const paymentTermsLabels = paymentTermsCloned.querySelectorAll(
                ".payment-term-label"
            );
            paymentTermsLabels.forEach((paymentTermLabel) => {
                paymentTermLabel.classList.remove("hidden");
            });
        } else if (startDate < currentDate && finishDate > currentDate) {
            const payTermsBtns =
                paymentTermsCloned.querySelectorAll(".pay-term-btn");

            payTermsBtns.forEach((payTermBtn) => {
                payTermBtn.classList.remove("hidden");
                payTermBtn.dataset.payment_term_uid = paymentTerm.uid;
            });
        }

        // Añadir el término de pago clonado al contenedor
        paymentTermsContainer
            .querySelector(".payment-terms-list")
            .appendChild(paymentTermsCloned);
    });
}

function getInscribedEducationalPrograms(
    page = 1,
    items_per_page = 3,
    search = null
) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/inscribed/get",
        body: {
            page,
            items_per_page,
            search,
        },
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {
        document.getElementById("number-total-results").innerHTML =
            response.total;

        const containerInscribedEducationalProgramsPagination =
            document.getElementById(
                "pagination-inscribed-educational-programs"
            );

        if (response.total <= items_per_page) {
            containerInscribedEducationalProgramsPagination.classList.add(
                "hidden"
            );
        } else {
            updatePagination(
                containerInscribedEducationalProgramsPagination,
                response.current_page,
                response.last_page
            );
        }

        const educationalPrograms = response.data;

        let educationalProgramsInscribedContainer = document.getElementById(
            "educational-programs-inscribed-container"
        );

        let noEducationalProgramsInscribed = document.getElementById(
            "no-educational-programs-inscribed"
        );

        if (educationalPrograms.length) {
            educationalProgramsInscribedContainer.classList.remove("hidden");
            noEducationalProgramsInscribed.classList.add("hidden");
            const listEducationalProgramsInscribed = document.getElementById(
                "educational-programs-inscribed-list"
            );

            loadEducationalPrograms(
                response.data,
                listEducationalProgramsInscribed
            );
            accordionControls();
        } else {
            educationalProgramsInscribedContainer.classList.add("hidden");
            noEducationalProgramsInscribed.classList.remove("hidden");
        }
    });
}

function loadEducationalPrograms(educationalPrograms, container) {
    let educationalProgramTemplate = document.getElementById(
        "educational-program-template"
    );

    let noEducationalProgramsFound = document.getElementById(
        "no-educational-programs-found"
    );

    container.innerHTML = "";

    if (educationalPrograms.length) {
        noEducationalProgramsFound.classList.add("hidden");
        educationalPrograms.forEach((educationalProgram) => {
            let templateEducationalProgramCloned =
                educationalProgramTemplate.content.cloneNode(true);

            fillEducationalProgramTemplate(
                templateEducationalProgramCloned,
                educationalProgram
            );

            container.appendChild(
                document.importNode(templateEducationalProgramCloned, true)
            );

            // Concatenamos al array de documentos pendientes de subir
            let educationalProgramDocument = {
                evaluationCriteria: educationalProgram.evaluation_criteria,
                educationalProgramDocuments:
                    educationalProgram.educational_program_documents,
            };

            educationalProgramsDocuments[educationalProgram.uid] =
                educationalProgramDocument;
        });
    } else {
        noEducationalProgramsFound.classList.remove("hidden");
    }
}

function fillEducationalProgramTemplate(template, educationalProgram) {
    fillEducationalProgramDetails(template, educationalProgram);
    fillEducationalProgramCourses(template, educationalProgram);
    setEducationalProgramDocumentsBtn(template, educationalProgram);
    setEducationalProgramEnrollingBtn(template, educationalProgram);
    setIndicatorsStatuses(template, educationalProgram);

    if (educationalProgram.payment_mode == "INSTALLMENT_PAYMENT") {
        setPaymentTerms(template, educationalProgram);
    }
}

function fillEducationalProgramDetails(template, educationalProgram) {
    template.querySelector(".educational-program-uid").value =
        educationalProgram.uid;

    template.querySelectorAll(".title").forEach((title) => {
        title.innerHTML = educationalProgram.name;
    });

    template.querySelector(".image").src =
        window.backendUrl + "/" + educationalProgram.image_path;

    if (
        educationalProgram.enrolling_start_date &&
        educationalProgram.enrolling_finish_date
    ) {
        template
            .querySelector(".enrolling-dates-section")
            .classList.remove("hidden");
        template
            .querySelector(".enrolling-date")
            .querySelector(".date").innerHTML = `${getDateShort(
            educationalProgram.enrolling_start_date
        )} - ${getDateShort(educationalProgram.enrolling_finish_date)}`;
    }

    template
        .querySelector(".realization-date")
        .querySelector(".date").innerHTML = `${getDateShort(
        educationalProgram.realization_start_date
    )} - ${getDateShort(educationalProgram.realization_finish_date)}`;

    template.querySelector(".educational_program_uid").value =
        educationalProgram.uid;
    template.querySelector(
        ".btn-action-educational-program"
    ).dataset.educational_program_uid = educationalProgram.uid;

    template.querySelectorAll(".educational-program-link").forEach((link) => {
        link.href = `/educational_program/${educationalProgram.uid}`;
    });

    template.querySelector(
        ".documentation-btn"
    ).dataset.educational_program_uid = educationalProgram.uid;

    const moreOptionsButtons = template.querySelectorAll(".more-options-btn");
    moreOptionsButtons.forEach((button) => {
        button.dataset.educational_program_uid = educationalProgram.uid;
    });
}

function fillEducationalProgramCourses(template, educationalProgram) {
    let coursesContainer = template.querySelector(".courses-container");

    educationalProgram.courses.forEach((course) => {
        let courseTemplate = document.getElementById("course-template");
        let courseTemplateCloned = courseTemplate.content.cloneNode(true);

        courseTemplateCloned.querySelector(".course-title").innerHTML =
            course.title;
        courseTemplateCloned.querySelector(".course-description").innerHTML =
            course.description;
        courseTemplateCloned.querySelector(".course-ects-workload").innerHTML =
            course.ects_workload;

        coursesContainer.appendChild(
            document.importNode(courseTemplateCloned, true)
        );
    });
}

function setEducationalProgramDocumentsBtn(template, educationalProgram) {
    let documentationBtn = template.querySelector(".documentation-btn");

    if (!educationalProgram.educational_program_documents.length) {
        template
            .querySelector(".documentation-btn-container")
            .classList.add("hidden");
    }

    documentationBtn.innerHTML = checkPendingUploadDocuments(educationalProgram)
        ? "Documentación pendiente"
        : "Ver documentación";

    if (!checkPendingUploadDocuments(educationalProgram)) {
        template
            .querySelector(".require-documentation")
            .classList.add("hidden");
    }
}

function setEducationalProgramEnrollingBtn(template, educationalProgram) {
    let btnActionEducationalProgram = template.querySelector(
        ".btn-action-educational-program"
    );
    let labelEnrollingBtn = "Matricularse";
    if (educationalProgram.cost && educationalProgram.cost > 0) {
        labelEnrollingBtn += ` (${educationalProgram.cost}€)`;
        btnActionEducationalProgram.innerHTML = labelEnrollingBtn;
    }

    if (
        educationalProgram.status_code !== "ENROLLING" ||
        educationalProgram.acceptance_status !== "ACCEPTED"
    ) {
        btnActionEducationalProgram.classList.add("btn-blocked");
    }
}

function setIndicatorsStatuses(template, educationalProgram) {
    if (educationalProgram.validate_student_registrations) {
        setIndicatorStudentStatus(template, educationalProgram);
    } else {
        setIndicatorEducationalProgramStatus(template, educationalProgram);
    }
}

function setIndicatorStudentStatus(template, educationalProgram) {
    template
        .querySelector(".indicator-student-status-section")
        .classList.remove("hidden");

    let indicatorStudentStatus = template.querySelector(
        ".indicator-student-status"
    );

    let indicatorStudentStatusLabel = template.querySelector(
        ".indicator-student-status-label"
    );

    if (educationalProgram.acceptance_status === "ACCEPTED") {
        indicatorStudentStatus.classList.add("openned");
        indicatorStudentStatusLabel.innerHTML = "Aprobado";
        setIndicatorEducationalProgramStatus(template, educationalProgram);
    } else if (educationalProgram.acceptance_status === "PENDING") {
        indicatorStudentStatus.classList.add("pending");
        indicatorStudentStatusLabel.innerHTML = "Pendiente de aprobación";
    } else if (educationalProgram.acceptance_status === "REJECTED") {
        indicatorStudentStatus.classList.add("soon");
        indicatorStudentStatusLabel.innerHTML = "No aprobado";
    }
}

function setIndicatorEducationalProgramStatus(template, educationalProgram) {
    template
        .querySelector(".indicator-educational-program-status-section")
        .classList.remove("hidden");

    let indicatorEducationalProgramStatus = template.querySelector(
        ".indicator-educational-program-status"
    );

    let indicatorEducationalProgramStatusLabel = template.querySelector(
        ".indicator-educational-program-status-label"
    );

    if (educationalProgram.status_code == "ENROLLING") {
        indicatorEducationalProgramStatus.classList.add("openned");
        indicatorEducationalProgramStatusLabel.innerHTML =
            "Listo para matriculación";
    } else if (educationalProgram.status_code == "INSCRIPTION") {
        indicatorEducationalProgramStatus.classList.add("pending");
        indicatorEducationalProgramStatusLabel.innerHTML =
            "Pendiente de matriculación";
    }
}

function checkPendingUploadDocuments(educationalProgram) {
    let pendingUploadDocuments = false;

    educationalProgram.educational_program_documents.forEach(
        (educationalProgramDocument) => {
            if (
                !educationalProgramDocument.educational_program_student_document
            )
                pendingUploadDocuments = true;
        }
    );

    return pendingUploadDocuments;
}

function loadUploadFilesModal(educationalProgramUid) {
    // Sacamos los documentos del curso seleccionado
    const educationalProgramDocumentsFiltered =
        educationalProgramsDocuments[educationalProgramUid];

    const documentTemplate = document.getElementById("document-template");

    const documentContainer = document.getElementById("documents-container");

    documentContainer.innerHTML = "";

    document.getElementById("documents-modal-educational-program-uid").value =
        educationalProgramUid;
    document.getElementById("evaluation-criteria").innerHTML =
        educationalProgramDocumentsFiltered.evaluationCriteria;

    educationalProgramDocumentsFiltered.educationalProgramDocuments.forEach(
        (documentEducationalProgram) => {
            let templateCloned = documentTemplate.content.cloneNode(true);

            let templateFilled = fillDocumentTemplate(
                templateCloned,
                documentEducationalProgram
            );

            documentContainer.appendChild(
                document.importNode(templateFilled, true)
            );
        }
    );

    showModal("upload-documents-modal", "Subir archivos");
}

function fillDocumentTemplate(template, document) {
    template.querySelector(".document-name").innerHTML = document.document_name;
    template.querySelector(".document-input").id = document.uid;
    template.querySelector(".document-input").name = document.uid;

    if (document.educational_program_student_document) {
        template.querySelector(".download-document").classList.remove("hidden");
        template.querySelector(".download-document").dataset.document_uid =
            document.educational_program_student_document.uid;
    }

    template.querySelector(".document-input-label").htmlFor = document.uid;
    return template;
}

/**
 *
 * @param {*} documentUid
 * creación del token para la descarga del documento
 */
function downloadDocumentEducationalProgram(documentUid) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/inscribed/download_document_educational_program",
        body: {
            educational_program_document_uid: documentUid,
        },
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {
        downloadFileBackend(response.token);
    });
}
