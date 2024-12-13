document.addEventListener("DOMContentLoaded", function () {
    handleMenu();
});

// Muestra u oculta los submenús de la barra de navegación de mi perfil
function handleMenu() {
    document.querySelectorAll(".toggle-submenu").forEach((item) => {
        item.addEventListener("click", function () {
            const subMenu = this.nextElementSibling;
            if (subMenu.classList.contains("hidden")) {
                subMenu.classList.remove("hidden");

                this.querySelector(".icon-down").classList.add("hidden");
                this.querySelector(".icon-up").classList.remove("hidden");
            } else {
                subMenu.classList.add("hidden");

                this.querySelector(".icon-down").classList.remove("hidden");
                this.querySelector(".icon-up").classList.add("hidden");
            }
        });
    });
}
