import { toggleVisibility } from "./app.js";

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("mobile-menu-btn")
        .addEventListener("click", function () {
            hideMobileMenus();
            toggleMobileMenu();
        });

    document
        .getElementById("notifications-btn")
        .addEventListener("click", function () {
            hideMobileMenus();
            toogleNotificationsMenu();
        });

    document
        .getElementById("user-menu-mobile-btn")
        .addEventListener("click", function () {
            hideMobileMenus();
            toogleUserMenuMobile();
        });

    document
        .getElementById("overlay-layer-menu")
        .addEventListener("click", hideMobileMenus);

    // Cuando hay un cambio de resolución
    window.addEventListener("resize", function () {
        hideMobileMenus();
    });

    toggleVisibility("my-account-btn", "my-account-menu");

    toogleSubmenuMobile();
});

function toggleMobileMenu() {
    let mobileMenu = document.getElementById("mobile-menu");
    let overlay = document.getElementById("overlay-layer-menu");

    mobileMenu.classList.toggle("hidden");
    overlay.classList.toggle("hidden");
}

function toogleNotificationsMenu() {
    let notificationsMobileMenu = document.getElementById(
        "notifications-mobile-menu"
    );
    let overlay = document.getElementById("overlay-layer-menu");

    notificationsMobileMenu.classList.toggle("hidden");
    overlay.classList.toggle("hidden");
}

function toogleUserMenuMobile() {
    hideMobileMenus();
    let notificationsMobileMenu = document.getElementById("user-mobile-menu");
    let overlay = document.getElementById("overlay-layer-menu");

    notificationsMobileMenu.classList.toggle("hidden");
    overlay.classList.toggle("hidden");
}

function hideMobileMenus() {
    let mobileMenu = document.getElementById("mobile-menu");
    let mobileNotificationsMenu = document.getElementById(
        "notifications-mobile-menu"
    );
    let mobileUserMenu = document.getElementById("user-mobile-menu");

    let overlay = document.getElementById("overlay-layer-menu");

    mobileMenu.classList.add("hidden");
    mobileNotificationsMenu.classList.add("hidden");
    mobileUserMenu.classList.add("hidden");
    overlay.classList.add("hidden");
}

// Función para mostrar y ocultar los submenús en la versión móvil
function toogleSubmenuMobile() {
    const optionsSubmenu = document.querySelectorAll(".option-submenu");

    optionsSubmenu.forEach((option) => {
        option.addEventListener("click", function () {
            const submenu = this.querySelector(".submenu");

            const iconClosed = this.querySelector(".icon-closed");
            const iconOpenned = this.querySelector(".icon-openned");

            if (iconClosed && iconOpenned) {
                iconClosed.classList.toggle("hidden");
                iconOpenned.classList.toggle("hidden");
            }

            if (submenu) {
                submenu.classList.toggle("hidden");
                submenu.classList.toggle("menu-mobile");
            }
        });
    });
}
