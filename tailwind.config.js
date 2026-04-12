import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                indigo: {
                    50: '#f4fbff',
                    100: '#e4f6ff',
                    200: '#bdeaff',
                    300: '#8fdcff',
                    400: '#5fcbff',
                    500: '#20a7db',
                    600: '#1294e4',
                    700: '#007dff',
                    800: '#0067d0',
                    900: '#0156ab',
                    950: '#00356d',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
