import {
    apiFetch,
    updateInputFile,
    getDateShort,
    updatePagination,
    handlePagination,
    downloadFileBackend,
    fillRedsysForm,
    moreOptionsBtnHandler,
} from "../../app.js";
import {
    hideModal,
    showModal,
    showModalConfirmation,
} from "../../modal_handler.js";

let courseDocuments = [];

document.addEventListener("DOMContentLoaded", function () {
    initHandlers();
    getInscribedCourses();
    updateInputFile();
    handlePaginationCourses();
    moreOptionsBtnHandler();
});

function initHandlers() {
    document
        .getElementById("search-course-btn")
        .addEventListener("click", function () {
            handleSearchCourses();
        });

    document
        .getElementById("search-course-input")
        .addEventListener("keyup", function (event) {
            if (event.key === "Enter") handleSearchCourses();
        });

    document.body.addEventListener("click", function (event) {
        const classClicked = event.target.classList;
        if (classClicked.contains("btn-blocked")) return;

        if (classClicked.contains("documentation-btn")) {
            const courseUid = event.target
                .closest(".course-block")
                .querySelector(".course-uid").value;
            loadUploadFilesModal(courseUid);
        } else if (classClicked.contains("download-document")) {
            const documentUid = event.target.dataset.document_uid;
            downloadDocumentCourse(documentUid);
        } else if (classClicked.contains("upload-files")) {
            const courseUid = event.target.dataset.course_uid;
            loadUploadFilesModal(courseUid);
        } else if (classClicked.contains("btn-action-course")) {
            const courseUid = event.target.dataset.course_uid;
            enrollCourse(courseUid);
        } else if (classClicked.contains("cancel-inscription-btn")) {
            const courseUid =
                event.target.closest(".more-options-btn").dataset.course_uid;

            showModalConfirmation(
                "Cancelar inscripción",
                "Vas a cancelar la inscripción a este curso. ¿Deseas continuar?",
                "cancelInscription",
                []
            ).then((resultado) => {
                if (resultado) cancelInscription(courseUid);
            });
        }
    });

    document
        .getElementById("upload-documents-form")
        .addEventListener("submit", saveDocumentsCourse);
}

function cancelInscription(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/inscribed/cancel_inscription",
        body: {
            course_uid: courseUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then(() => {
        getInscribedCourses();
    });
}

function saveDocumentsCourse() {
    const formData = new FormData(this);

    const params = {
        method: "POST",
        url: "/profile/inscribed_courses/save_documents_course",
        body: formData,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        hideModal("upload-documents-modal");
        getInscribedCourses();
    });
}

function enrollCourse(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/inscribed/enroll_course",
        body: {
            course_uid: courseUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        if (response.requirePayment) {
            fillRedsysForm(response.redsysParams);
        } else {
            getInscribedCourses();
        }
    });
}

function handleSearchCourses() {
    const search = document.getElementById("search-course-input").value;

    getInscribedCourses(1, 3, search);
}

function getSearchInput() {
    const search = document.getElementById("search-course-input").value;
    return search;
}

function handlePaginationCourses() {
    const containerPagination = document.getElementById(
        "pagination-inscribed-courses"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getInscribedCourses(pageNumber, undefined, search);
    });
}

function getInscribedCourses(page = 1, items_per_page = 3, search = null) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/inscribed/get",
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

        const containerInscribedCoursesPagination = document.getElementById(
            "pagination-inscribed-courses"
        );

        if (response.total <= items_per_page) {
            containerInscribedCoursesPagination.classList.add("hidden");
        } else {
            updatePagination(
                containerInscribedCoursesPagination,
                response.current_page,
                response.last_page
            );
        }

        const courses = response.data;

        let coursesInscribedContainer = document.getElementById(
            "courses-inscribed-container"
        );

        let noCoursesInscribed = document.getElementById(
            "no-courses-inscribed"
        );

        if (courses.length) {
            coursesInscribedContainer.classList.remove("hidden");
            noCoursesInscribed.classList.add("hidden");
            const listCoursesInscribed = document.getElementById(
                "courses-inscribed-list"
            );

            loadCourses(response.data, listCoursesInscribed);
        } else {
            coursesInscribedContainer.classList.add("hidden");
            noCoursesInscribed.classList.remove("hidden");
        }
    });
}

function loadCourses(courses, container) {
    let courseTemplate = document.getElementById("course-template");

    let noCoursesFound = document.getElementById("no-courses-found");

    container.innerHTML = "";

    if (courses.length) {
        noCoursesFound.classList.add("hidden");
        courses.forEach((course) => {
            let templateCourseCloned = courseTemplate.content.cloneNode(true);

            fillCourseTemplate(templateCourseCloned, course);

            container.appendChild(
                document.importNode(templateCourseCloned, true)
            );

            // Concatenamos al array de documentos pendientes de subir
            let courseDocument = {
                evaluationCriteria: course.evaluation_criteria,
                courseDocuments: course.course_documents,
            };

            courseDocuments[course.uid] = courseDocument;
        });
    } else {
        noCoursesFound.classList.remove("hidden");
    }
}

function fillCourseTemplate(template, course) {
    fillCourseDetails(template, course);
    setCourseDocumentsBtn(template, course);
    setCourseEnrollingBtn(template, course);
    setIndicatorsStatuses(template, course);
}

