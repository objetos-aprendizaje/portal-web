import { apiFetch } from "../../app";

document.addEventListener("DOMContentLoaded", function () {
    controlChangesEmailNotificationsCheckboxes();

    document
        .getElementById("save-notifications-btn")
        .addEventListener("click", saveNotifications);
});

function controlChangesEmailNotificationsCheckboxes() {
    let otherEmailNotificationsCheckboxes = document.querySelectorAll(
        ".email-notification-type, .automatic-email-notification-type"
    );

    let emailNotificationsCheckbox = document.querySelector(
        "#email_notifications_allowed"
    );

    emailNotificationsCheckbox.addEventListener("change", function () {
        // Marca o desmarca los otros checkboxes para que coincidan con el estado del checkbox "Notificaciones por email"
        otherEmailNotificationsCheckboxes.forEach(function (checkbox) {
            checkbox.checked = emailNotificationsCheckbox.checked;
        });
    });

    otherEmailNotificationsCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            // Si el checkbox de tipo está marcado, marca también el checkbox "Notificaciones por email"
            if (checkbox.checked) {
                emailNotificationsCheckbox.checked = true;
            }
        });
    });
}

function saveNotifications() {
    const emailNotificationTypesUnchecked = document.querySelectorAll(
        ".email-notification-type:not(:checked)"
    );

    const emailNotificationTypesUncheckedValues = Array.from(
        emailNotificationTypesUnchecked
    ).map((checkbox) => checkbox.value);

    const automaticEmailNotificationTypesUnchecked = document.querySelectorAll(
        ".automatic-email-notification-type:not(:checked)"
    );

    const automaticEmailNotificationTypesUncheckedValues = Array.from(
        automaticEmailNotificationTypesUnchecked
    ).map((checkbox) => checkbox.value);

    const emailNotificationsAllowed = document.getElementById(
        "email_notifications_allowed"
    ).checked
        ? 1
        : 0;

    const params = {
        url: "/profile/notifications/email/save",
        method: "POST",
        body: {
            email_notification_types_disabled:
                emailNotificationTypesUncheckedValues,
            automatic_email_notification_types_disabled:
                automaticEmailNotificationTypesUncheckedValues,
            email_notifications_allowed: emailNotificationsAllowed,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params);
}
