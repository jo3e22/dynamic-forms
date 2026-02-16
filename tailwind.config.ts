import type { Config } from 'tailwindcss';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        tealbrand: {
            DEFAULT: '#104547',
            50:  '#e6f7f7',
            100: '#b3ebeb',
            200: '#80dfdf',
            300: '#4dd3d3',
            400: '#26c6c6',
            500: '#104547', // main
            600: '#0d393a',
            700: '#092c2d',
            800: '#061f20',
            900: '#031213',
        },
        goldbrand: {
            DEFAULT: '#CFB53B',
            50:  '#fcf8e3',
            100: '#f9eec0',
            200: '#f6e39c',
            300: '#f3d978',
            400: '#f0cf54',
            500: '#CFB53B', // main
            600: '#a6902e',
            700: '#7d6b22',
            800: '#554616',
            900: '#2c2109',
        },
        silverbrand: {
            DEFAULT: '#848482',
            50:  '#f7f7f7',
            100: '#e3e3e3',
            200: '#cecece',
            300: '#bababa',
            400: '#a5a5a5',
            500: '#848482', // main
            600: '#686867',
            700: '#4d4d4d',
            800: '#313131',
            900: '#161616',
        },
      },
    },
  },
  plugins: [],
} satisfies Config;