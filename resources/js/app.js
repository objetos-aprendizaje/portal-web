import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import { showToast } from "./toast";

window.defaultErrorMessageFetch = "Ha ocurrido un error";

document.addEventListener("DOMContentLoaded", function () {
    controlCustomCheckboxs();
    applyPreventDefaultForms();
    controlSubmenus();
    controlSearch();
});

/**
 * Recoge el texto del buscador del header y redirige a la página de búsqueda.
 */
function controlSearch() {
    const searchInput = document.querySelector(".searcher input");
    const searchButton = document.querySelector(".searcher button");
    const searchButtonFooter = document.querySelector(
        ".searcher-footer button"
    );

    function redirectToSearcher() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `/searcher?text=${encodeURIComponent(
                query
            )}`;
        }
    }

    searchButton.addEventListener("click", function () {
        redirectToSearcher();
    });

    searchInput.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            redirectToSearcher();
        }
    });

    if (searchButtonFooter) {
        searchButtonFooter.addEventListener("click", function () {
            redirectToSearcher();
        });

        searchButtonFooter.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                redirectToSearcher();
            }
        });
    }
}

function controlSubmenus() {
    const menuItems = document.querySelectorAll(".has-submenu-header");

    // Función para ocultar todos los submenús
    function hideAllSubmenus() {
        document.querySelectorAll(".submenu-header").forEach((submenu) => {
            submenu.classList.add("hidden");
        });
    }

    menuItems.forEach((item) => {
        const submenu = item.nextElementSibling; // Asume que el submenú es el siguiente elemento hermano

        item.addEventListener("mouseenter", () => {
            hideAllSubmenus(); // Oculta todos los submenús
            submenu.classList.remove("hidden"); // Muestra el submenú correspondiente
        });

        item.parentNode.addEventListener("mouseleave", () => {
            // Inicia un temporizador para ocultar el submenú
            submenu.classList.add("hidden");
        });

        // Oculta el submenú cuando el cursor sale del submenú
        submenu.addEventListener("mouseleave", () => {
            submenu.classList.add("hidden");
        });

        //control para manejo con teclado
        item.addEventListener("focusin", () => {
            hideAllSubmenus(); // Oculta todos los submenús
            submenu.classList.remove("hidden"); // Muestra el submenú correspondiente
        });

        submenu.addEventListener("focusout", () => {
            hideAllSubmenus(); // Oculta todos los submenús
            submenu.classList.add("hidden"); // Muestra el submenú correspondiente
        });
    });

    // Añade el manejador de eventos al header
    const header = document.querySelector("header");
    if (header) {
        header.addEventListener("mouseleave", () => {
            // Inicia un temporizador para ocultar todos los submenús
            hideAllSubmenus();
        });
    }
}

function applyPreventDefaultForms() {
    const forms = document.querySelectorAll("form[prevent-default]");

    forms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
        });
    });
}

export function handleTabs() {
    var tabs = Array.from(document.querySelectorAll(".tab"));
    var tabContents = Array.from(document.querySelectorAll(".tab-content"));

    function showTabContent(tab) {
        tabContents.forEach((tabContent) => {
            tabContent.style.display = "none";
            tabContent.classList.remove("fade-tab");
        });
        var tabContentId = tab.getAttribute("tab-content");
        var tabContent = document.getElementById(tabContentId);
        tabContent.style.display = "block";
        tabContent.style.opacity = 0;
        tabContent.classList.add("fade-tab");
        setTimeout(() => (tabContent.style.opacity = 1), 50);
        tabs.forEach((otherTab) => otherTab.classList.remove("tab-selected"));
        tab.classList.add("tab-selected");
    }

    var selectedTab = tabs.find((tab) =>
        tab.classList.contains("tab-selected")
    );
    if (selectedTab) {
        showTabContent(selectedTab);
    }

    tabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            showTabContent(tab);
        });
    });
}