function fillCourseDetails(template, course) {
    template.querySelector(".course-uid").value = course.uid;

    template.querySelectorAll(".title").forEach((title) => {
        title.innerHTML = course.title;
    });

    template.querySelector(".image").src =
        window.backendUrl + "/" + course.image_path;

    if (course.enrolling_start_date && course.enrolling_finish_date) {
        template
            .querySelector(".enrolling-dates-section")
            .classList.remove("hidden");
        template
            .querySelector(".enrolling-date")
            .querySelector(".date").innerHTML = `${getDateShort(
            course.enrolling_start_date
        )} - ${getDateShort(course.enrolling_finish_date)}`;
    }

    template
        .querySelector(".realization-date")
        .querySelector(".date").innerHTML = `${getDateShort(
        course.realization_start_date
    )} - ${getDateShort(course.realization_finish_date)}`;

    template.querySelector(".course_uid").value = course.uid;
    template.querySelector(".btn-action-course").dataset.course_uid =
        course.uid;

    template.querySelectorAll(".course-link").forEach((link) => {
        link.href = `/course/${course.uid}`;
    });

    const moreOptionsButtons = template.querySelectorAll(".more-options-btn");
    moreOptionsButtons.forEach((button) => {
        button.dataset.course_uid = course.uid;
    });
}

function setCourseDocumentsBtn(template, course) {
    let documentationBtn = template.querySelector(".documentation-btn");

    if (!course.course_documents.length) {
        template
            .querySelector(".documentation-btn-container")
            .classList.add("hidden");
    }

    documentationBtn.innerHTML = checkPendingUploadDocuments(course)
        ? "Documentación pendiente"
        : "Ver documentación";

    if (!checkPendingUploadDocuments(course)) {
        template
            .querySelector(".require-documentation")
            .classList.add("hidden");
    }
}

function setCourseEnrollingBtn(template, course) {
    let btnActionCourse = template.querySelector(".btn-action-course");
    let labelEnrollingBtn = "Matricularse";
    if (course.cost && course.cost > 0) {
        labelEnrollingBtn += ` (${course.cost}€)`;
        btnActionCourse.innerHTML = labelEnrollingBtn;
    }

    if (
        course.status_code !== "ENROLLING" ||
        course.acceptance_status !== "ACCEPTED"
    ) {
        btnActionCourse.classList.add("btn-blocked");
    }
}

function setIndicatorsStatuses(template, course) {
    if (course.validate_student_registrations) {
        setIndicatorStudentStatus(template, course);
    } else {
        setIndicatorCourseStatus(template, course);
    }
}

function setIndicatorStudentStatus(template, course) {
    template
        .querySelector(".indicator-student-status-section")
        .classList.remove("hidden");

    let indicatorStudentStatus = template.querySelector(
        ".indicator-student-status"
    );

    let indicatorStudentStatusLabel = template.querySelector(
        ".indicator-student-status-label"
    );

    if (course.acceptance_status === "ACCEPTED") {
        indicatorStudentStatus.classList.add("openned");
        indicatorStudentStatusLabel.innerHTML = "Aprobado";
        setIndicatorCourseStatus(template, course);
    } else if (course.acceptance_status === "PENDING") {
        indicatorStudentStatus.classList.add("pending");
        indicatorStudentStatusLabel.innerHTML = "Pendiente de aprobación";
    } else if (course.acceptance_status === "REJECTED") {
        indicatorStudentStatus.classList.add("soon");
        indicatorStudentStatusLabel.innerHTML = "No aprobado";
    }
}

function setIndicatorCourseStatus(template, course) {
    template
        .querySelector(".indicator-course-status-section")
        .classList.remove("hidden");

    let indicatorCourseStatus = template.querySelector(
        ".indicator-course-status"
    );

    let indicatorCourseStatusLabel = template.querySelector(
        ".indicator-course-status-label"
    );

    if (course.status_code == "ENROLLING") {
        indicatorCourseStatus.classList.add("openned");
        indicatorCourseStatusLabel.innerHTML = "Listo para matriculación";
    } else if (course.status_code == "INSCRIPTION") {
        indicatorCourseStatus.classList.add("pending");
        indicatorCourseStatusLabel.innerHTML = "Pendiente de matriculación";
    }
}

function checkPendingUploadDocuments(course) {
    let pendingUploadDocuments = false;

    course.course_documents.forEach((course_document) => {
        if (!course_document.course_student_document)
            pendingUploadDocuments = true;
    });

    return pendingUploadDocuments;
}

function loadUploadFilesModal(courseUid) {
    // Sacamos los documentos del curso seleccionado
    const courseDocumentsFiltered = courseDocuments[courseUid];

    const documentTemplate = document.getElementById("document-template");

    const documentContainer = document.getElementById("documents-container");

    documentContainer.innerHTML = "";

    document.getElementById("documents-modal-course-uid").value = courseUid;
    document.getElementById("evaluation-criteria").innerHTML =
        courseDocumentsFiltered.evaluationCriteria;

    courseDocumentsFiltered.courseDocuments.forEach((documentCourse) => {
        let templateCloned = documentTemplate.content.cloneNode(true);

        let templateFilled = fillDocumentTemplate(
            templateCloned,
            documentCourse
        );

        documentContainer.appendChild(
            document.importNode(templateFilled, true)
        );
    });

    showModal("upload-documents-modal", "Subir archivos");
}

function fillDocumentTemplate(template, document) {
    template.querySelector(".document-name").innerHTML = document.document_name;
    template.querySelector(".document-input").id = document.uid;
    template.querySelector(".document-input").name = document.uid;

    if (document.course_student_document) {
        template.querySelector(".download-document").classList.remove("hidden");
        template.querySelector(".download-document").dataset.document_uid =
            document.course_student_document.uid;
    }

    template.querySelector(".document-input-label").htmlFor = document.uid;
    return template;
}

/**
 *
 * @param {*} documentCourseUid
 * Creación del token para la descarga del documento
 */
function downloadDocumentCourse(documentCourseUid) {
    const params = {
        method: "POST",
        url: "/profile/inscribed_courses/download_document_course",
        body: {
            course_document_uid: documentCourseUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        downloadFileBackend(response.token);
    });
}
