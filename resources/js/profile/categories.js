import { apiFetch } from "../app.js";

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("save-categories-btn")
        .addEventListener("click", saveCategories);
});

function saveCategories() {

    let selectedCategories = getSelectedCategories();

    const params = {
        url: "/profile/categories/save_categories",
        method: "POST",
        body: {
            categories: selectedCategories,
        },
        stringify: true,
        loader: true,
        toast: true
    };

    apiFetch(params);

}

function getSelectedCategories() {
    let categories = [];
    document.querySelectorAll(".category-checkbox").forEach((checkbox) => {
        if (checkbox.checked) {
            categories.push(checkbox.value);
        }
    });

    return categories;
}