export function showFormErrors(errors, customClassLabel = []) {
    Object.keys(errors).forEach((field) => {
        const element = document.getElementById(field);

        if (!element) return;

        if (element.tagName === "INPUT" && element.type === "file") {
            // Encuentra el div que quieres resaltar y añade la clase de error.
            const div = element.closest(".select-file-container");
            div.classList.add("error-border");

            // Crea el mensaje de error y lo coloca fuera del div 'select-file-container'.
            const small = document.createElement("small");
            small.textContent = errors[field];
            small.classList.add("error-label");

            customClassLabel.forEach((classLabel) => {
                small.classList.add(classLabel);
            });

            div.parentNode.insertBefore(small, div.nextSibling);
        } else if (element.getAttribute("data-choice")) {
            // Encuentra el contenedor de Choices.js y añade la clase de error.
            const choicesContainer = element.closest(".choices");
            const choicesInnerContainer = element.closest(".choices__inner");
            if (choicesContainer) {
                choicesInnerContainer.classList.add("error-border");

                // Crea el mensaje de error y lo coloca fuera del contenedor de Choices.js.
                const small = document.createElement("small");
                small.textContent = errors[field];
                small.classList.add("error-label");

                customClassLabel.forEach((classLabel) => {
                    small.classList.add(classLabel);
                });

                choicesContainer.parentNode.insertBefore(
                    small,
                    choicesContainer.nextSibling
                );
            }
        } else if (["INPUT", "TEXTAREA", "SELECT"].includes(element.tagName)) {
            // El comportamiento original para otros tipos de elementos.
            element.classList.add("error-border");

            const small = document.createElement("small");
            small.textContent = errors[field];
            small.classList.add("error-label");

            customClassLabel.forEach((classLabel) => {
                small.classList.add(classLabel);
            });
            element.parentNode.appendChild(small);
        } else if (element.getAttribute("data-tomselect")) {
            const tomSelectContainer = element.closest(".ts-wrap");
            if (tomSelectContainer) {
                tomSelectContainer.classList.add("error-border");

                const small = document.createElement("small");
                small.textContent = errors[field];
                small.classList.add("error-label");
                tomSelectContainer.parentNode.appendChild(small);
            }
        }
    });
}

export function resetFormErrors(formId) {
    const form = document.getElementById(formId);

    if (!form) return;

    // Reset errors for inputs and textareas within the form
    const elementsWithError = form.querySelectorAll(".error-border");
    elementsWithError.forEach((element) => {
        element.classList.remove("error-border");
    });

    // Remove error messages within the form
    const errorMessages = form.querySelectorAll("small.error-label");
    errorMessages.forEach((small) => {
        small.remove();
    });
}

/**
 * Función para actualizar el nombre del archivo seleccionado en un input de tipo "file".
 * Esta función se liga al evento "change" del documento y busca contenedores con la clase "poa-input-file".
 * Dentro de estos contenedores, busca un elemento <span> con la clase "file-name" y actualiza su contenido
 * con el nombre del archivo seleccionado. Si no hay archivo, muestra "Ningún archivo seleccionado".
 */
export function updateInputFile() {
    let classDiv = "poa-input-file";

    document.addEventListener("change", function (e) {
        const target = e.target;

        if (target.closest(`.${classDiv}`) && target.type === "file") {
            const previewDiv = target.closest(`.${classDiv}`);
            const span = previewDiv.querySelector(".file-name");

            if (target.files && target.files[0]) {
                const fileName = target.files[0].name;
                span.textContent = fileName;
            } else {
                span.textContent = "Ningún archivo seleccionado";
            }
        }
    });
}

/**
 * Controla los checks personalizados
 */
function controlCustomCheckboxs() {
    document.querySelectorAll(".custom-checkbox").forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            this.nextElementSibling
                .querySelector(".checkmark")
                .classList.toggle("hidden", !this.checked);

            this.nextElementSibling
                .querySelector(".checkbox-icon")
                .classList.toggle("checked", this.checked);
        });
    });

    // Controla los clicks en los iconos de los checks personalizados
    document
        .querySelectorAll(".checkbox-container .checkbox-icon")
        .forEach(function (icon) {
            icon.addEventListener("click", function () {
                // Obtener el padre del padre
                let parentOfParent = icon.parentElement.parentElement;

                // Obtener el .custom-checkbox dentro del padre del padre
                let customCheckbox =
                    parentOfParent.querySelector(".custom-checkbox");

                // Obtener el valor del .custom-checkbox
                let checkboxValue = customCheckbox.checked;

                // Cambiar el valor del .custom-checkbox
                customCheckbox.checked = !checkboxValue;

                customCheckbox.dispatchEvent(new Event("change"));
            });
        });
}

