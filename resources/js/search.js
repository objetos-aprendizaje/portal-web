import {
    instanceFlatpickr,
    getFlatpickrDateRangeSql,
    toggleCheckbox,
    apiFetch,
    updatePagination,
    handlePagination,
} from "./app";

import { heroicon } from "./heroicons";

import Treeselect from "treeselectjs";

let treeSelectCategories;
let treeSelectCompetences;

let selectedCategories = [];
let selectedCompetences = [];

let resourceTypes = [];

let inscriptionDateFlatpickr;
let realizationDateFlatpickr;

document.addEventListener("DOMContentLoaded", function () {
    initializeFlatpickr();
    initializeTreeSelect();
    handleChecksResourceTypes();
    getResourcesChecked();
    getCategoryChecked();

    document
        .getElementById("openned-inscriptions")
        .addEventListener("change", function () {
            searchLearningObjects();
        });

    document
        .getElementById("modality-payment")
        .addEventListener("change", function () {
            searchLearningObjects();
        });

    document
        .getElementById("assessments")
        .addEventListener("change", function () {
            searchLearningObjects();
        });

    document
        .getElementById("search-btn")
        .addEventListener("click", function () {
            searchLearningObjects();
        });

    document
        .getElementById("search")
        .addEventListener("keyup", function (event) {
            if (event.key === "Enter") searchLearningObjects();
        });

    document
        .getElementById("view-vertical-btn")
        .addEventListener("click", function () {
            changeViewLayout("vertical");
        });

    document
        .getElementById("view-horizontal-btn")
        .addEventListener("click", function () {
            changeViewLayout("horizontal");
        });

    document
        .getElementById("delete-all-filters-btn")
        .addEventListener("click", function () {
            wipeFilters();
        });

    let orderByBtn = document.getElementById("order-by-btn");
    let orderByContainer = document.getElementById("order-by-container");

    orderByBtn.addEventListener("click", function (event) {
        event.stopPropagation(); // Evita que el evento se propague al documento
        orderByContainer.classList.toggle("hidden");
    });

    document.addEventListener("click", function (event) {
        if (
            !orderByBtn.contains(event.target) &&
            !orderByContainer.contains(event.target)
        ) {
            orderByContainer.classList.add("hidden");
        }
    });

    document.querySelectorAll(".order-option-btn").forEach(function (btn) {
        btn.addEventListener("click", applyOrder);
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth < 992) changeViewLayout("vertical");
    });

    const containerPagination = document.getElementById("searcher-pagination");
    handlePagination(containerPagination, (pageNumber) => {
        searchLearningObjects(pageNumber);
    });

    document.body.addEventListener("click", function (event) {
        const deleteFilterBtn = event.target.closest(".delete-filter");

        if (deleteFilterBtn) {
            const filterKey = deleteFilterBtn.dataset.filter_key;
            deleteFilter(filterKey);
        }
    });

    searchLearningObjects();
});

function applyOrder() {
    var orderBy = this.getAttribute("data-order_by");

    let orderByLabel = "";
    if (orderBy == "relevance") {
        orderByLabel = "relevancia";
    } else if (orderBy == "closer") {
        orderByLabel = "más próximos";
    } else if (orderBy == "puntuation") {
        orderByLabel = "mejor valorados";
    }

    document.getElementById("order-by-label").innerHTML = orderByLabel;

    // Cerramos el menú
    document.getElementById("order-by-container").classList.add("hidden");
    searchLearningObjects(1, 10, orderBy);
}

function wipeFilters() {
    document.getElementById("openned-inscriptions").value = "";
    inscriptionDateFlatpickr.clear();
    realizationDateFlatpickr.clear();
    document.getElementById("modality-payment").value = "";
    document.getElementById("assessments").value = "";
    document.getElementById("search").value = "";

    selectedCategories = [];
    treeSelectCategories.updateValue([]);

    selectedCompetences = [];
    treeSelectCompetences.updateValue([]);

    searchLearningObjects();
}

function deleteFilter(filterKey) {
    if (filterKey === "categories") {
        selectedCategories = [];
        treeSelectCategories.updateValue([]);
    } else if (filterKey === "competences") {
        selectedCompetences = [];
        treeSelectCompetences.updateValue([]);
    } else if (filterKey === "opennedInscriptions") {
        document.getElementById("openned-inscriptions").value = "";
    } else if (filterKey === "inscriptionDate") {
        inscriptionDateFlatpickr.clear();
    } else if (filterKey === "realizationDate") {
        realizationDateFlatpickr.clear();
    } else if (filterKey === "modalityPayment") {
        document.getElementById("modality-payment").value = "";
    } else if (filterKey === "assessments") {
        document.getElementById("assessments").value = "";
    } else if (filterKey === "search") {
        document.getElementById("search").value = "";
    }

    searchLearningObjects();
}

