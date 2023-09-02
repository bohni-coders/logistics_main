/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ['class', '[data-theme="dark"]'],
    content: ['./app/**/*.{hbs,js}'],
    theme: {
        extend: {
            colors: {
                gray: {
                    50: '#F9FAFB',
                    100: '#EBF1F5',
                    200: '#D9E3EA',
                    300: '#C5D2DC',
                    400: '#9BA9B4',
                    500: '#707D86',
                    600: '#55595F',
                    700: '#33363A',
                    800: '#25282C',
                    900: '#151719',
                },
                sky: {
                    100: '#660BD2',
                    200: '#660BD2',
                    300: '#660BD2',
                    400: '#660BD2',
                    500: '#660BD2',
                    600: '#660BD2',
                    700: '#660BD2',
                    800: '#660BD2',
                    900: '#660BD2',
                },
            },
            fontFamily: {
                inter: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