export function toggleCheckbox(id) {
    let checkbox = document.getElementById(id);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;

        let checkmark = checkbox.nextElementSibling.querySelector(".checkmark");
        if (checkmark) {
            checkmark.classList.toggle("hidden", !checkbox.checked);
        }
    }
}

export function formatDateTime(datetime) {
    const date = new Date(datetime);
    const day = date.getDate().toString().padStart(2, "0");
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const year = date.getFullYear();
    const hours = date.getHours().toString().padStart(2, "0");
    const minutes = date.getMinutes().toString().padStart(2, "0");

    return `${day}/${month}/${year} a las ${hours}:${minutes}`;
}

export function getCsrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
}

/**
 *
 *
 * @param {*} params
 * @param {*} params.method - Método HTTP a utilizar para la solicitud.
 * @param {*} params.url - URL a la que se enviará la solicitud.
 * @param {*} params.body - Cuerpo de la solicitud.
 * @param {*} params.stringify - Si el cuerpo debe convertirse a una cadena JSON.
 * @param {*} params.loader - Si se debe mostrar el loader mientras se realiza la solicitud.
 * @param {*} params.toast - Si se debe mostrar un mensaje de tostada después de la solicitud.
 * @param {*} params.download - Si se debe descargar un archivo.
 *
 * @returns {Promise} - Retorna una promesa que se resuelve con los datos de la solicitud o se rechaza con un mensaje de error.
 */

export function apiFetch(params) {
    let headers = {
        "X-CSRF-TOKEN": getCsrfToken(),
    };

    let fetchOptions = {
        method: params.method,
        headers: params.headers,
    };

    if (params.stringify) headers["Content-Type"] = "application/json";

    if (params.body) {
        params.body.currentUrl = window.location.href;

        fetchOptions.body = params.stringify
            ? JSON.stringify(params.body)
            : params.body;
    }

    if (params.loader) showLoader();

    return new Promise((resolve, reject) => {
        fetch(params.url, {
            body: fetchOptions.body,
            headers: headers,
            method: fetchOptions.method,
        })
            .then((response) => {
                if (params.loader) hideLoader();

                if (params.download) {
                    return response.blob().then((blob) => {
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement("a");
                        a.href = url;
                        a.download = params.filename || "download";
                        a.style.display = "none";
                        document.body.appendChild(a);
                        var contentDisposition = response.headers.get(
                            "Content-Disposition"
                        );
                        var filename = contentDisposition
                            ? contentDisposition.split("=")[1]
                            : "download";
                        a.download = filename;
                        a.click();
                        a.remove();
                        resolve();
                    });
                } else {
                    return response.json().then((data) => {
                        if (response.ok) {
                            if (params.toast && data.message)
                                showToast(data.message, "success");
                            resolve(data);
                        } else {
                            if (params.toast) showToast(data.message, "error");
                            reject(data);
                        }
                    });
                }
            })
            .catch((error) => {
                let errorMessage = "";

                if (
                    typeof error === "object" &&
                    error !== null &&
                    error.hasOwnProperty("success") &&
                    !error.success
                ) {
                    errorMessage = error.message;
                } else {
                    errorMessage = defaultErrorMessageFetch;
                }

                if (params.toast) showToast(errorMessage, "error");
                reject(errorMessage);
            });
    });
}

export function instanceFlatpickr(idElement, onChangeFunction = null) {
    let options = {
        mode: "range",
        dateFormat: "d-m-Y",
        enableTime: false,
        locale: Spanish,
    };

    if (onChangeFunction !== null) options.onChange = onChangeFunction;

    const flatpickrInstance = flatpickr("#" + idElement, options);

    return flatpickrInstance;
}

