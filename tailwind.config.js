/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                color_1: "var(--color_1)",
                color_2: "var(--color_2)",
                color_3: "var(--color_3)",
                color_4: "var(--color_4)",
                color_background_elements: "var(--color_background_elements)",
                color_hover: "var(--color_hover)",
                color_hover_2: "var(--color_hover_2)",
                danger: "#E74C3C",
            },
            fontFamily: {
                "medium": ["font-medium", "sans-serif"],
                "regular": ["font-regular", "sans-serif"],
                "bold": ["font-bold", "sans-serif"],
            },
        },
    },
    darkMode: "class",
    container: {
        screens: {
            sm: "100%",
            md: "768px",
            lg: "992px",
            xl: "1200px",
            "2xl": "1600px",
        },
    },
    screens: {
        sm: "480px",
        md: "768px",
        lg: "992px",
        xl: "1200px",
        "2xl": "1600px",
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
