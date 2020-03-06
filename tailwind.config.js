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
    /* eslint-disable import/no-unresolved */
    plugins: [require('@tailwindcss/ui')]
};
