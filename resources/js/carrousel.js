document.addEventListener("DOMContentLoaded", function () {
    var swiper = new Swiper(".swiper-container", {
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            dynamicBullets: true,
        },
        slidesPerView: 1,
        spaceBetween: 50,
        // init: false,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 50,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 50,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 50,
            },
            1286: {
                slidesPerView: 4,
                spaceBetween: 50,
            },
            1536: {
                slidesPerView: 5,
                spaceBetween: 50,
            },
        },

    });
});
