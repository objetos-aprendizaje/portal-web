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
            scrollRightBtn.classList.remove("lane-arrow-enabled");
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
        const numPages = document.querySelector(
            "#selector-num-pages-courses-lanes .selector-num-pages"
        ).value;

        loadLane(currentLane, pageNumber, numPages);
    });

    const selectorNumPagesCoursesLanes = document.getElementById(
        "selector-num-pages-courses-lanes"
    );
    handleNumPages(selectorNumPagesCoursesLanes, (numPages) => {
        loadLane(currentLane, 1, numPages);
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
                active: checkbox.checked ? 1 : 0,
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

// Cuando se cambia el carril desde la pestaña
function handleLaneChange() {
    const laneTabs = document.querySelectorAll(".lane-tab");

    laneTabs.forEach((tab) => {
        tab.addEventListener("click", async (event) => {
            const lane = event.target.getAttribute("data-lane");
            laneTabs.forEach((tab) => tab.classList.remove("selected"));

            event.target.classList.add("selected");

            const numPages = document.querySelector(
                "#selector-num-pages-courses-lanes .selector-num-pages"
            ).value;

            currentLane = lane;
            loadLane(currentLane, 1, numPages);
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

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "courses");
        })
        .catch((error) => {
            showNoLearningObjectsLanesUser("courses");
        });
}

function getRecommendedCourses(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_recommended_courses",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "courses");
        })
        .catch(() => {
            showNoLearningObjectsLanesUser("courses");
        });
}

function getRecommendedEducationalResources(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_recommended_educational_resources",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "educationalResources");
        })
        .catch((error) => {
            showNoLearningObjectsLanesUser("educationalResources");
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

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "courses");
        })
        .catch(() => {
            showNoLearningObjectsLanesUser("courses");
        });
}

function getMyEducationalResources(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_my_educational_resources",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "educationalResources");
        })
        .catch((error) => {
            showNoLearningObjectsLanesUser("educationalResources");
        });
}

function getRecommendedItinerary(page = 1, items_per_page = 3) {
    const params = {
        method: "POST",
        url: "/home/get_recommended_itinerary",
        stringify: true,
        body: {
            page,
            items_per_page,
        },
        loader: true,
    };

    apiFetch(params)
        .then((response) => {
            showLearningObjects(response, "courses");
        })
        .catch(() => {
            showNoLearningObjectsLanesUser("courses");
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
        showLearningObjects(response, "courses");
    });
}

function showLearningObjects(response, type) {
    let coursesLanesContainer = document.getElementById(
        "courses-lane-container"
    );

    if (!response.data.length) {
        showNoLearningObjectsLanesUser(type);
        return;
    }

    coursesLanesContainer.innerHTML = "";

    document
        .getElementById("control-pagination-courses-lanes")
        .classList.remove("hidden");

    loadResources(coursesLanesContainer, response.data, type);

    const containerPagination = document.getElementById(
        "pagination-lane-courses"
    );

    updatePagination(
        containerPagination,
        response.current_page,
        response.last_page
    );
}

function showNoLearningObjectsLanesUser(type) {
    document.getElementById(
        "courses-lane-container"
    ).innerHTML = `<h2 class='text-center'>No hay ${
        type == "courses" ? "cursos" : "recursos educativos"
    }</h2>`;

    document
        .getElementById("control-pagination-courses-lanes")
        .classList.add("hidden");
}

function loadResources(learningObjectsContainer, learning_objects, type) {
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

        let url = "#";
        if (
            type == "courses" &&
            learning_object.status.code == "DEVELOPMENT" &&
            learning_object.lms_url
        ) {
            url = learning_object.lms_url;
        } else if (
            type == "courses" &&
            learning_object.status.code == "INSCRIPTION"
        ) {
            url = `/course/${learning_object.uid}`;
        } else if (type == "educationalResources") {
            url = `/resource/${learning_object.uid}`;
        }

        templateCloned.querySelectorAll(".block-url").forEach((element) => {
            element.href = url;
        });

        if (type == "courses") {
            templateCloned.querySelector(
                ".learning-object-start-date"
            ).innerHTML = formatDate(learning_object.realization_start_date);

            templateCloned.querySelector(
                ".learning-object-finish-date"
            ).innerHTML = formatDate(learning_object.realization_finish_date);
        } else {
            const idsHidde = [
                ".learning-objects-dates-container",
                ".separator-dates",
            ];

            idsHidde.forEach((id) => {
                templateCloned.querySelector(id).classList.add("hidden");
            });
        }

        templateCloned.querySelector(
            ".learning-object-image"
        ).src = `${window.backendUrl}/${learning_object.image_path}`;

        learningObjectsContainer.appendChild(
            document.importNode(templateCloned, true)
        );
    });
}

function loadLane(lane, page = 1, items_per_page = 3) {
    if (lane == "courses-actived") {
        getActiveCourses(page, items_per_page);
    } else if (lane == "courses-inscribed") {
        getInscribedCourses(page, items_per_page);
    } else if (lane == "courses-teacher") {
        getTeacherCourses(page, items_per_page);
    } else if (lane == "courses-recommended") {
        getRecommendedCourses(page, items_per_page);
    } else if (lane == "educational-resources-recommended") {
        getRecommendedEducationalResources(page, items_per_page);
    } else if (lane == "my-educational-resources") {
        getMyEducationalResources(page, items_per_page);
    } else if (lane == "recommended-itinerary") {
        getRecommendedItinerary(page, items_per_page);
    }
}
