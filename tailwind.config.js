import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './THEMA/**/*.{jsx,js}',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta de cores laranja principal
                primary: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316', // Laranja principal
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                    950: '#431407',
                },
                // Cores secundárias
                accent: {
                    orange: '#F15F22',
                    coral: '#FF6B4A',
                    peach: '#FFB088',
                },
                // Cores utilitárias mantidas (para compatibilidade)
                brand: '#F15F22',
                'brand-red': '#DC2626',
                'brand-green': '#16A34A',
                'brand-blue': '#4c8fddff',
                // Cores para modo escuro
                dark: {
                    50: '#18181b',
                    100: '#27272a',
                    200: '#3f3f46',
                    300: '#52525b',
                    400: '#71717a',
                    500: '#a1a1aa',
                    600: '#d4d4d8',
                    700: '#e4e4e7',
                    800: '#f4f4f5',
                    900: '#fafafa',
                }
            },
            backdropBlur: {
                xs: '2px',
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                'glass-gradient': 'linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05))',
            },
            boxShadow: {
                'glass': '0 8px 32px 0 rgba(249, 115, 22, 0.15)',
                'glass-lg': '0 16px 48px 0 rgba(249, 115, 22, 0.2)',
                'inner-glass': 'inset 0 0 20px rgba(255, 255, 255, 0.1)',
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'scale-in': 'scaleIn 0.2s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                scaleIn: {
                    '0%': { transform: 'scale(0.9)', opacity: '0' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
            },
        },
    },

    plugins: [forms],
};