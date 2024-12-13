import { showModal } from "./modal_handler";
import { apiFetch } from "./app";
import { toggleVisibility } from "./app.js";

document.addEventListener("DOMContentLoaded", function () {
    controlNotification();
    toggleVisibility("notifications-desktop-btn", "notification-box");
});

// Configura el comportamiento al hacer clic en cada notificación
function controlNotification() {
    const notificationElements = document.querySelectorAll(".notification");

    notificationElements.forEach(function (notificationElement) {
        notificationElement.addEventListener("click", function (event) {
            const notificationUid =
                notificationElement.dataset.notification_uid;
            const notificationType =
                notificationElement.dataset.notification_type;

            if (notificationType === "general_notification")
                loadGeneralNotification(notificationUid);
            else
                loadGeneralNotificationAutomatic(
                    notificationUid,
                    notificationElement
                );
        });
    });
}

function loadGeneralNotification(notificationUid, notificationElement) {
    const params = {
        method: "GET",
        loader: true,
    };

    apiFetch({
        ...params,
        url:
            "/notifications/general/get_general_notification_user/" +
            notificationUid,
    }).then((data) => {
        openNotification(data);
        markReadNotification(data.uid);
    });
}

function loadGeneralNotificationAutomatic(
    notificationUid,
    notificationElement
) {
    const params = {
        method: "GET",
        loader: true,
    };

    apiFetch({
        ...params,
        url:
            "/notifications/general/get_general_notification_automatic_user/" +
            notificationUid,
    }).then((data) => {
        const urlAndText = getUrlAndTextButtonRedirectNotificationAutomatic(
            data.entity,
            data.entity_uid
        );

        openNotification(data, urlAndText);
        markReadNotification(notificationElement);
    });
}

function getUrlAndTextButtonRedirectNotificationAutomatic(entity, entityUid) {
    if (entity === "course_status_change_finished") {
        return {
            url: "/profile/my_courses/historic",
            text: "Ir al curso",
        };
    } else if (entity === "course_status_change_development") {
        return {
            url: "/profile/my_courses/enrolled",
            text: "Ir al curso",
        };
    } else if (entity === "course_status_change_finished") {
        return {
            url: "/profile/my_courses/historic",
            text: "Ir al curso",
        };
    } else if (entity === "course_status_change_inscription") {
        return {
            url: "/course/" + entityUid,
            text: "Ir al curso",
        };
    }
}

function openNotification(notification, urlAndText = false) {
    fillNotification(notification, urlAndText);
    showModal("notification-info-modal", notification.title);
}

// Abre un modal con los detalles de la notificación
/**
 *
 * @param {*} notification datos de la notificación
 * @param {*} type Si el tipo es de notificación automática, se mostrará un botón de acción
 * para redirigir al usuario a la página correspondiente.
 */
function fillNotification(notification, urlAndText = false) {
    document.getElementById("notification-description").innerHTML =
        notification.description;

    let actionBtnContainer = document.getElementById("action-btn-container");

    if (urlAndText) {
        actionBtnContainer.classList.remove("hidden");

        let btnAction = document.getElementById("notification-action-btn");
        // URL del botón
        btnAction.querySelector("span").innerHTML = urlAndText.text;

        let btnActionHref = document.getElementById("notification-action-href");
        btnActionHref.href = urlAndText.url;
    } else {
        actionBtnContainer.classList.add("hidden");
    }
}

// Marca una notificación como leída y actualiza el indicador visual
function markReadNotification(notificationUid) {
    const notificationDivs = document.querySelectorAll(
        `.notification[data-notification_uid="${notificationUid}"]`
    );

    notificationDivs.forEach(notificationDiv => {
        const notReadDiv = notificationDiv.querySelector(".not-read");
        if (notReadDiv) notReadDiv.remove();
    });

    const unreadNotifications = checkUnreadNotifications();

    if (!unreadNotifications) {
        const notificationDots =
            document.getElementsByClassName("notification-dot");
        for (let i = 0; i < notificationDots.length; i++) {
            notificationDots[i].classList.add("hidden");
        }
    }
}

// Verifica si hay notificaciones no leídas restantes
function checkUnreadNotifications() {
    const notificationBox = document.getElementById("notification-box");

    const notReadDiv = notificationBox.querySelector(".not-read");

    return notReadDiv ? true : false;
}
