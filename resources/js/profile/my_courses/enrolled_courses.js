import {
    apiFetch,
    updatePagination,
    getDateShort,
    handlePagination,
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
        const containerenrolledCoursesPagination = document.getElementById(
            "pagination-enrolled-courses"
        );

        updatePagination(
            containerenrolledCoursesPagination,
            response.current_page,
            response.last_page
        );

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

    if (course.status.code !== "DEVELOPMENT") {
        template
            .querySelector(".btn-action-course")
            .classList.add("btn-blocked");
    }
}

function setCourseIndicators(template, course) {
    let indicator = template.querySelector(".indicator");
    let indicatorLabel = template.querySelector(".indicator-label");

    if (["INSCRIPTION", "ENROLLING"].includes(course.status.code)) {
        indicator.classList.add("pending");
        indicatorLabel.innerHTML = "Disponible próximamente";
    } else if (course.status.code === "DEVELOPMENT") {
        indicator.classList.add("openned");
        indicatorLabel.innerHTML = "En realización";
    }
}
