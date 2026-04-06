/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/**/*.html', './app/**/*.php'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        parchment: '#faf8f1',
        vellum: '#f4f1e8',
        linen: '#eae6da',
        ink: '#2c2a25',
        copper: '#b5623f',
        'copper-light': '#c8795a',
        'copper-dark': '#9e5436',
        charcoal: '#3d3a34',
        'warm-gray': '#6b675e',
        stone: '#9c9789',
        sand: '#c4bfb3',
        'light-sand': '#ddd9ce',
        dust: '#ece9e0',
        // Dark mode surfaces
        'deep-parchment': '#1a1915',
        'dark-vellum': '#23211b',
        'dark-linen': '#2d2b24',
        'dark-sand': '#3d3a34',
        // Semantic
        success: '#4a7c59',
        error: '#a63d2f',
        warning: '#b8862e',
        info: '#4a6fa5',
      },
      fontFamily: {
        heading: ['Lora', 'Georgia', "'Times New Roman'", 'serif'],
        body: ['Inter', 'system-ui', '-apple-system', "'Segoe UI'", 'sans-serif'],
        code: ["'JetBrains Mono'", "'Fira Code'", "'Source Code Pro'", 'Consolas', 'monospace'],
      },
      maxWidth: {
        prose: '720px',
        layout: '1120px',
      },
    },
  },
  plugins: [],
};
