document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("mobile-menu-btn")
        .addEventListener("click", toggleMobileMenu);
    document
        .getElementById("overlay-layer-menu")
        .addEventListener("click", hideMobileMenu);


    var myAccountButton = document.getElementById("my-account-btn");

    if (myAccountButton) {
        myAccountButton.addEventListener("click", toogleAccountMenu);
    }

    toogleSubmenuMobile();

});

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

function toogleAccountMenu() {
    document.getElementById("my-account-menu").classList.toggle("hidden");
}
