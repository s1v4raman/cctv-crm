import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

// The theme colour (Profile -> Theme) is --crm-accent; every blue shade is mixed from it so
// buttons, links, tabs and badges follow the colour the user picks. 600 is the colour itself.
const accentShade = (mix) => ({ opacityValue }) => {
    if (opacityValue === undefined || opacityValue === 1 || opacityValue === '1') return mix;
    const amount = typeof opacityValue === 'number' ? `${opacityValue * 100}%` : `calc(${opacityValue} * 100%)`;
    return `color-mix(in srgb, ${mix} ${amount}, transparent)`;
};
const tint = (pct) => accentShade(`color-mix(in srgb, var(--crm-accent, #2563eb) ${pct}%, #fff)`);
const shade = (pct) => accentShade(`color-mix(in srgb, var(--crm-accent, #2563eb) ${pct}%, #000)`);
export const accent = {
    50: tint(6),
    100: tint(12),
    200: tint(24),
    300: tint(42),
    400: tint(68),
    500: tint(85),
    600: accentShade('var(--crm-accent, #2563eb)'),
    700: shade(82),
    800: shade(64),
    900: shade(48),
    950: shade(30),
};

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
                brand: accent,
                // One accent colour: blue and the decorative hues used across the views are
                // all the theme colour. Green, amber and red stay for status (done, waiting, problem).
                blue: accent,
                indigo: accent,
                violet: accent,
                purple: accent,
                fuchsia: accent,
                sky: accent,
                cyan: accent,
                teal: colors.emerald,
                lime: colors.emerald,
                pink: colors.rose,
                orange: colors.amber,
            },
        },
    },

    plugins: [forms],
};
