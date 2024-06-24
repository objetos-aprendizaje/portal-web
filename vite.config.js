import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/js/home.js',
                'resources/js/carrousel.js',
                'resources/js/header.js',
                'resources/js/search.js',
                'node_modules/treeselectjs/dist/treeselectjs.css',
                'resources/js/course_info.js',
                'resources/js/educational_resource_info.js',
                "resources/js/toast.js",
                "resources/css/toastify.css",
                "resources/js/doubts.js",
                "resources/js/suggestions.js",
                "resources/js/profile/categories.js",
                "resources/js/cart.js",
                "resources/js/educational_program_info.js",
                "resources/js/profile/notifications.js",
                "resources/js/profile/my_courses.js",
                "resources/js/notifications_handler.js",
                "resources/js/modal_handler.js",
                "resources/js/my_profile.js",
                "resources/js/slider.js",
                "resources/js/login.js",
                'node_modules/flatpickr/dist/flatpickr.css',
                "resources/js/profile/menu.js",
                "resources/js/profile/my_courses/inscribed_courses.js",
                "resources/js/profile/my_courses/enrolled_courses.js",
                "resources/js/profile/my_courses/historic_courses.js",
                "resources/js/profile/my_educational_programs/inscribed_educational_programs.js",
                "resources/js/profile/my_educational_programs/enrolled_educational_programs.js",
                "resources/js/profile/my_educational_programs/historic_educational_programs.js"
            ],
            refresh: true,
        }),
    ],
});
