import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
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
                // Numbers, codes and amounts use Roboto too (body sets tabular digits so
                // columns still line up); a typewriter font made figures look generated.
                mono: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            // Roboto is bundled in 400, 500 and 700: extra-bold and black resolve to bold
            fontWeight: {
                extrabold: '700',
                black: '700',
            },
            // Two corner sizes app-wide: 6px for controls, 8px for cards and panels
            // (--radius-control / --radius-card in app.css). Pills stay rounded-full.
            borderRadius: {
                lg: '6px',
                xl: '8px',
                '2xl': '8px',
                '3xl': '8px',
            },
            // Cards and buttons are flat with a border. Dropdowns and modals keep the
            // larger shadows so they still lift off the page.
            boxShadow: {
                '2xs': 'none',
                xs: 'none',
                sm: 'none',
                DEFAULT: 'none',
                md: 'none',
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
                // One accent colour: the decorative hues used across the views fold into
                // blue. Green, amber and red stay for status (done, waiting, problem).
                indigo: colors.blue,
                violet: colors.blue,
                purple: colors.blue,
                fuchsia: colors.blue,
                sky: colors.blue,
                cyan: colors.blue,
                teal: colors.emerald,
                lime: colors.emerald,
                pink: colors.rose,
                orange: colors.amber,
            },
        },
    },

    plugins: [forms],
};
