/**
 * Jest configuration. Reuses wp-scripts' babel transform so JSX + ES modules
 * work without an explicit .babelrc — same way @wordpress/scripts test-unit-js
 * runs internally.
 *
 * Theme-specific overrides:
 *   - setupFilesAfterEach mocks window.customifyDashboard so React components
 *     find the bootstrap blob the production page injects via wp_localize_script.
 *   - testMatch limits Jest to tests/js/ + colocated *.test.js to avoid
 *     scanning vendor/build folders.
 */

const path = require( 'path' );
const wpScriptsBabelTransform = path.join(
	__dirname,
	'node_modules/@wordpress/scripts/config/babel-transform'
);

module.exports = {
	preset: '@wordpress/jest-preset-default',
	transform: {
		'\\.[jt]sx?$': wpScriptsBabelTransform,
	},
	setupFilesAfterEnv: [
		'<rootDir>/tests/js/setup.js',
	],
	testMatch: [
		'<rootDir>/tests/js/**/*.test.js',
		'<rootDir>/src/**/*.test.js',
	],
	moduleNameMapper: {
		'\\.(css|scss)$': '<rootDir>/tests/js/__mocks__/style-mock.js',
	},
	transformIgnorePatterns: [
		'/node_modules/(?!(@wordpress)/)',
	],
	collectCoverageFrom: [
		'src/backend/dashboard/**/*.js',
		'!src/backend/dashboard/**/*.test.js',
	],
};
