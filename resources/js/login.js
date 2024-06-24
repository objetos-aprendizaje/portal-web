import { apiFetch } from "./app.js";

document.addEventListener("DOMContentLoaded", function () {
    console.log('login.js loaded!')
    document.getElementById("loginFormDesktop").addEventListener("submit", loginForm);
});

function loginForm(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const params = {
        method: "POST",
        url: "/login/authenticate",
        body: formData,
        toast: true
    };

    apiFetch(params).then((response) => {
        let urlRedirect = response.urlIntended ? response.urlIntended : '/';
        window.location.href = urlRedirect;
    });
}
