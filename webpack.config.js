/**
 * Webpack config extending @wordpress/scripts defaults.
 *
 * Entry points: add new entries to the `entries` object below.
 * Each key becomes a subdirectory under assets/build/.
 *
 * Output:  assets/build/<entry>/index.js  (+ index.asset.php)
 * Source:  src/<entry>/index.js
 */

const path                = require( 'path' );
const defaultConfig       = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config' );

/**
 * All JS entry points.
 * Key   → bundle name  (assets/build/<key>/index.js)
 * Value → source file  (src/<key>/index.js  by convention)
 *
 * Add a new line here whenever you create a new block or admin script.
 */
const entries = {
	'page-settings':  path.resolve( __dirname, 'src/page-settings/index.js' ),
	'header-builder': path.resolve( __dirname, 'src/header-builder/index.js' ),
	'footer-builder': path.resolve( __dirname, 'src/footer-builder/index.js' ),
};

module.exports = {
	...defaultConfig,

	entry: entries,

	output: {
		...defaultConfig.output,
		path:     path.resolve( __dirname, 'assets/build' ),
		filename: '[name]/index.js',
	},
};