export function getFlatpickrDateTimeRangeSql(flatpickrDate) {
    // Obtiene el rango de fechas seleccionado desde flatpickr
    let dateRange = flatpickrDate.selectedDates;

    // Formatea las fechas a YYYY-MM-DD HH:MM:SS
    let formattedDates = dateRange.map((date) => {
        return date.toISOString().replace("T", " ").substring(0, 19);
    });

    return formattedDates;
}

export function showLoader() {
    document.getElementById("fullscreen-loader").style.display = "flex";
    document.body.style.overflow = "hidden"; // Deshabilita el scroll
}

export function hideLoader() {
    document.getElementById("fullscreen-loader").style.display = "none";
    document.body.style.overflow = ""; // Restaura el scroll
}

export function getFlatpickrDateRangeSql(flatpickrDate) {
    // Obtiene el rango de fechas seleccionado desde flatpickr
    let dateRange = flatpickrDate.selectedDates;

    // Formatea las fechas a YYYY-MM-DD ajustando a la zona horaria local
    let formattedDates = dateRange.map((date) => {
        let localDate = new Date(
            date.getTime() - date.getTimezoneOffset() * 60000
        );
        return localDate.toISOString().split("T")[0];
    });

    return formattedDates;
}

export function getFlatpickrDateRange(flatpickrDate) {
    // Obtiene el rango de fechas seleccionado desde flatpickr
    let dateRange = flatpickrDate.selectedDates;

    // Formatea las fechas a "dd/MM/YYYY a las HH:mm"
    let formattedDates = dateRange.map((date) => {
        let datePart = date.toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });

        return `${datePart}`;
    });

    // Une las fechas con un guión y un espacio
    return formattedDates.join(" - ");
}

export function accordionControls() {
    var accordionHeaders = document.querySelectorAll(".accordion-header");

    accordionHeaders.forEach(function (header) {
        header.addEventListener("click", function () {
            var content = this.nextElementSibling;

            // Toggle accordion classes
            content.classList.toggle("accordion-collapsed");
            content.classList.toggle("accordion-uncollapsed");

            // Toggle arrow classes
            this.querySelector(".arrow-down").classList.toggle("hidden");
            this.querySelector(".arrow-up").classList.toggle("hidden");
        });
    });
}

/**
 * Función que se encarga de rellenar las estrellas de la calificación al hacer hover sobre ellas.
 */
export function fillStarsHover() {
    var stars = Array.from(
        document.querySelectorAll(".stars-califications svg")
    );

    stars.forEach(function (star, index) {
        star.addEventListener("mouseover", function () {
            fillStars(index + 1);
        });

        star.addEventListener("mouseout", function () {
            const avgCalification =
                document.getElementById("avg-calification").value;
            fillStars(avgCalification);
        });
    });
}

/**
 * Función que se encarga de rellenar las estrellas de la calificación.
 * @param {*} number Número de estrellas a rellenar
 */
export function fillStars(number) {
    var stars = document.querySelectorAll(".stars-califications svg");

    // Resalta las estrellas correspondientes
    for (var i = 0; i < number; i++) {
        stars[i].style.fill = "#EABA0F";
        stars[i].style.stroke = "#EABA0F";
    }

    // Pone grises las estrellas restantes
    for (var i = number; i < stars.length; i++) {
        stars[i].style.fill = "#E4E4E4";
        stars[i].style.stroke = "#E4E4E4";
    }
}

/**
 * Maneja la actualización de una vista previa de imagen y el nombre del archivo en un input "file".
 * Se activa al cambiar el archivo en elementos con la clase "poa-input-image".
 */
export function updateInputImage() {
    let classDiv = "poa-input-image";
    document.addEventListener("change", function (e) {
        const target = e.target;

        if (target.closest(`.${classDiv}`) && target.type === "file") {
            const previewDiv = target.closest(`.${classDiv}`);
            const img = previewDiv.querySelector("img");
            const span = previewDiv.querySelector(".image-name");

            if (target.files && target.files[0]) {
                const fileType = target.files[0].type;

                if (!fileType.startsWith("image/")) return;

                const reader = new FileReader();

                reader.onload = function (event) {
                    img.src = event.target.result;
                };

                reader.readAsDataURL(target.files[0]);

                span.textContent = target.files[0].name;
            } else {
                img.src = defaultImagePreview;
                span.textContent = "Ningún archivo seleccionado";
            }
        }
    });
}

