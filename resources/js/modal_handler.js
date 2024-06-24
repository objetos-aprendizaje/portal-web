document.addEventListener("DOMContentLoaded", function () {

    // Evento para cerrar el modal
    let closeButtons = document.querySelectorAll(".close-modal-btn");
    closeButtons.forEach(function (button) {
        button.addEventListener("click", function (e) {
            e.stopImmediatePropagation();
            const modalId = this.getAttribute("data-modal-id");
            hideModal(modalId);
        });
    });
});

// Mostrar el modal
export function showModal(modalId, title = false) {

    const modal = document.getElementById(modalId);
    modal.style.display = 'block';

    if (title) modal.querySelector('.modal-title').textContent = title;
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.classList.add('open');
        document.body.classList.add('modal-open');
    }, 10);
}

// Ocultar el modal
export function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('open');
        document.body.classList.remove('modal-open');
    }, 300);
}
