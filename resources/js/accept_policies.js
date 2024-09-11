import { apiFetch } from "./app.js";

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("submit-acceptance-policies")
        .addEventListener("submit", submitAcceptancePolicies);
});

function submitAcceptancePolicies() {
    const formData = new FormData(this);
    const form = document.getElementById("submit-acceptance-policies");

    const checkedBoxes = form.querySelectorAll(
        'input[type="checkbox"]:checked'
    );

    const acceptedPolicies = [];

    checkedBoxes.forEach((box) => {
        if (box.checked) acceptedPolicies.push(box.name);
    });

    formData.append("acceptedPolicies", JSON.stringify(acceptedPolicies));

    const params = {
        url: "/accept_policies/submit",
        method: "POST",
        body: {
            acceptedPolicies,
        },
        loader: true,
        stringify: true,
        toast: true
    };

    apiFetch(params).then(() => {
        window.location.href = "/";
    });
}