function getParamFromUrl(paramName) {
    let url = new URL(window.location.href);
    let params = new URLSearchParams(url.search);
    return params.get(paramName);
}

function getCategoryChecked() {
    let category_uid = getParamFromUrl("category_uid");

    if (category_uid) {
        treeSelectCategories.updateValue([category_uid]);
        selectedCategories = [category_uid];
    }
}

function getResourcesChecked() {
    let resources = getParamFromUrl("resources");

    // Si no hay recursos en los parámetros de la URL, usar un array por defecto
    resourceTypes = resources
        ? resources.split(",")
        : ["courses", "programs", "resources"];

    // Marcar o desmarcar cada checkbox
    resourceTypes.forEach(toggleCheckbox);
}

/**
 * Manejar los cambios en los checkbox de los tipos de recursos.
 */
function handleChecksResourceTypes() {
    ["courses", "programs", "resources"].forEach((checkbox) => {
        document
            .getElementById(checkbox)
            .addEventListener("change", function () {
                if (this.checked) {
                    if (!resourceTypes.includes(this.id)) {
                        resourceTypes.push(this.id);
                    }
                } else {
                    let index = resourceTypes.indexOf(this.id);
                    if (index !== -1) {
                        resourceTypes.splice(index, 1);
                    }
                }

                searchLearningObjects();
            });
    });
}

function initializeFlatpickr() {
    inscriptionDateFlatpickr = instanceFlatpickr(
        "filter_inscription_date",
        function (selectedDates) {
            if (!selectedDates.length) return;
            searchLearningObjects();
        }
    );

    realizationDateFlatpickr = instanceFlatpickr(
        "filter_realization_date",
        function (selectedDates) {
            if (!selectedDates.length) return;
            searchLearningObjects();
        }
    );
}

function initializeTreeSelect() {
    const optionsCategoriesTreeSelect = convertCategoriesToOptions(
        window.categories
    );
    treeSelectCategories = new Treeselect({
        parentHtmlContainer: document.getElementById("treeselect-categories"),
        options: optionsCategoriesTreeSelect,
        showTags: false,
        tagsCountText: "categorías seleccionadas",
        searchable: false,
        value: [],
        isIndependentNodes: true,
        iconElements: {},
        placeholder: "Categorías",

        inputCallback: function (categories) {
            selectedCategories = categories;
            searchLearningObjects();
        },
    });

    const optionsCompetencesTreeSelect = convertCompetencesToOptions(
        window.competences
    );
    treeSelectCompetences = new Treeselect({
        parentHtmlContainer: document.getElementById("treeselect-competences"),
        options: optionsCompetencesTreeSelect,
        showTags: false,
        tagsCountText: "competencias seleccionadas",
        searchable: false,
        iconElements: {},
        placeholder: "Competencias",
        value: [],
        inputCallback: function (competences) {
            selectedCompetences = competences;
            searchLearningObjects();
        },
    });
}

function convertCompetencesToOptions(competences) {
    return competences.map((competence) => ({
        name: competence.name,
        value: competence.uid,
        children: convertCompetencesToOptions(competence.subcompetences),
    }));
}

function convertCategoriesToOptions(categories) {
    return categories.map((category) => ({
        name: category.name,
        value: category.uid,
        children: convertCategoriesToOptions(category.subcategories),
    }));
}

function collectFilters() {
    const opennedInscriptions = document.getElementById(
        "openned-inscriptions"
    ).value;
    const inscriptionDate = getFlatpickrDateRangeSql(inscriptionDateFlatpickr);
    const realizationDate = getFlatpickrDateRangeSql(realizationDateFlatpickr);
    const modalityPayment = document.getElementById("modality-payment").value;
    const assessments = document.getElementById("assessments").value;
    const search = document.getElementById("search").value;

    let filters = {};
    if (opennedInscriptions) filters.opennedInscriptions = opennedInscriptions;
    if (inscriptionDate[0]) filters.inscription_start_date = inscriptionDate[0];
    if (inscriptionDate[1])
        filters.inscription_finish_date = inscriptionDate[1];
    if (realizationDate[0]) filters.realization_start_date = realizationDate[0];
    if (realizationDate[1])
        filters.realization_finish_date = realizationDate[1];
    if (modalityPayment) filters.modalityPayment = modalityPayment;
    if (assessments) filters.assessments = assessments;
    if (selectedCategories.length) filters.categories = selectedCategories;
    if (selectedCompetences.length) filters.competences = selectedCompetences;
    if (search) filters.search = search;

    return filters;
}

