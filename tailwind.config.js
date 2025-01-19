import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Enums/RepairMethod.php',
        './app/Enums/StatusType.php',
    ],
    safelist: [
        // 'border-blue-500',
        // 'border-yellow-500',
        // 'border-green-500',
        // Diğer dinamik sınıflarınızı buraya ekleyebilirsiniz
      ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
