/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/views/**/*.php",
    "./public/**/*.html",
    "./public/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'page-bg': '#C2B098',
        'login-bg': '#ffffff',
        'input-bg': '#FFE6CC',
        'input-border': '#1F2937',
        'input-text': '#000000',
        'input-focus': '#FFD8A8',
        'navbar-bg': '#191C21',
        'heading': '#FF7043', 
        'heading-hover': '#BF4519',
        'button-text-orange': '#DE541E',
      },
    },
  },
  plugins: [],
}
