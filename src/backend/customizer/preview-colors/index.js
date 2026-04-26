/*
 * Webpack entry for the Customizer-only Color palette control.
 *
 * Bundles a clone of the frontend overlay's JS + SCSS, restyled to match
 * the WP Customizer's native chrome. Independent from the frontend bundle:
 * touching this version does not affect `?preview-colors=1`, and vice versa.
 *
 * Output:
 *   build/js/backend/customizer/preview-colors.js   (+ .min.js)
 *   build/css/backend/customizer/preview-colors.css (+ .min.css, -rtl variants)
 */
import './customizer.scss';
import './customizer.js';
