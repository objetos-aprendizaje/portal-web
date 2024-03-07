    const menuButton = document.getElementById("menu-button");
    const menu = document.querySelector("[role='menu']");

    const menuNotifyButton = document.getElementById("menu-notify-button");
    const menuNotify = document.querySelector("[role='menu-notify']");
    
    menuButton.addEventListener("click", () => {
        const isExpanded = menuButton.getAttribute("aria-expanded") === "true";
        menuButton.setAttribute("aria-expanded", !isExpanded);
        menu.style.display = isExpanded ? "none" : "block";

    });

    menuNotifyButton.addEventListener("click", () => {
        const isExpanded = menuNotifyButton.getAttribute("aria-expanded") === "true";
        menuNotifyButton.setAttribute("aria-expanded", !isExpanded);

        menuNotify.style.display = isExpanded ? "none" : "block";
    });
    
    // Cerrar el menú si se hace clic fuera de él
    document.addEventListener("click", (event) => {
        if (!menu.contains(event.target) && event.target !== menuButton) {
            menuButton.setAttribute("aria-expanded", "false");
            menu.style.display = "none";
        }
        
        if (!menuNotify.contains(event.target) && event.target !== menuNotifyButton) {
            menuNotifyButton.setAttribute("aria-expanded", "false");
            menuNotify.style.display = "none";
        }
    });

    // Control menu desplegable movil

    const mobileMenuButton = document.getElementById('mobile-menu');
    const mobileMenu = document.getElementById('menu');

    const iconOpen = document.getElementById('menu-icon1');
    const iconClose = document.getElementById('menu-icon2');    


    mobileMenuButton.addEventListener('click', function () {
        var menu = document.getElementById("menu");
        if (menu.style.display === "none" || menu.style.display === "") {
          menu.style.display = "block";
        } else {
          menu.style.display = "none";
        }
        
        if(mobileMenu.style.display == 'block') {
            iconClose.style.display = 'block';
            iconOpen.style.display = 'none';

        } else if(mobileMenu.style.display == 'none') {
            iconClose.style.display = 'none';
            iconOpen.style.display = 'block';
            
        }
    });


    // Control menu desplegable navbar movil menu user

    const menuUser = document.querySelector('.menu-cuenta');
    const menuBackUser = document.querySelector('.menu-back-icon');
    const menuGeneral = document.querySelector('.menu-general');
    const cuentaLink = document.querySelector('.cuenta-icon');
    

    cuentaLink.addEventListener('click', function () {
        menuGeneral.classList.toggle('hidden');
        menuUser.classList.toggle('hidden');
    });

    menuBackUser.addEventListener('click', function () {
        menuGeneral.classList.toggle('hidden');
        menuUser.classList.toggle('hidden');
    });
