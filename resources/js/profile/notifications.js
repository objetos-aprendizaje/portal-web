import { apiFetch } from "../app";

document.addEventListener("DOMContentLoaded", function () {
    controlChangesGeneralNotificationsCheckboxes();
    controlChangesEmailNotificationsCheckboxes();

    document
        .getElementById("save-notifications-btn")
        .addEventListener("click", saveNotifications);
});

/**
 * Controla los cambios en los checkboxes de notificaciones generales.
 */
function controlChangesGeneralNotificationsCheckboxes() {
    // Encuentra los otros checkboxes
    let otherGeneralNotificationsCheckboxes = document.querySelectorAll(
        ".general-notification-type, .automatic-general-notification-type"
    );

    // Encuentra el checkbox "Notificaciones Generales"
    let generalNotificationsCheckbox = document.querySelector(
        "#general_notifications_allowed"
    );

    // Escucha los eventos de cambio en el checkbox "Notificaciones Generales"
    generalNotificationsCheckbox.addEventListener("change", function () {
        // Marca o desmarca los otros checkboxes para que coincidan con el estado del checkbox "Notificaciones Generales"
        otherGeneralNotificationsCheckboxes.forEach(function (checkbox) {
            checkbox.checked = generalNotificationsCheckbox.checked;
        });
    });

    // Añade un escuchador de eventos a cada checkbox de tipo
    otherGeneralNotificationsCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            // Si el checkbox de tipo está marcado, marca también el checkbox "Notificaciones Generales"
            if (checkbox.checked) {
                generalNotificationsCheckbox.checked = true;
            }
        });
    });
}

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
    // Recoger los uid de los checkboxes marcados con clase notification-type
    const generalNotificationTypesUnchecked = document.querySelectorAll(
        ".general-notification-type:not(:checked)"
    );

    const generalNotificationTypesUncheckedValues = Array.from(
        generalNotificationTypesUnchecked
    ).map((checkbox) => checkbox.value);

    // Recoger los uid de los checkboxes marcados con clase notification-type
    const automaticGeneralNotificationTypesUnchecked =
        document.querySelectorAll(
            ".automatic-general-notification-type:not(:checked)"
        );

    const automaticGeneralNotificationTypesUncheckedValues = Array.from(
        automaticGeneralNotificationTypesUnchecked
    ).map((checkbox) => checkbox.value);

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

    const generalNotificationsAllowed = document.getElementById(
        "general_notifications_allowed"
    ).checked
        ? 1
        : 0;
    const emailNotificationsAllowed = document.getElementById(
        "email_notifications_allowed"
    ).checked
        ? 1
        : 0;

    const params = {
        url: "/profile/notifications/save_notifications",
        method: "POST",
        body: {
            general_notification_types_disabled:
                generalNotificationTypesUncheckedValues,
            automatic_general_notification_types_disabled:
                automaticGeneralNotificationTypesUncheckedValues,
            email_notification_types_disabled:
                emailNotificationTypesUncheckedValues,
            general_notifications_allowed: generalNotificationsAllowed,
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
