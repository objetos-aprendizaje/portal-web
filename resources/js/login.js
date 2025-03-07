import { apiFetch } from "./app.js";
import { showToast } from "./toast.js";

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("loginFormDesktop")
        .addEventListener("submit", loginForm);

    document
        .getElementById("loginFormMobile")
        .addEventListener("submit", loginForm);

    const resendEmailConfirmationLinks = document.querySelectorAll(
        ".resend-email-confirmation"
    );

    resendEmailConfirmationLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            const email = this.getAttribute("data-email-account");
            resendEmailConfirmation(email);
        });
    });

    showErrors();
});

function showErrors() {
    if (!window.errors) {
        return;
    }

    window.errors.forEach((error) => {
        showToast(error, "error");
    });
}

function resendEmailConfirmation(email) {
    const params = {
        method: "POST",
        url: "/register/resend_email_confirmation",
        body: { email },
        toast: true,
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {});
}

function loginForm(event) {
    // Bloqueamos el botón submit
    const submitButton = document.querySelector('button[type="submit"]');
    submitButton.disabled = true;
}
