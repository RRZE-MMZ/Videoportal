module.exports = {
  plugins: {
    '@tailwindcss/postcss': {},
    autoprefixer: {},
    'postcss-replace': {
      pattern: /color-adjust/g,
      data: {
        to: 'print-color-adjust'
      }
    }
  }
};