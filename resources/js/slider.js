class Slider {
    constructor(idContainer) {
        this.container = document.getElementById(idContainer);
        this.slides = this.container.getElementsByClassName("mySlides");
        this.slideIndex = 1;
        this.registerEventListeners();
        this.showSlides();
    }

    showSlides() {
        Array.from(this.slides).forEach(slide => slide.style.display = "none");
        this.slides[this.slideIndex - 1].style.display = "block";
    }

    nextSlide() {
        this.slideIndex = (this.slideIndex % this.slides.length) + 1;
        this.showSlides();
    }

    previousSlide() {
        this.slideIndex = (this.slideIndex - 2 + this.slides.length) % this.slides.length + 1;
        this.showSlides();
    }

    registerEventListeners() {
        this.container.querySelector(".prev").addEventListener("click", () => this.previousSlide());
        this.container.querySelector(".next").addEventListener("click", () => this.nextSlide());
    }
}

export function controlSlides(idContainer) {
    new Slider(idContainer);
}
