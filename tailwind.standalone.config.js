import baseConfig, { accent } from './tailwind.config.js';

/**
 * Plain Tailwind for the standalone pages built from resources/css/standalone.css
 * (worker portal, payment checkout, mobile scanner): same content scan and dark mode
 * as the CRM, but without the forms plugin and font overrides, matching the
 * Tailwind Play CDN defaults these pages were designed against.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    darkMode: baseConfig.darkMode,
    content: baseConfig.content,
    // Blue follows the theme colour here too (the worker portal loads the theme)
    theme: { extend: { colors: { blue: accent, indigo: accent, purple: accent, violet: accent, sky: accent } } },
    plugins: [],
};
