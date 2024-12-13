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

    document
        .getElementById("delete-photo")
        .addEventListener("click", deletePhoto);
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

function deletePhoto() {
    document.getElementById("photo_path_preview").src = "/images/no-user.svg";
    document.getElementById("photo_path").value = "";
    document.getElementById("image-name").textContent =
        "Ningún archivo seleccionado";

    const params = {
        method: "DELETE",
        toast: true,
        loader: true,
        url: "/profile/update_account/delete_image",
    };

    apiFetch(params);
}
