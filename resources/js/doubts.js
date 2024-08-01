import { apiFetch, showFormErrors, resetFormErrors } from "./app";

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("form-doubt").addEventListener("submit", sendDoubt);
});

function sendDoubt() {
    const formData = new FormData(this);

    const params = {
        url: "/doubts/send_doubt",
        method: "POST",
        body: formData,
        loader: true,
        toast: true,
    };

    resetFormErrors("form-doubt");

    apiFetch(params)
        .then(() => {
            document.getElementById("form-doubt").reset();
        })
        .catch((error) => {
            showFormErrors(error.errors, ["ml-[24px]"]);
        });
}
