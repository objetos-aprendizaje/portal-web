import { apiFetch, showFormErrors, resetFormErrors } from "./app";

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("form-suggestions").addEventListener("submit", sendSuggestion);
});


function sendSuggestion() {
    const formData = new FormData(this);

    const params = {
        url: "/suggestions/send_suggestion",
        method: "POST",
        body: formData,
        loader: true,
        toast: true,
    };

    resetFormErrors("form-suggestions");

    apiFetch(params)
        .then(() => {
            document.getElementById("form-suggestions").reset();
        })
        .catch((error) => {
            showFormErrors(error.errors, ["ml-[24px]"]);
        });
}
