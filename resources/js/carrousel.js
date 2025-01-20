import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';

document.addEventListener("DOMContentLoaded", function () {
    new Swiper(".swiper-container", {
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        slidesPerView: 1,
        spaceBetween: 50,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        modules: [Navigation],
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
