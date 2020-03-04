/* eslint-disable global-require */
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    theme: {
        fontFamily: {
            sans: ['Inter var', ...defaultTheme.fontFamily.sans]
        },
        extend: {}
    },
    variants: {},
    plugins: [require('@tailwindcss/ui')]
};
