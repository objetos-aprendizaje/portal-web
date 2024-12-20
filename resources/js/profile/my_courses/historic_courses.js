import {
    apiFetch,
    updatePagination,
    getDateShort,
    handlePagination,
} from "../../app.js";

document.addEventListener("DOMContentLoaded", function () {
    initHandlers();
    getHistoricCourses();
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

        if (
            classClicked.contains("btn-action-course") &&
            !classClicked.contains("btn-blocked")
        ) {
            const courseUid = event.target.dataset.course_uid;
            accessCourse(courseUid);
        }
    });
}

function handlePaginationCourses() {
    const containerPagination = document.getElementById(
        "pagination-historic-courses"
    );

    handlePagination(containerPagination, (pageNumber) => {
        const search = getSearchInput();
        getHistoricCourses(pageNumber, undefined, search);
    });
}

function getHistoricCourses(page = 1, items_per_page = 3, search = null) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/historic/get",
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

        const containerHistoricCoursesPagination = document.getElementById(
            "pagination-historic-courses"
        );

        if (response.total <= items_per_page) {
            containerHistoricCoursesPagination.classList.add("hidden");
        } else {
            updatePagination(
                containerHistoricCoursesPagination,
                response.current_page,
                response.last_page
            );
        }

        const courses = response.data;

        let coursesHistoricContainer = document.getElementById(
            "courses-historic-container"
        );

        let noCoursesHistoric = document.getElementById("no-courses-historic");
        let coursesHistoricList = document.getElementById(
            "courses-historic-list"
        );

        if (courses.length) {
            coursesHistoricContainer.classList.remove("hidden");
            noCoursesHistoric.classList.add("hidden");

            loadCourses(response.data, coursesHistoricList);
        } else {
            coursesHistoricContainer.classList.add("hidden");
            noCoursesHistoric.classList.remove("hidden");
        }
    });
}

function handleSearchCourses() {
    const search = document.getElementById("search-course-input").value;

    getHistoricCourses(1, 3, search);
}

function getSearchInput() {
    const search = document.getElementById("search-course-input").value;
    return search;
}

function accessCourse(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/historic/access_course",
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

    let noCoursesFound = document.getElementById("no-courses-historic");

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
    template.querySelector(".title").innerHTML = course.title;
    template.querySelector(".image").src =
        window.backendUrl + "/" + course.image_path;

    template.querySelectorAll(".course-link").forEach((link) => {
        link.href = `/course/${course.uid}`;
    });

    template
        .querySelector(".realization-date")
        .querySelector(".date").innerHTML = `${getDateShort(
        course.realization_start_date
    )} - ${getDateShort(course.realization_finish_date)}`;

    if (course.lms_url) {
        template.querySelector(".btn-action-course").dataset.course_uid =
            course.uid;
    } else {
        template.querySelector(".separator").style.display = "none";
        template.querySelector(".btn-action-course-container").style.display =
            "none";
    }
}
