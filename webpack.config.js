/**
 * Webpack config extending @wordpress/scripts defaults.
 *
 * Source:  src/frontend/  — public-facing JS + SCSS
 *          src/backend/   — admin / Customizer JS + SCSS (React apps, vanilla scripts)
 *          src/fonts/     — font files (static, copied to build/)
 *          src/images/    — image files (static, copied to build/)
 *          src/vendor/    — vendor libs (static, copied to build/)
 *
 * Output:  build/js/[name].js        (JS bundles + .asset.php)
 *          build/css/[name].css      (extracted CSS)
 *          build/css/[name]-rtl.css  (RTL variants)
 *          build/fonts/              (copied from src/fonts/)
 *          build/images/             (copied from src/images/)
 *          build/vendor/             (copied from src/vendor/)
 *
 * build/ is fully gitignored — delete and re-run `npm run build` at any time.
 */

const path                 = require( 'path' );
const webpack              = require( 'webpack' );
const rtlcss               = require( 'rtlcss' );
const defaultConfig        = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CopyPlugin           = require( 'copy-webpack-plugin' );

// ── Custom RTL plugin ────────────────────────────────────────────────────────
// Replaces the default RtlCssPlugin so RTL files also land in build/css/
// instead of build/<entry-name>-rtl.css.
class RtlCssPlugin {
	apply( compiler ) {
		compiler.hooks.compilation.tap( 'CustomRtlCssPlugin', ( compilation ) => {
			compilation.hooks.processAssets.tapAsync(
				{
					name:  'CustomRtlCssPlugin',
					stage: compilation.PROCESS_ASSETS_STAGE_OPTIMIZE,
				},
				( _chunks, callback ) => {
					for ( const chunk of compilation.chunks ) {
						for ( const filename of chunk.files ) {
							if ( ! filename.endsWith( '.css' ) || filename.endsWith( '-rtl.css' ) ) {
								continue;
							}
							const src     = compilation.assets[ filename ].source();
							const rtlSrc  = rtlcss.process( src );
							// Mirror the main CSS path but append -rtl before the extension.
							const rtlName = filename.replace( /\.css$/, '-rtl.css' );
							compilation.assets[ rtlName ] = new webpack.sources.RawSource( rtlSrc );
							chunk.files.add( rtlName );
						}
					}
					callback();
				}
			);
		} );
	}
}

// ── Filter default plugins ───────────────────────────────────────────────────
// Remove MiniCssExtractPlugin (replaced with custom filename pattern below).
// Remove RtlCssPlugin (replaced with CustomRtlCssPlugin above).
const filteredPlugins = defaultConfig.plugins.filter(
	( plugin ) =>
		! ( plugin instanceof MiniCssExtractPlugin ) &&
		plugin.constructor.name !== 'RtlCssPlugin'
);

// ── Patch css-loader / sass-loader ───────────────────────────────────────────
// css-loader: disable URL resolution — fonts/images live beside the output CSS
// and are resolved correctly by the browser at runtime.
// sass-loader: silence the @import deprecation warning — migrating the entire
// SCSS codebase to @use/@forward is deferred; the feature still works fine.
const isCssLoader = ( loader ) =>
	typeof loader === 'object' &&
	loader.loader &&
	/\/css-loader\//.test( loader.loader );

const isSassLoader = ( loader ) =>
	typeof loader === 'object' &&
	loader.loader &&
	/\/sass-loader\//.test( loader.loader );

const patchedRules = defaultConfig.module.rules.map( ( rule ) => {
	if ( ! Array.isArray( rule.use ) ) {
		return rule;
	}
	const hasCssLoader  = rule.use.some( isCssLoader );
	const hasSassLoader = rule.use.some( isSassLoader );
	if ( ! hasCssLoader && ! hasSassLoader ) {
		return rule;
	}
	return {
		...rule,
		use: rule.use.map( ( loader ) => {
			if ( isCssLoader( loader ) ) {
				return { ...loader, options: { ...loader.options, url: false } };
			}
			if ( isSassLoader( loader ) ) {
				return {
					...loader,
					options: {
						...loader.options,
						sassOptions: {
							...( loader.options?.sassOptions || {} ),
							silenceDeprecations: [ 'import' ],
						},
					},
				};
			}
			return loader;
		} ),
	};
} );

// ── Entry points ─────────────────────────────────────────────────────────────
const entries = {
	// Frontend
	'frontend/theme':       path.resolve( __dirname, 'src/frontend/index.js' ),
	'frontend/woocommerce': path.resolve( __dirname, 'src/frontend/woocommerce.js' ),

	// Backend — React apps
	'backend/header-builder': path.resolve( __dirname, 'src/backend/header-builder/index.js' ),
	'backend/footer-builder': path.resolve( __dirname, 'src/backend/footer-builder/index.js' ),
	'backend/page-settings':  path.resolve( __dirname, 'src/backend/page-settings/index.js' ),

	// Backend — Customizer
	'backend/customizer/customizer':         path.resolve( __dirname, 'src/backend/customizer/customizer.js' ),
	'backend/customizer/auto-css':           path.resolve( __dirname, 'src/backend/customizer/js/auto-css.js' ),
	'backend/customizer/control':            path.resolve( __dirname, 'src/backend/customizer/js/control.js' ),
	'backend/customizer/color-picker-alpha': path.resolve( __dirname, 'src/backend/customizer/js/color-picker-alpha.js' ),
	'backend/customizer/builder':            path.resolve( __dirname, 'src/backend/customizer/js/builder.js' ),
	'backend/customizer/builder-v1':         path.resolve( __dirname, 'src/backend/customizer/js/builder-v1.js' ),
	'backend/customizer/builder-v2':         path.resolve( __dirname, 'src/backend/customizer/js/builder-v2.js' ),

	// Backend — Admin
	'backend/admin/dashboard': path.resolve( __dirname, 'src/backend/admin/dashboard.js' ),
	'backend/admin/metabox':   path.resolve( __dirname, 'src/backend/admin/metabox.js' ),
	'backend/admin/editor':    path.resolve( __dirname, 'src/backend/admin/editor.js' ),
};

// ── Final config ─────────────────────────────────────────────────────────────
module.exports = {
	...defaultConfig,

	entry: entries,

	output: {
		...defaultConfig.output,
		path:     path.resolve( __dirname, 'build' ),
		filename: 'js/[name].js',
	},

	module: {
		...defaultConfig.module,
		rules: patchedRules,
	},

	plugins: [
		...filteredPlugins,

		new MiniCssExtractPlugin( { filename: 'css/[name].css' } ),

		new RtlCssPlugin(),

		new CopyPlugin( {
			patterns: [
				{ from: 'src/fonts',  to: 'fonts' },
				{ from: 'src/images', to: 'images' },
				{ from: 'src/vendor', to: 'vendor' },
			],
		} ),
	],
};
