import { apiFetch } from "./app";

document.addEventListener("DOMContentLoaded", function () {
    if (window.userUid) {
        fillStarsHover();
        handleCalificateResource();
    }
});

/**
 * Función que se encarga de rellenar las estrellas de la calificación al hacer hover sobre ellas.
 */
function fillStarsHover() {
    var stars = Array.from(
        document.querySelectorAll(".stars-califications svg")
    );

    stars.forEach(function (star, index) {
        star.addEventListener("mouseover", function () {
            fillStars(index + 1);
        });

        star.addEventListener("mouseout", function () {
            const avgCalification = document.getElementById("avg-calification").value;
            fillStars(avgCalification);
        });
    });
}

/**
 * Función que se encarga de rellenar las estrellas de la calificación.
 * @param {*} number Número de estrellas a rellenar
 */
function fillStars(number) {
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
 * Función que se encarga de manejar el click en la calificación de un recurso educativo.
 */
function handleCalificateResource() {
    var stars = document.querySelectorAll(".stars-califications svg");

    stars.forEach(function (star, index) {
        star.addEventListener("click", function () {
            // El usuario ha seleccionado index + 1 estrellas
            var selectedStars = index + 1;
            calificateResource(selectedStars);
        });
    });
}

/**
 * Función que se encarga de calificar un recurso educativo.
 * @param {*} selectedStars
 */
function calificateResource(selectedStars) {
    const params = {
        method: "POST",
        url: "/resource/calificate",
        stringify: true,
        loader: true,
        body: {
            educational_resource_uid: document.getElementById(
                "educational_resource_uid"
            ).value,
            calification: selectedStars,
        },
        toast: true,
    };

    apiFetch(params).then(() => {
        reloadResourceInfo();
    });
}

/**
 * Función que se encarga de recargar la información del recurso educativo.
 */
function reloadResourceInfo() {
    const resource_uid = document.getElementById(
        "educational_resource_uid"
    ).value;

    const params = {
        method: "GET",
        url: "/resource/get_resource/" + resource_uid,
        loader: true,
    };

    apiFetch(params).then((response) => {
        fillResourceInfo(response)
    });
}

function fillResourceInfo(resource) {
    document.getElementById("resource-avg-calification-block").classList.remove("hidden");
    document.getElementById("average-calification").innerText = resource.average_calification;
    document.getElementById("avg-calification").value = Math.trunc(resource.average_calification);
    fillStars(Math.trunc(resource.average_calification));
}



