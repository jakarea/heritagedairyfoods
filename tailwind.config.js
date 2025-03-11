import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        screens: {
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1312px'
        },
        container: {
            center: true,
            padding: {
                DEFAULT: '1.25rem',
                sm: '2rem',
                md: '2.5rem',
                lg: '2.75rem',
                xl: '1.5rem',
            }
        },
        extend: {
            boxShadow: {
                'first': '0px 4px 10px 0px #00000040',
                'second': '0px 5px 20px 0px #00000040',
            },
            fontFamily: {
                'hind_siliguri': ['Hind Siliguri', 'sans-serif'],
                'inter': ['Inter', 'sans-serif'],
            },
            backgroundColor: {
                'first': '#F53838',
                'second': '#B11116',
                'third': '#2E3192',
                'fourth': '#F7F7F7',
                'five': '#FF9C22',
            },
            borderColor: {
                'first': '#FFFAFA',
            },
            colors: {
                'first': '#2A2A2A',
                'second': '#B11116',
                'third': '#484848',
            },
        },
    },
    plugins: [],
}  