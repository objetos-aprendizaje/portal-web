document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("mobile-menu-btn")
        .addEventListener("click", toggleMobileMenu);
    document
        .getElementById("overlay-layer-menu")
        .addEventListener("click", hideMobileMenu);
    document
        .getElementById("your-account-option-btn")
        .addEventListener("click", showAccountMenu);
    var myAccountButton = document.getElementById("my-account-btn");

    if (myAccountButton) {
        myAccountButton.addEventListener("click", toogleAccountMenu);
    }

    document
        .getElementById("main-menu-btn")
        .addEventListener("click", showOptionsMenu);
});

function toggleMobileMenu() {
    let mobileMenu = document.getElementById("mobile-menu");
    let overlay = document.getElementById("overlay-layer-menu");

    mobileMenu.classList.toggle("hidden");
    overlay.classList.toggle("hidden");
}

function hideMobileMenu() {
    let mobileMenu = document.getElementById("mobile-menu");
    let overlay = document.getElementById("overlay-layer-menu");

    mobileMenu.classList.add("hidden");
    overlay.classList.add("hidden");
}

function showAccountMenu() {
    var optionsMenu = document.getElementById("menu-mobile-options");
    var accountMenu = document.getElementById("menu-mobile-account");

    optionsMenu.classList.remove("slide-left-center");
    optionsMenu.classList.add("slide-left");

    accountMenu.classList.remove("not-show");
    accountMenu.classList.add("slide-right-center");
}

function showOptionsMenu() {
    var optionsMenu = document.getElementById("menu-mobile-options");
    var accountMenu = document.getElementById("menu-mobile-account");

    optionsMenu.classList.remove("slide-left");
    optionsMenu.classList.add("slide-left-center");

    accountMenu.classList.remove("slide-right-center");
    accountMenu.classList.add("slide-right");

}
function toogleAccountMenu() {
    document.getElementById("my-account-menu").classList.toggle("hidden");
}