export function formatDate(dateSql) {
    let date = new Date(dateSql);
    let formattedDate =
        ("0" + date.getDate()).slice(-2) +
        "/" +
        ("0" + (date.getMonth() + 1)).slice(-2) +
        "/" +
        date.getFullYear().toString().substr(-2);

    return formattedDate;
}

export function updatePagination(container, current_page, last_page) {
    const previousBtn = container.querySelector(".previous-page-btn");
    const currentPageBtn = container.querySelector(".current-page-btn");
    const nextPageBtn = container.querySelector(".next-page-btn");
    const lastPageBtn = container.querySelector(".last-page");
    const separator = container.querySelector(".separator");
    const goNextPageBtn = container.querySelector(".go-next-page");
    const goPreviousPageBtn = container.querySelector(".go-previous-page");
    const goLastPageBtn = container.querySelector(".go-last-page");
    const goFirstPageBtn = container.querySelector(".go-first-page-btn");

    const paginationButtons = [
        previousBtn,
        currentPageBtn,
        nextPageBtn,
        lastPageBtn,
    ];

    // Remove the 'selected' class from all buttons
    paginationButtons.forEach((btn) => btn.classList.remove("selected"));

    previousBtn.style.display = "flex";
    nextPageBtn.style.display = "flex";
    goFirstPageBtn.classList.remove("pagination-btn-hidden");
    goPreviousPageBtn.classList.remove("pagination-btn-hidden");
    goLastPageBtn.classList.remove("pagination-btn-hidden");
    goNextPageBtn.classList.remove("pagination-btn-hidden");

    let cssClassLastPage;
    if (last_page < 4 || last_page == current_page + 1) {
        cssClassLastPage = "none";
    } else {
        cssClassLastPage = "flex";
    }

    separator.style.display = cssClassLastPage;
    lastPageBtn.style.display = cssClassLastPage;

    // Si la primera página es la actual, mostrar los números de las tres páginas siguientes
    if (current_page === 1) {
        let previous_page = current_page;
        previousBtn.textContent = previous_page;
        previousBtn.dataset.page = previous_page;

        if (last_page < 3) nextPageBtn.style.display = "none";

        let current_btn_value = current_page + 1;
        currentPageBtn.textContent = current_btn_value;
        currentPageBtn.dataset.page = current_btn_value;

        let next_page_value = current_page + 2;
        nextPageBtn.textContent = next_page_value;
        nextPageBtn.dataset.page = next_page_value;

        goNextPageBtn.dataset.page =
            last_page > current_page ? current_page + 1 : last_page;

        goFirstPageBtn.classList.add("pagination-btn-hidden");
        goPreviousPageBtn.classList.add("pagination-btn-hidden");
    }
    // If the current page is the last page, show the numbers of the last three pages
    else if (current_page === last_page) {
        let previous_page = Math.max(1, current_page - 2);
        previousBtn.textContent = previous_page;
        previousBtn.dataset.page = previous_page;

        if (last_page < 3) previousBtn.style.display = "none";

        currentPageBtn.textContent = Math.max(1, current_page - 1);
        currentPageBtn.dataset.page = Math.max(1, current_page - 1);

        nextPageBtn.textContent = last_page;
        nextPageBtn.dataset.page = last_page;

        goPreviousPageBtn.dataset.page = current_page - 1;

        goLastPageBtn.classList.add("pagination-btn-hidden");
        goNextPageBtn.classList.add("pagination-btn-hidden");

        // Hide the last page button if there are less than 4 pages
        separator.style.display = "none";
        lastPageBtn.style.display = "none";
    } else {
        // Otherwise, show the current page number and the next two page numbers
        let previous_page_value = Math.max(1, current_page - 1);
        previousBtn.textContent = previous_page_value;
        previousBtn.dataset.page = previous_page_value;

        currentPageBtn.textContent = current_page;
        currentPageBtn.dataset.page = current_page;

        let next_page_value = Math.min(last_page, current_page + 1);
        nextPageBtn.textContent = next_page_value;
        nextPageBtn.dataset.page = next_page_value;

        goNextPageBtn.dataset.page = current_page + 1;
        goPreviousPageBtn.dataset.page = current_page - 1;
    }

    lastPageBtn.textContent = last_page;
    lastPageBtn.dataset.page = last_page;
    goLastPageBtn.dataset.page = last_page;

    // Add the 'selected' class to the current page button
    paginationButtons
        .find((btn) => btn.textContent === String(current_page))
        .classList.add("selected");

    let cssClassCurrentPageBtn = last_page < 2 ? "none" : "flex";
    currentPageBtn.style.display = cssClassCurrentPageBtn;
}

