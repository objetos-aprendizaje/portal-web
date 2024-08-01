import { accordionControls, apiFetch, fillStarsHover, fillStars } from "./app";

document.addEventListener("DOMContentLoaded", function () {
    accordionControls();
    fillStarsHover();
    clickStars();
});

// Detecta el número de estrellas que se le da al curso
function clickStars() {
    var stars = document.querySelectorAll(".stars-califications svg");
    stars.forEach(function (star, index) {
        star.addEventListener("click", function () {
            const starsClicked = index + 1;
            calificateCourse(starsClicked);
        });
    });
}

// Califica el curso
function calificateCourse(starsClicked) {

    const course_uid = document.getElementById("course_uid").value;

    const params = {
        url: "/course/calificate",
        method: "POST",
        body: {
            stars: starsClicked,
            course_uid: course_uid
        },
        stringify: true,
        toast: true,
        loader: true
    };

    apiFetch(params).then(() => {
        getCourseCalification();
    });

}

// Obtiene la calificación general del curso
function getCourseCalification() {
    const course_uid = document.getElementById("course_uid").value;

    const params = {
        url: "/course/get_course_calification",
        body: {
            course_uid
        },
        method: "POST",
        stringify: true,
        toast: false,
        loader: true
    };

    apiFetch(params).then((response) => {
        updateCalification(response.calification);
    });
}

// Actualiza la calificación general del curso y las estrellas que se muestran en cuando a la
// calificación del curso aportada por el usuario
function updateCalification(calification) {
    let calificationContainer = document.getElementById("average-calification-container");
    calificationContainer.classList.remove("hidden");
    calificationContainer.classList.add("flex");

    document.getElementById("average-calification").innerText = calification;
    document.getElementById("avg-calification").value = calification;
}
