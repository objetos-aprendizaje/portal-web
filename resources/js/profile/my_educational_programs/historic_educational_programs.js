import {
    apiFetch,
    updatePagination,
    getDateShort,
    handlePagination,
    accordionControls,
} from "../../app.js";

document.addEventListener("DOMContentLoaded", function () {
    initHandlers();
    getHistoricEducationalPrograms();
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

        if (classClicked.contains("btn-action-course")) {
            const courseUid = event.target.dataset.course_uid;
            accessCourse(courseUid);
        }
    });
}

function handlePaginationEducationalPrograms() {
    const containerPagination = document.getElementById(
        "pagination-historic-educational-programs"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getHistoricEducationalPrograms(pageNumber, undefined, search);
    });
}

function getHistoricEducationalPrograms(
    page = 1,
    items_per_page = 3,
    search = null
) {
    const params = {
        method: "POST",
        url: "/profile/my_educational_programs/historic/get",
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

        const containerHistoricEducationalProgramsPagination =
            document.getElementById("pagination-historic-educational-programs");

        if (response.total <= items_per_page) {
            containerHistoricEducationalProgramsPagination.classList.add(
                "hidden"
            );
        } else {
            updatePagination(
                containerHistoricEducationalProgramsPagination,
                response.current_page,
                response.last_page
            );
        }

        const educationalPrograms = response.data;

        let educationalProgramsHistoricContainer = document.getElementById(
            "educational-programs-historic-container"
        );

        let noEducationalProgramsHistoric = document.getElementById(
            "no-educational-programs-historic"
        );

        if (educationalPrograms.length) {
            educationalProgramsHistoricContainer.classList.remove("hidden");
            noEducationalProgramsHistoric.classList.add("hidden");
            const listEducationalProgramsHistoric = document.getElementById(
                "educational-programs-historic-list"
            );

            loadEducationalPrograms(
                response.data,
                listEducationalProgramsHistoric
            );

            accordionControls();
        } else {
            educationalProgramsHistoricContainer.classList.add("hidden");
            noEducationalProgramsHistoric.classList.remove("hidden");
        }
    });
}

function handleSearchEducationalPrograms() {
    const search = document.getElementById(
        "search-educational-program-input"
    ).value;

    getHistoricEducationalPrograms(1, 3, search);
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
        url: "/profile/my_educational_programs/historic/access_course",
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

        if (course.lms_url) {
            courseTemplateCloned
                .querySelector(".btn-action-container")
                .classList.remove("hidden");
        }

        let accordionBodyContainer = courseTemplateCloned.querySelector(
            ".accordion-body-container"
        );

        if (index === 0) {
            accordionBodyContainer.classList.add("accordion-uncollapsed");
        } else {
            accordionBodyContainer.classList.add("accordion-collapsed");
        }

        courseTemplateCloned.querySelector(
            ".btn-action-course"
        ).dataset.course_uid = course.uid;

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
