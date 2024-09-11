import { apiFetch } from "./app";
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("registerFormDesktop").addEventListener("submit", registerForm);
    document.getElementById("registerFormMobile").addEventListener("submit", registerForm);
});

function registerForm() {
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
}


