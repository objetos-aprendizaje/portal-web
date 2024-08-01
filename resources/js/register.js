document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("registerFormDesktop").addEventListener("submit", registerForm);
    document.getElementById("registerFormMobile").addEventListener("submit", registerForm);


});

function registerForm(event) {
    event.preventDefault();

    const formData = new FormData(this);
}
