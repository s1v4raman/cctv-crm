import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // The CRM uses the bundled Roboto everywhere (matches --font-sans in app.css).
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
                // Outfit is only loaded by the public marketing page (welcome.blade.php);
                // everywhere else font-heading falls back to Roboto.
                heading: ['"Outfit"', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                },
            },
        },
    },

    plugins: [forms],
};
