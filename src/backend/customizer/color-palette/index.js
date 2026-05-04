/*
 * Webpack entry for the Customizer-only Color palette control.
 *
 * Bundles the React panel + SCSS, styled to match the WP Customizer's
 * native chrome.
 *
 * Output:
 *   build/js/backend/customizer/color-palette.js   (+ .min.js)
 *   build/css/backend/customizer/color-palette.css (+ .min.css, -rtl variants)
 */
import './customizer.scss';
import './customizer.js';
