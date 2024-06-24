import {
    apiFetch,
    formatDate,
    updatePagination,
    handlePagination,
    handleNumPages,
} from "./app";

import { controlSlides } from "./slider";
// Carril por defecto
let currentLane = "courses-actived";
document.addEventListener("DOMContentLoaded", function () {
    handleLanes();
    if (window.userUid) {
        getActiveCourses();
        handlePaginationLanes();
        handleLaneChange();

        // Carriles personalizados
        handleScrollCustomLanes();
        toggleScrollButtons();
        initHandlersCustomLanes();

    }

    controlSlides("main-slider");
});

function initHandlersCustomLanes() {
    document
        .getElementById("scroll-lanes-left")
        .addEventListener("click", () => scrollLanes("left"));

    document
        .getElementById("scroll-lanes-right")
        .addEventListener("click", () => scrollLanes("right"));
}

function toggleScrollButtons() {
    const container = document.getElementById("scroll-container");
    const scrollButtonsCustomLanes = document.getElementById(
        "scroll-buttons-custom-lanes"
    );
    // Verificar si el contenido se puede desplazar horizontalmente
    scrollButtonsCustomLanes.style.display =
        container.scrollWidth <= container.clientWidth ? "none" : "flex";
}

// Función para desplazar los carriles personalizados del usuario autenticado
function scrollLanes(direction) {
    const container = document.getElementById("scroll-container");
    const scrollAmount = 200; // Ajusta este valor según necesites

    if (direction === "left") {
        container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    } else if (direction === "right") {
        container.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
}

// Añade o quita la clase 'lane-arrow-enabled' a los botones de desplazamiento de carriles personalizados
function handleScrollCustomLanes() {
    const container = document.getElementById("scroll-container");
    container.addEventListener("scroll", function () {
        const scrollLeft = container.scrollLeft;
        const scrollWidth = container.scrollWidth;
        const clientWidth = container.clientWidth;
        const scrollRight = scrollWidth - clientWidth - scrollLeft;

        const scrollLeftBtn = document.getElementById("scroll-lanes-left");
        const scrollRightBtn = document.getElementById("scroll-lanes-right");

        if (scrollLeft === 0) {
            scrollLeftBtn.classList.remove("lane-arrow-enabled");
        } else {
            scrollLeftBtn.classList.add("lane-arrow-enabled");
        }

        if (scrollRight === 0) {
+            scrollRightBtn.classList.remove("lane-arrow-enabled");
        } else {
            scrollRightBtn.classList.add("lane-arrow-enabled");
        }
    });
}

/**
 * Detecta el cambio en la paginación de los carriles de cursos
 */
function handlePaginationLanes() {
    const container = document.getElementById("pagination-lane-courses");

    handlePagination(container, (pageNumber) => {
        console.log("pageNumber", pageNumber);
        if (currentLane == "courses-actived") {
            getActiveCourses(pageNumber);
        } else if (currentLane == "courses-inscribed") {
            getInscribedCourses(pageNumber);
        } else if (currentLane == "courses-teacher") {
            getTeacherCourses(pageNumber);
        }
    });

    const selectorNumPagesCoursesLanes = document.getElementById(
        "selector-num-pages-courses-lanes"
    );
    handleNumPages(selectorNumPagesCoursesLanes, (numPages) => {
        console.log("numPages", numPages);

        if (currentLane == "courses-actived") {
            getActiveCourses(1, numPages);
        } else if (currentLane == "courses-inscribed") {
            getInscribedCourses(1, numPages);
        } else if (currentLane == "courses-teacher") {
            getTeacherCourses(1, numPages);
        }
    });
}

/**
 * Detecta el cambio en los checkboxes de los cursos, programas y recursos
 */
function handleLanes() {
    const params = {
        method: "POST",
        url: "/home/save_lanes_preferences",
        stringify: true,
    };

    const lanes = ["courses", "programs", "resources"];

    lanes.forEach((lane) => {
        const checkbox = document.getElementById(`${lane}-lane-checkbox`);

        if (!checkbox) return;

        checkbox.addEventListener("change", function () {
            const laneElements = Array.from(
                document.getElementsByClassName(`${lane}-lane`)
            );

            params.body = {
                lane: lane,
                active: checkbox.checked,
            };

            if (window.userUid) {
                apiFetch(params).then(() => {
                    laneElements.forEach(function (element) {
                        element.style.display = checkbox.checked
                            ? "block"
                            : "none";
                    });
                });
            } else {
                laneElements.forEach(function (element) {
                    element.style.display = checkbox.checked ? "block" : "none";
                });
            }
        });
    });
}

function handleLaneChange() {
    const laneTabs = document.querySelectorAll(".lane-tab");

    laneTabs.forEach((tab) => {
        tab.addEventListener("click", async (event) => {
            const lane = event.target.getAttribute("data-lane");
            laneTabs.forEach((tab) => tab.classList.remove("selected"));

            event.target.classList.add("selected");

            if (lane == "courses-actived") {
                currentLane = "courses-actived";
                getActiveCourses();
            } else if (lane == "courses-inscribed") {
                currentLane = "courses-inscribed";
                getInscribedCourses();
            } else if (lane == "courses-teacher") {
                currentLane = "courses-teacher";
                getTeacherCourses();
            } else if (lane == "courses-recommended") {
                // To do
            }
        });
    });
}

function getActiveCourses(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_active_courses",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params).then((response) => {
        showCourses(response);
    });
}

function getInscribedCourses(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_inscribed_courses",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params).then((response) => {
        showCourses(response);
    });
}

function getTeacherCourses(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_teacher_courses",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params).then((response) => {
        console.log(response);
        showCourses(response);
    });
}

function showCourses(response) {
    let coursesLanesContainer = document.getElementById(
        "courses-lane-container"
    );

    if (response.data.length) {
        coursesLanesContainer.innerHTML = "";

        document
            .getElementById("control-pagination-courses-lanes")
            .classList.remove("hidden");

        loadResources(coursesLanesContainer, response.data);

        const containerPagination = document.getElementById(
            "pagination-lane-courses"
        );

        updatePagination(
            containerPagination,
            response.current_page,
            response.last_page
        );
    } else {
        coursesLanesContainer.innerHTML =
            "<h2 class='text-center'>No hay cursos</h2>";

        document
            .getElementById("control-pagination-courses-lanes")
            .classList.add("hidden");
    }
}

function loadResources(learningObjectsContainer, learning_objects) {
    let templateLearningObject = document.getElementById(
        "learning-object-template"
    );

    learningObjectsContainer.innerHTML = "";

    learning_objects.forEach((learning_object) => {
        let templateCloned = templateLearningObject.content.cloneNode(true);

        templateCloned.querySelector(".block-title").innerHTML =
            learning_object.title;
        templateCloned.querySelector(".block-description").innerHTML =
            learning_object.description;

        if (
            learning_object.status.code == "DEVELOPMENT" &&
            learning_object.lms_url
        ) {
            templateCloned.querySelectorAll(".block-url").forEach((element) => {
                element.href = learning_object.lms_url;
            });
        }

        templateCloned.querySelector(
            ".learning-object-image"
        ).src = `${window.backendUrl}/${learning_object.image_path}`;

        templateCloned.querySelector(
            ".learning-object-inscription-date"
        ).innerHTML = formatDate(learning_object.inscription_start_date);

        templateCloned.querySelector(
            ".learning-object-realization-date"
        ).innerHTML = formatDate(learning_object.realization_start_date);

        learningObjectsContainer.appendChild(
            document.importNode(templateCloned, true)
        );
    });
}
