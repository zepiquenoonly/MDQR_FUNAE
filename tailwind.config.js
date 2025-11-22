import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class', // ← ADICIONAR ESTA LINHA

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: '#F15F22',
                'brand-red': '#DC2626', 
                'brand-green': '#16A34A', 
                'brand-blue': '#4c8fddff',
                dark: {
                    primary: '#1a202c',
                    secondary: '#2d3748',
                    accent: '#4a5568',
                    text: {
                        primary: '#f7fafc',
                        secondary: '#e2e8f0',
                        muted: '#a0aec0'
                    }
                }
            }
        },
    },

    plugins: [forms],
};