function searchLearningObjects(
    page = 1,
    itemsPerPage = 10,
    orderBy = "relevance"
) {
    const filters = collectFilters();

    const params = {
        method: "POST",
        body: {
            filters: filters,
            page,
            itemsPerPage,
            resourceTypes,
            orderBy,
        },
        stringify: true,
        url: "/searcher/get_learning_objects",
        loader: true,
    };

    apiFetch(params).then((response) => {
        const containerPagination = document.getElementById(
            "searcher-pagination"
        );

        updatePagination(
            containerPagination,
            response.current_page,
            response.last_page
        );

        updateFiltersSelectors(filters);
        updateFiltersResults(filters, response.data.length, response.total);

        loadResources(response);
    });
}

function updateFiltersResults(filters, dataLength, total) {
    const numberFilters = Object.keys(filters).length;
    const filterResults = document.getElementById("filters-results");

    if (numberFilters) {
        filterResults.classList.remove("hidden");
        filterResults.classList.add("flex");
        document.getElementById("filter-results-showing").innerHTML =
            dataLength;
        document.getElementById("filter-results-total").innerHTML = total;
    } else {
        filterResults.classList.add("hidden");
        filterResults.classList.remove("flex");
    }
}

function updateFiltersSelectors(filters) {
    const filtersContainer = document.getElementById("filters-container");

    // Eliminamos todos los filtros que hubieran
    filtersContainer.querySelectorAll(".filter-selector").forEach((filter) => {
        filter.remove();
    });

    const templateFilter = document.getElementById("filter-template");

    function addFilter(name, filterKey) {
        let templateCloned = templateFilter.content.cloneNode(true);
        templateCloned.querySelector(".filter-name").innerHTML = name;
        templateCloned.querySelector(".delete-filter").dataset.filter_key =
            filterKey;
        filtersContainer.prepend(document.importNode(templateCloned, true));
    }

    if (filters.categories && filters.categories.length) {
        addFilter(
            filters.categories.length + " categorías seleccionadas",
            "categories"
        );
    }

    if (filters.competences && filters.competences.length) {
        addFilter(
            filters.competences.length + " competencias seleccionadas",
            "competences"
        );
    }

    if (filters.opennedInscriptions && filters.opennedInscriptions !== "") {
        addFilter(
            filters.opennedInscriptions == 1
                ? "Inscripciones abiertas"
                : "Inscripciones cerradas",
            "opennedInscriptions"
        );
    }

    if (filters.inscription_start_date && filters.inscription_finish_date) {
        addFilter(
            "Fecha de inscripción: " +
                filters.inscription_start_date +
                " - " +
                filters.inscription_finish_date,
            "inscriptionDate"
        );
    }

    if (filters.realization_start_date && filters.realization_finish_date) {
        addFilter(
            "Fecha de realización: " +
                filters.realization_start_date +
                " - " +
                filters.realization_finish_date,
            "realizationDate"
        );
    }

    if (filters.modalityPayment) {
        addFilter(
            filters.modalityPayment === "PAID"
                ? "Cursos de pago"
                : "Cursos gratuitos",
            "modalityPayment"
        );
    }

    if (filters.assessments) {
        addFilter("Valoraciones: " + filters.assessments, "assessments");
    }

    if (filters.search) {
        addFilter("Búsqueda: " + filters.search, "search");
    }
}

