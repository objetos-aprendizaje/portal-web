import {
    apiFetch,
    updatePagination,
    getDateShort,
    handlePagination,
    accordionControls,
    formatDate,
    fillRedsysForm
} from "../../app.js";

document.addEventListener("DOMContentLoaded", function () {
    initHandlers();
    getEnrolledEducationalPrograms();
});

function initHandlers() {
    document
        .getElementById("search-educational-program-btn")
        .addEventListener("click", function () {
            handleSearchEducationalPrograms();
        });

    document
        .getElementById("search-educational-program-input")
        .addEventListener("keyup", function (event) {
            if (event.key === "Enter") handleSearchEducationalPrograms();
        });

    handlePaginationEducationalPrograms();

    document.body.addEventListener("click", function (event) {
        const classClicked = event.target.classList;
        if (classClicked.contains("btn-blocked")) return;

        if(classClicked.contains("btn-action-course")) {
            const courseUid = event.target.dataset.course_uid;
            accessCourse(courseUid);
        }

        if(classClicked.contains("pay-term-btn")) {
            const paymentTermUid = event.target.dataset.payment_term_uid;
            payTerm(paymentTermUid);
        }
    });
}

function handlePaginationEducationalPrograms() {
    const containerPagination = document.getElementById(
        "pagination-enrolled-educational-programs"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getEnrolledEducationalPrograms(pageNumber, undefined, search);
    });
}

function getEnrolledEducationalPrograms(
    page = 1,
    items_per_page = 3,
    search = null
) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/enrolled/get",
        body: {
            page,
            items_per_page,
            search,
        },
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {
        const containerenrolledEducationalProgramsPagination =
            document.getElementById("pagination-enrolled-educational-programs");

        updatePagination(
            containerenrolledEducationalProgramsPagination,
            response.current_page,
            response.last_page
        );

        const educationalPrograms = response.data;

        let educationalProgramsenrolledContainer = document.getElementById(
            "educational-programs-enrolled-container"
        );

        let noEducationalProgramsenrolled = document.getElementById(
            "no-educational-programs-enrolled"
        );

        if (educationalPrograms.length) {
            educationalProgramsenrolledContainer.classList.remove("hidden");
            noEducationalProgramsenrolled.classList.add("hidden");
            const listEducationalProgramsenrolled = document.getElementById(
                "educational-programs-enrolled-list"
            );

            loadEducationalPrograms(
                response.data,
                listEducationalProgramsenrolled
            );

            accordionControls();
        } else {
            educationalProgramsenrolledContainer.classList.add("hidden");
            noEducationalProgramsenrolled.classList.remove("hidden");
        }
    });
}

function handleSearchEducationalPrograms() {
    const search = document.getElementById(
        "search-educational-program-input"
    ).value;

    getEnrolledEducationalPrograms(1, 3, search);
}

function getSearchInput() {
    const search = document.getElementById(
        "search-educational-program-input"
    ).value;
    return search;
}

function accessCourse(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/enrolled/access_course",
        body: {
            courseUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        window.open(response.lmsUrl, "_blank");
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
        });
    } else {
        noEducationalProgramsFound.classList.remove("hidden");
    }
}

function fillEducationalProgramTemplate(template, educationalProgram) {
    fillEducationalProgramDetails(template, educationalProgram);
    fillEducationalProgramCourses(template, educationalProgram);
    setEducationalProgramIndicators(template, educationalProgram);

    if (educationalProgram.payment_mode == "INSTALLMENT_PAYMENT") {
        setPaymentTerms(template, educationalProgram.payment_terms);
    }
}

function payTerm(paymentTermUid) {

    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/enrolled/pay_term",
        body: {
            paymentTermUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        fillRedsysForm(response.redsysParams);
    });
}

function fillEducationalProgramDetails(template, educationalProgram) {
    template.querySelector(".title").innerHTML = educationalProgram.name;
    template.querySelector(".image").src =
        window.backendUrl + "/" + educationalProgram.image_path;

    template.querySelectorAll(".educational-program-link").forEach((link) => {
        link.href = `/educational_program/${educationalProgram.uid}`;
    });

    if (
        educationalProgram.enrolling_start_date &&
        educationalProgram.enrolling_finish_date
    ) {
        template.querySelector(".enrolling-date").classList.remove("hidden");
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
}

function fillEducationalProgramCourses(template, educationalProgram) {
    let coursesContainer = template.querySelector(".courses-container");

    educationalProgram.courses.forEach((course, index) => {
        let courseTemplate = document.getElementById("course-template");
        let courseTemplateCloned = courseTemplate.content.cloneNode(true);

        courseTemplateCloned.querySelector(".course-title").innerHTML =
            course.title;
        courseTemplateCloned.querySelector(".course-description").innerHTML =
            course.description;
        courseTemplateCloned.querySelector(".course-ects-workload").innerHTML =
            course.ects_workload;

        if (educationalProgram.status_code === "DEVELOPMENT" && course.lms_url) {
            courseTemplateCloned
                .querySelector(".btn-action-container")
                .classList.remove("hidden");
        }

        let accordionBodyContainer = courseTemplateCloned.querySelector(
            ".accordion-body-container"
        );

        if (index === 0) {
            accordionBodyContainer.classList.add("accordion-uncollapsed");
        }
        else {
            accordionBodyContainer.classList.add("accordion-collapsed");
        }

        courseTemplateCloned.querySelector(".btn-action-course").dataset.course_uid = course.uid;

        coursesContainer.appendChild(
            document.importNode(courseTemplateCloned, true)
        );
    });
}

function setEducationalProgramIndicators(template, educationalProgram) {
    let indicator = template.querySelector(".indicator");
    let indicatorLabel = template.querySelector(".indicator-label");

    if (["INSCRIPTION", "ENROLLING"].includes(educationalProgram.status_code)) {
        indicator.classList.add("pending");
        indicatorLabel.innerHTML = "Disponible próximamente";
    } else if (educationalProgram.status_code === "DEVELOPMENT") {
        indicator.classList.add("openned");
        indicatorLabel.innerHTML = "En realización";
    }
}

function setPaymentTerms(template, paymentTerms) {
    // Mostrar el contenedor de términos de pago
    const paymentTermsContainer = template.querySelector(
        ".payment-terms-container"
    );
    paymentTermsContainer.classList.remove("hidden");

    // Obtener la plantilla de términos de pago
    const paymentTermsTemplate = document.getElementById("payment-terms");

    // Iterar sobre los términos de pago del curso
    paymentTerms.forEach((paymentTerm) => {
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

        if (paymentTerm.user_payment && paymentTerm.user_payment.is_paid) {
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