export function handlePagination(container, callback) {
    const paginationButtons = container.querySelectorAll("[data-page]");

    paginationButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
            const pageNumber = event.currentTarget.getAttribute("data-page");
            callback(pageNumber);
        });
    });
}

export function handleNumPages(container, callback) {
    const selectorNumPages = container.querySelector(".selector-num-pages");

    selectorNumPages.addEventListener("change", (event) => {
        const pageNumber = event.target.value;
        callback(pageNumber);
    });
}

export function formatDateLong(date) {
    let dateObject = new Date(date);
    let options = { year: "numeric", month: "long", day: "numeric" };
    let formattedDate = dateObject.toLocaleDateString("es-ES", options);

    return formattedDate;
}

export function getDateShort(dateStr) {
    let date = new Date(dateStr);

    let options = { day: "numeric", month: "short", year: "numeric" };
    let formattedDate = date.toLocaleDateString("es-ES", options);

    return formattedDate;
}

export function fillRedsysForm(parametersRedsys) {
    document.getElementById("Ds_SignatureVersion").value =
        parametersRedsys.Ds_SignatureVersion;
    document.getElementById("Ds_MerchantParameters").value =
        parametersRedsys.Ds_MerchantParameters;
    document.getElementById("Ds_Signature").value =
        parametersRedsys.Ds_Signature;
    document.getElementById("tpv_redsys_form").submit();
}

/**
 *
 * @param {*} token
 * Descarga un archivo desde el backend.
 */
export function downloadFileBackend(token) {
    const params = {
        method: "POST",
        url: `${window.backendUrl}/download_file_token`,
        body: {
            token,
        },
        stringify: true,
        download: true,
        loader: true,
        toast: true,
    };

    apiFetch(params);
}

/**
 * Controla el botón de más opciones situado en la parte superior derecha de los contenedores de los cursos
 */
export function moreOptionsBtnHandler() {
    function deleteAllOptionsList() {
        const optionsList = document.querySelectorAll(".options-list");
        optionsList.forEach((list) => {
            list.remove();
        });
    }

    document.body.addEventListener("click", function (event) {
        const moreOptionsBtn = event.target.closest(".more-options-btn");

        deleteAllOptionsList();
        if (moreOptionsBtn) {
            const optionsListTemplate = document.getElementById("options-list");

            const optionsList = optionsListTemplate.content.cloneNode(true);
            moreOptionsBtn.appendChild(optionsList);
        }
    });
}

/**
 *
 * @param {*} buttonId Botón que activa la visibilidad del div
 * @param {*} targetId Div a mostrar u ocultar
 *
 * Controla la visibilidad de un div al hacer clic en un botón.
 */
export function toggleVisibility(buttonId, targetId) {
    const button = document.getElementById(buttonId);
    const target = document.getElementById(targetId);

    if (button && target) {
        button.addEventListener("click", function (event) {
            event.stopPropagation();
            target.classList.toggle("hidden");
        });

        document.addEventListener("click", function (event) {
            if (
                !target.contains(event.target) &&
                !target.classList.contains("hidden")
            ) {
                target.classList.add("hidden");
            }
        });
    }
}
