import { showModal } from "./modal_handler";
import { apiFetch, formatDateTime } from "./app";

document.addEventListener("DOMContentLoaded", function () {
    controlNotification();

    // Configura el botón de la campana para controlar la visibilidad de las notificaciones
    const bellButton = document.getElementById("bell-btn");
    const notificationBox = document.getElementById("notification-box");

    // Evento para alternar la visibilidad de notificationBox al hacer clic en bellButton
    if (bellButton) {
        bellButton.addEventListener("click", function (event) {
            event.stopPropagation();
            toggleNotification();
        });
    }

    // Evento para cerrar notificationBox si se hace clic fuera de él
    if(notificationBox) {
        document.addEventListener("click", function (event) {
            if (
                !notificationBox.contains(event.target) &&
                !notificationBox.classList.contains("hidden")
            ) {
                notificationBox.classList.add("hidden");
            }
        });
    }

});

// Alterna la visibilidad del contenedor de notificaciones.
function toggleNotification() {
    const notificationBox = document.querySelector("#notification-box");

    if (notificationBox) {
        notificationBox.classList.toggle("hidden");
    }
}

// Configura el comportamiento al hacer clic en cada notificación
function controlNotification() {
    const notificationElements = document.querySelectorAll(".notification");

    notificationElements.forEach(function (notificationElement) {
        notificationElement.addEventListener("click", function (event) {
            loadNotification(notificationElement);
        });
    });
}

// Carga los detalles de una notificación específica
function loadNotification(notificationElement) {
    const notificationUid = notificationElement.dataset.notification_uid;
    const notificationType = notificationElement.dataset.notification_type;

    if (notificationType === "general_notification") {
        loadGeneralNotification(notificationUid, notificationElement);
    } else {
        loadGeneralNotificationAutomatic(notificationUid, notificationElement);
    }
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
        markReadNotification(notificationElement);
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
function markReadNotification(notificationDiv) {
    const notReadDiv = notificationDiv.querySelector(".not-read");

    if (notReadDiv) notReadDiv.remove();

    const unreadNotifications = checkUnreadNotifications();

    if (!unreadNotifications) {
        document.getElementById("notification-dot").classList.add("hidden");
    }
}

// Verifica si hay notificaciones no leídas restantes
function checkUnreadNotifications() {
    const notificationBox = document.getElementById("notification-box");

    const notReadDiv = notificationBox.querySelector(".not-read");

    return notReadDiv ? true : false;
}
