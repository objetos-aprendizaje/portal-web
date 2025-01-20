
import { fillStarsHover, apiFetch, fillStars } from "./app";


document.addEventListener("DOMContentLoaded", function () {

    fillStarsHover();
    handleCalificateResource();
});

/**
 * Función que se encarga de manejar el click en la calificación de un recurso educativo.
 */
function handleCalificateResource() {
    const stars = document.querySelectorAll(".stars-califications svg");

    stars.forEach(function (star, index) {
        star.addEventListener("click", function () {
            // El usuario ha seleccionado index + 1 estrellas
            const selectedStars = index + 1;
            calificateEducationalProgram(selectedStars);
        });
    });
}

/**
 * Función que se encarga de calificar un recurso educativo.
 * @param {*} selectedStars
 */
function calificateEducationalProgram(selectedStars) {
    const params = {
        method: "POST",
        url: "/educational_program/calificate",
        stringify: true,
        loader: true,
        body: {
            educational_program_uid: document.getElementById(
                "educational_program_uid"
            ).value,
            calification: selectedStars,
        },
        toast: true,
    };

    apiFetch(params).then(() => {
        reloadEducationalProgramInfo();
    });
}

/**
 * Función que se encarga de recargar la información del recurso educativo.
 */
function reloadEducationalProgramInfo() {
    const educationalProgramUid = document.getElementById(
        "educational_program_uid"
    ).value;

    const params = {
        method: "GET",
        url: "/educational_program/get_educational_program/" + educationalProgramUid,
        loader: true,
    };

    apiFetch(params).then((response) => {
        fillEducationalProgramInfo(response)
    });
}

function fillEducationalProgramInfo(educational_program) {
    document.getElementById("program-avg-calification-block").classList.remove("hidden");
    document.getElementById("average-calification").innerText = educational_program.average_calification;
    document.getElementById("avg-calification").value = Math.trunc(educational_program.average_calification);
    fillStars(Math.trunc(educational_program.average_calification));
}

