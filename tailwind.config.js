/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                bps: {
                    navy: '#0A3866',
                    darknavy: '#062442',
                    orange: '#E67E22',
                    darkorange: '#D35400',
                    blue: '#2980B9',
                    teal: '#16A085',
                    softgray: '#F8F9FA',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                print: ['Roboto', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
