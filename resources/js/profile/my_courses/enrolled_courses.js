import {
    apiFetch,
    updatePagination,
    getDateShort,
    handlePagination,
    formatDate,
    accordionControls,
    fillRedsysForm,
} from "../../app.js";

document.addEventListener("DOMContentLoaded", function () {
    initHandlers();
    getEnrolledCourses();
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

    handlePaginationCourses();

    document.body.addEventListener("click", function (event) {
        const classClicked = event.target.classList;
        if (classClicked.contains("btn-blocked")) return;

        if (classClicked.contains("btn-action-course")) {
            const courseUid = event.target.dataset.course_uid;
            accessCourse(courseUid);
        }

        if (classClicked.contains("pay-term-btn")) {
            const paymentTermUid = event.target.dataset.payment_term_uid;
            payTerm(paymentTermUid);
        }
    });
}

function handlePaginationCourses() {
    const containerPagination = document.getElementById(
        "pagination-enrolled-courses"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getEnrolledCourses(pageNumber, undefined, search);
    });
}

function getEnrolledCourses(page = 1, items_per_page = 3, search = null) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/enrolled/get",
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

        const containerenrolledCoursesPagination = document.getElementById(
            "pagination-enrolled-courses"
        );

        if (response.total <= items_per_page) {
            containerenrolledCoursesPagination.classList.add("hidden");
        } else {
            updatePagination(
                containerenrolledCoursesPagination,
                response.current_page,
                response.last_page
            );
        }

        const courses = response.data;

        let coursesenrolledContainer = document.getElementById(
            "courses-enrolled-container"
        );

        let noCoursesenrolled = document.getElementById("no-courses-enrolled");

        if (courses.length) {
            coursesenrolledContainer.classList.remove("hidden");
            noCoursesenrolled.classList.add("hidden");
            const listCoursesenrolled = document.getElementById(
                "courses-enrolled-list"
            );

            loadCourses(response.data, listCoursesenrolled);
        } else {
            coursesenrolledContainer.classList.add("hidden");
            noCoursesenrolled.classList.remove("hidden");
        }

        accordionControls();
    });
}

function handleSearchCourses() {
    const search = document.getElementById("search-course-input").value;

    getEnrolledCourses(1, 3, search);
}

function getSearchInput() {
    const search = document.getElementById("search-course-input").value;
    return search;
}

function accessCourse(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/enrolled/access_course",
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
        });
    } else {
        noCoursesFound.classList.remove("hidden");
    }
}

function fillCourseTemplate(template, course) {
    fillCourseDetails(template, course);
    setCourseActionBtn(template, course);
    setCourseIndicators(template, course);

    if (course.payment_mode == "INSTALLMENT_PAYMENT") {
        setPaymentTerms(template, course);
    }
}

function setPaymentTerms(template, course) {
    // Mostrar el contenedor de términos de pago
    const paymentTermsContainer = template.querySelector(
        ".payment-terms-container"
    );
    paymentTermsContainer.classList.remove("hidden");

    // Obtener la plantilla de términos de pago
    const paymentTermsTemplate = document.getElementById("payment-terms");

    // Iterar sobre los términos de pago del curso
    course.payment_terms.forEach((paymentTerm) => {
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

        if (paymentTerm.user_payment?.is_paid) {
            const paymentTermsLabels =
                paymentTermsCloned.querySelectorAll(".paid-term-label");
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

function payTerm(paymentTermUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/enrolled/pay_term",
        body: {
            paymentTermUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then((response) => {
        console.log(response);
        fillRedsysForm(response.redsysParams);
    });
}

function fillCourseDetails(template, course) {
    template.querySelector(".title").innerHTML = course.title;
    template.querySelector(".image").src =
        window.backendUrl + "/" + course.image_path;

    template.querySelectorAll(".course-link").forEach((link) => {
        link.href = `/course/${course.uid}`;
    });

    if (course.enrolling_start_date && course.enrolling_finish_date) {
        template.querySelector(".enrolling-date").classList.remove("hidden");
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
}

function setCourseActionBtn(template, course) {
    template.querySelector(".btn-action-course").dataset.course_uid =
        course.uid;

    if (course.status_code !== "DEVELOPMENT") {
        template
            .querySelector(".btn-action-course")
            .classList.add("btn-blocked");
    }
}

function setCourseIndicators(template, course) {
    let indicator = template.querySelector(".indicator");
    let indicatorLabel = template.querySelector(".indicator-label");

    if (["INSCRIPTION", "ENROLLING"].includes(course.status_code)) {
        indicator.classList.add("pending");
        indicatorLabel.innerHTML = "Disponible próximamente";
    } else if (course.status_code === "DEVELOPMENT") {
        indicator.classList.add("openned");
        indicatorLabel.innerHTML = "En realización";
    }
}
