import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Cairo", ...defaultTheme.fontFamily.sans],
                cairo: ["Cairo", "sans-serif"],
            },
            colors: {
                // Primary Dark Blue - من التصميم المرجعي
                primary: {
                    50: "#e8f1f8",
                    100: "#d1e3f1",
                    200: "#a3c7e3",
                    300: "#75abd5",
                    400: "#478fc7",
                    500: "#1973b9",
                    600: "#145c94",
                    700: "#0f456f",
                    800: "#0a2e4a",
                    900: "#0A3A5C", // اللون الأساسي من التصميم
                    950: "#051d2e",
                },
                // Yellow/Gold - من التصميم المرجعي
                yellow: {
                    50: "#fffef5",
                    100: "#fffceb",
                    200: "#fff9d6",
                    300: "#fff5c2",
                    400: "#fff2ad",
                    500: "#ffef99",
                    600: "#ffe566",
                    700: "#FFD700", // الأصفر الذهبي من التصميم
                    800: "#ccac00",
                    900: "#998100",
                },
                // Background Colors
                bg: {
                    main: "#F5F7FA",
                    white: "#FFFFFF",
                    "light-yellow": "#FFF9E6",
                },
                // Text Colors
                text: {
                    primary: "#0A3A5C",
                    secondary: "#6B7280",
                    muted: "#9CA3AF",
                },
                // Status Colors
                status: {
                    pending: "#F59E0B",
                    active: "#10B981",
                    rejected: "#EF4444",
                },
                // Border Colors
                border: {
                    light: "#E5E7EB",
                    medium: "#D1D5DB",
                },
                // Gray Scale
                gray: {
                    50: "#F9FAFB",
                    100: "#F3F4F6",
                    200: "#E5E7EB",
                    300: "#D1D5DB",
                    400: "#9CA3AF",
                    500: "#6B7280",
                    600: "#4B5563",
                    700: "#374151",
                    800: "#1F2937",
                    900: "#111827",
                },
            },
            boxShadow: {
                sm: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
                DEFAULT:
                    "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                md: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                lg: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                xl: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                card: "0 1px 3px rgba(0, 0, 0, 0.05)",
                "card-hover": "0 4px 12px rgba(0, 0, 0, 0.1)",
            },
            borderRadius: {
                DEFAULT: "0.5rem",
                lg: "0.75rem",
                xl: "1rem",
                "2xl": "1.25rem",
            },
            spacing: {
                18: "4.5rem",
                88: "22rem",
            },
            fontSize: {
                "2xs": ["0.625rem", { lineHeight: "0.75rem" }],
            },
            keyframes: {
                fadeInUp: {
                    "0%": { opacity: "0", transform: "translateY(10px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
                slideIn: {
                    "0%": { opacity: "0", transform: "translateX(-20px)" },
                    "100%": { opacity: "1", transform: "translateX(0)" },
                },
            },
            animation: {
                "fade-in-up": "fadeInUp 0.5s ease-out forwards",
                "slide-in": "slideIn 0.5s ease-out forwards",
            },
        },
    },
    plugins: [],
};
