import {
    updateInputImage,
    resetFormErrors,
    showFormErrors,
    apiFetch,
} from "./app";

document.addEventListener("DOMContentLoaded", function () {
    updateInputImage();
    initHandlers();
});

function initHandlers() {
    document
        .getElementById("user-profile-form")
        .addEventListener("submit", submitUserProfileForm);
}

function submitUserProfileForm() {
    const formData = new FormData(this);

    const params = {
        method: "POST",
        body: formData,
        toast: true,
        loader: true,
        url: "/profile/update_account/update",
    };

    resetFormErrors("user-form");

    apiFetch(params)
        .then(() => {})
        .catch((data) => {
            showFormErrors(data.errors);
        });
}