function fillLearningObjectsContainer(
    learningObjectsContainer,
    learning_objects
) {
    let templateLearningObject = document.getElementById(
        "learning-object-template"
    );

    learning_objects.forEach((learning_object) => {
        let templateCloned = templateLearningObject.content.cloneNode(true);

        templateCloned.querySelector(".block-title").innerHTML =
            learning_object.title;
        templateCloned.querySelector(".block-description").innerHTML =
            learning_object.description;
        templateCloned.querySelector(
            ".learning-object-image"
        ).src = `${window.backendUrl}/${learning_object.image_path}`;
        templateCloned.querySelector(".learning-object-type").innerHTML =
            getLabelLearningObjectType(learning_object.learning_object_type);

        let urlLearningObject = "";
        if (learning_object.learning_object_type === "course") {
            urlLearningObject = "/course/" + learning_object.uid;
        } else if (
            learning_object.learning_object_type === "educational_program"
        ) {
            urlLearningObject = "/educational_program/" + learning_object.uid;
        } else if (
            learning_object.learning_object_type === "educational_resource"
        ) {
            urlLearningObject = "/resource/" + learning_object.uid;
        }

        templateCloned
            .querySelectorAll(".learning-object-url")
            .forEach((link) => {
                link.href = urlLearningObject;
            });

        if (learning_object.learning_object_type === "educational_resource") {
            templateCloned
                .querySelector(".learning-object-ects")
                .classList.add("hidden");
            templateCloned
                .querySelector(".learning-object-dates-block")
                .classList.add("hidden");

            templateCloned
                .querySelector(".block-status")
                .classList.add("hidden");
        } else {
            templateCloned.querySelector(
                ".learning-object-ects-count"
            ).innerHTML = learning_object.ects_workload;
            templateCloned.querySelector(
                ".learning-object-ects-count"
            ).innerHTML = learning_object.ects_workload;
            templateCloned.querySelector(
                ".learning-object-inscription-date"
            ).innerHTML = `${formatDate(
                learning_object.inscription_start_date
            )} - ${formatDate(learning_object.inscription_finish_date)}`;

            templateCloned.querySelector(
                ".learning-object-realization-date"
            ).innerHTML = `${formatDate(
                learning_object.realization_start_date
            )} - ${formatDate(learning_object.realization_finish_date)}`;

            const classIndicator =
                learning_object.status_code == "ACCEPTED_PUBLICATION"
                    ? "soon"
                    : "openned";

            templateCloned
                .querySelector(".learning-object-status-indicator")
                .classList.add(classIndicator);

            const textIndicator =
                learning_object.status_code == "ACCEPTED_PUBLICATION"
                    ? "Próximamente"
                    : "En inscripción";
            templateCloned.querySelector(
                ".learning-object-status-text"
            ).innerHTML = textIndicator;
        }

        // Estrellas
        if (window.learning_objects_appraisals) {
            for (let i = 1; i <= 5; i++) {
                let starElement = document.createElement("div");
                starElement.className = "learning-object-star";
                starElement.className =
                    i <= learning_object.average_calification
                        ? "star-filled"
                        : "star-no-filled";
                starElement.innerHTML = heroicon("star", "solid");
                templateCloned
                    .querySelector(".learning-object-stars")
                    .appendChild(starElement);
            }
        }

        learningObjectsContainer.appendChild(
            document.importNode(templateCloned, true)
        );
    });
}

function loadResources(response) {
    let learningObjectsContainer = document.getElementById(
        "learning-objects-container"
    );

    let noLearningObjectsFound = document.getElementById(
        "no-learning-objects-found"
    );
    let learningObjectsSection = document.getElementById(
        "learning-objects-section"
    );
    learningObjectsContainer.innerHTML = "";

    if (response.data.length) {
        noLearningObjectsFound.classList.add("hidden");
        learningObjectsSection.classList.remove("hidden");
        fillLearningObjectsContainer(learningObjectsContainer, response.data);
    } else {
        noLearningObjectsFound.classList.remove("hidden");
        learningObjectsSection.classList.add("hidden");
    }
}

function formatDate(dateSql) {
    let date = new Date(dateSql);
    let formattedDate =
        ("0" + date.getDate()).slice(-2) +
        "/" +
        ("0" + (date.getMonth() + 1)).slice(-2) +
        "/" +
        date.getFullYear().toString().substr(-2);

    return formattedDate;
}

function getLabelLearningObjectType(type) {
    switch (type) {
        case "course":
            return "Curso";
        case "educational_program":
            return "Programa";
        case "educational_resource":
            return "Recurso";
    }
}

// Cambio de tipo de vista
function changeViewLayout(view) {
    const svgHorizontalElement = document.querySelector(
        "#view-horizontal-btn svg"
    );

    const svgVerticalElement = document.querySelector("#view-vertical-btn svg");

    const learningObjectsContainer = document.getElementById(
        "learning-objects-container"
    );

    const isHorizontalView = view === "horizontal";

    svgHorizontalElement.classList.toggle("text-color_1", isHorizontalView);
    svgVerticalElement.classList.toggle("text-color_1", !isHorizontalView);

    learningObjectsContainer.classList.toggle("horizontal", isHorizontalView);
    learningObjectsContainer.classList.toggle("vertical", !isHorizontalView);
}
