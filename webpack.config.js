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
const fs                   = require( 'fs' );
const webpack              = require( 'webpack' );
const rtlcss               = require( 'rtlcss' );
const Terser               = require( 'terser' );
const postcss              = require( 'postcss' );
const cssnano              = require( 'cssnano' );
const defaultConfig        = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CopyPlugin           = require( 'copy-webpack-plugin' );

/**
 * Git installs omit `build/` (created by dashboard-kit prepublish/build).
 * Alias to `src/` and stub `style.css` so webpack matches package exports rules.
 */
const dashboardKitRoot = ( () => {
	// A git worktree's own node_modules is gitignored (never copied on creation),
	// so a path hardcoded to __dirname won't exist there. Resolve wherever Node
	// can actually find the package: a worktree nested under the main checkout
	// walks up to the parent's node_modules. Fall back to the local path.
	try {
		return path.dirname(
			require.resolve( '@pressmaximum/dashboard-kit/package.json' )
		);
	} catch ( _err ) {
		return path.resolve(
			__dirname,
			'node_modules/@pressmaximum/dashboard-kit'
		);
	}
} )();
const dashboardKitBuildEntry   = path.join( dashboardKitRoot, 'build', 'index.mjs' );
const dashboardKitSrcDir     = path.join( dashboardKitRoot, 'src' );
const dashboardKitGitStyleNoopPath = path.resolve(
	__dirname,
	'src/backend/admin/dashboard-v2/dashboard-kit-git-style-noop.css'
);

const dashboardKitWebpackAliases =
	! fs.existsSync( dashboardKitBuildEntry )
		? {
			'@pressmaximum/dashboard-kit': path.join( dashboardKitSrcDir, 'index.mjs' ),
			'@pressmaximum/dashboard-kit/style.css': dashboardKitGitStyleNoopPath,
		}
		: {};
const dashboardKitFromGitRepo =
	Object.keys( dashboardKitWebpackAliases ).length > 0;

/** Babel skips `node_modules` by default — allow dashboard-kit JSX when cloning from Git. */
const babelExcludeLeavingDashboardKitGit = ( filepath ) =>
	/node_modules[/\\]/.test( filepath ) &&
	! /[/\\]@pressmaximum[/\\]dashboard-kit[/\\]/.test( filepath );

/** pnpm links `node_modules/@pressmaximum`; webpack resolves the real `.pnpm/` path — include must match. */
const dashboardKitSrcDirResolved = ( () => {
	try {
		return fs.existsSync( dashboardKitSrcDir )
			? fs.realpathSync( dashboardKitSrcDir )
			: dashboardKitSrcDir;
	} catch ( _err ) {
		return dashboardKitSrcDir;
	}
} )();

/** Overrides webpack default `.mjs` rule (resolve.byDependency.esm.fullySpecified: true). */
const dashboardKitMjsRelaxRule = dashboardKitFromGitRepo
		? [
			{
				test:    /\.mjs$/i,
				include: dashboardKitSrcDirResolved,
				resolve: {
					byDependency: {
						esm: {
							fullySpecified: false,
						},
					},
				},
			},
		]
		: [];

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

// ── Normalize emitted-asset line endings to LF ───────────────────────────────
// Some npm dependencies (e.g. `@dnd-kit/utilities` ships its ESM build with
// CRLF) leak their source line endings through webpack's concat into bundled
// chunks. Mixed CRLF/LF in `build/**` is a hard block for WP.org theme review
// (SVN can't resolve diffs across mixed-ending files). Rather than patching
// each upstream package, normalize every text asset webpack emits. Runs at
// the REPORT stage so it catches output from MiniCssExtractPlugin, RtlCss,
// and the `.min.*` siblings written by EmitMinifiedAssetsPlugin.
class NormalizeLineEndingsPlugin {
	apply( compiler ) {
		compiler.hooks.compilation.tap( 'NormalizeLineEndingsPlugin', ( compilation ) => {
			compilation.hooks.processAssets.tap(
				{
					name:  'NormalizeLineEndingsPlugin',
					stage: compilation.PROCESS_ASSETS_STAGE_REPORT,
				},
				( assets ) => {
					for ( const filename of Object.keys( assets ) ) {
						if ( ! /\.(js|css|map|json|html|svg|txt|md|asset\.php)$/.test( filename ) ) {
							continue;
						}
						const raw  = compilation.assets[ filename ].source();
						const text = typeof raw === 'string' ? raw : raw.toString();
						if ( ! text.includes( '\r' ) ) {
							continue;
						}
						const normalized = text.replace( /\r\n/g, '\n' ).replace( /\r/g, '\n' );
						compilation.updateAsset( filename, new webpack.sources.RawSource( normalized ) );
					}
				}
			);
		} );
	}
}

// ── Emit .min siblings alongside every JS/CSS output ─────────────────────────
// Runs in production only (`npm run build`). `npm start` (development /
// watch) skips minification so the watch loop stays fast and only emits
// readable bundles. PHP's Customify::get_asset_suffix() picks `.min` when
// WP_DEBUG is off and falls back to the unminified file otherwise — this
// keeps `npm start` usable without setting WP_DEBUG. Staged after OPTIMIZE
// so RTL output is minified too.
class EmitMinifiedAssetsPlugin {
	apply( compiler ) {
		compiler.hooks.compilation.tap( 'EmitMinifiedAssetsPlugin', ( compilation ) => {
			compilation.hooks.processAssets.tapPromise(
				{
					name:  'EmitMinifiedAssetsPlugin',
					stage: compilation.PROCESS_ASSETS_STAGE_OPTIMIZE_SIZE,
				},
				async () => {
					const filenames = Object.keys( compilation.assets );
					await Promise.all( filenames.map( async ( filename ) => {
						if ( /\.min\.(js|css)$/.test( filename ) ) return;
						// Skip static-copied assets (fonts/images/vendor) — those
						// often ship their own .min siblings via CopyPlugin.
						if ( /^(?:fonts|images|vendor)\//.test( filename ) ) return;
						const source = compilation.assets[ filename ].source().toString();
						try {
							if ( filename.endsWith( '.js' ) ) {
								const result = await Terser.minify( source );
								if ( result.code ) {
									const minName = filename.replace( /\.js$/, '.min.js' );
									compilation.emitAsset( minName, new webpack.sources.RawSource( result.code ) );
								}
							} else if ( filename.endsWith( '.css' ) ) {
								const result = await postcss( [ cssnano( { preset: 'default' } ) ] ).process( source, { from: undefined } );
								const minName = filename.replace( /\.css$/, '.min.css' );
								compilation.emitAsset( minName, new webpack.sources.RawSource( result.css ) );
							}
						} catch ( err ) {
							compilation.errors.push( new Error( `EmitMinifiedAssetsPlugin failed for ${ filename }: ${ err.message }` ) );
						}
					} ) );
				}
			);
		} );
	}
}

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

const isPostcssLoader = ( loader ) =>
	typeof loader === 'object' &&
	loader.loader &&
	/\/postcss-loader\//.test( loader.loader );

// sourceMap on each loader must mirror the top-level `devtool` so the
// chain reaches back to the original .scss files in dev and is fully
// disabled in production (no map generation cost).
const cssSourceMap = process.env.NODE_ENV !== 'production';

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
				return { ...loader, options: { ...loader.options, url: false, sourceMap: cssSourceMap } };
			}
			if ( isSassLoader( loader ) ) {
				return {
					...loader,
					options: {
						...loader.options,
						sourceMap: cssSourceMap,
						sassOptions: {
							...( loader.options?.sassOptions || {} ),
							silenceDeprecations: [ 'import' ],
							// Force expanded output so the non-min CSS stays
							// readable; the .min.css sibling is minified via
							// EmitMinifiedAssetsPlugin below.
							outputStyle: 'expanded',
						},
					},
				};
			}
			if ( isPostcssLoader( loader ) ) {
				return { ...loader, options: { ...loader.options, sourceMap: cssSourceMap } };
			}
			return loader;
		} ),
	};
} );

/** Let babel / source-map-loader process `@pressmaximum/dashboard-kit` when it has no compiled `build/`. */
const patchWpNodeModulesExcludeForDashboardKitGit = ( rule ) => {
	if ( ! dashboardKitFromGitRepo ) {
		return rule;
	}

	let excludesNodeModulesBare = false;
	if ( rule.exclude instanceof RegExp && rule.exclude.source === 'node_modules' ) {
		excludesNodeModulesBare = true;
	} else if (
		Array.isArray( rule.exclude ) &&
		rule.exclude.length === 1 &&
		rule.exclude[ 0 ] instanceof RegExp &&
		rule.exclude[ 0 ].source === 'node_modules'
	) {
		excludesNodeModulesBare = true;
	}

	if ( ! excludesNodeModulesBare ) {
		return rule;
	}

	const babelLoaderConfigured =
		Array.isArray( rule.use ) &&
		rule.use.some( ( u ) => u?.loader?.includes?.( 'babel-loader' ) );
	const sourceMapLoaderConfigured =
		typeof rule.use === 'string'
			? rule.use.includes( 'source-map-loader' )
			: Array.isArray( rule.use )
				? rule.use.some(
					( u ) =>
						( typeof u === 'string'
							? u.includes( 'source-map-loader' )
							: u?.loader?.includes?.( 'source-map-loader' ) )
				  )
				: false;

	if ( ! babelLoaderConfigured && ! sourceMapLoaderConfigured ) {
		return rule;
	}

	return {
		...rule,
		exclude: babelExcludeLeavingDashboardKitGit,
	};
};

const patchedRulesIncludingDashboardKit = patchedRules.map(
	patchWpNodeModulesExcludeForDashboardKitGit
);

// ── Entry points ─────────────────────────────────────────────────────────────
const entries = {
	// Frontend
	'frontend/theme':          path.resolve( __dirname, 'src/frontend/index.js' ),
	'frontend/woocommerce':    path.resolve( __dirname, 'src/frontend/woocommerce.js' ),

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
	'backend/admin/dashboard':    path.resolve( __dirname, 'src/backend/admin/dashboard.js' ),
	'backend/admin/dashboard-v2': path.resolve( __dirname, 'src/backend/admin/dashboard-v2/index.js' ),
	'backend/admin/metabox':      path.resolve( __dirname, 'src/backend/admin/metabox.js' ),
	'backend/admin/editor':       path.resolve( __dirname, 'src/backend/admin/editor.js' ),
};

// ── Final config ─────────────────────────────────────────────────────────────
// Source maps are emitted only in development mode (`npm run start`) so they
// never ship to production via `npm run build`. wp-scripts sets NODE_ENV to
// 'production' for build and 'development' for start.
const isDevelopment = process.env.NODE_ENV !== 'production';

module.exports = {
	...defaultConfig,

	entry: entries,

	resolve: {
		...defaultConfig.resolve,
		// webpack defaultRules enforce fullySpecified on .mjs (see webpack/lib/config/defaults.js).
		fullySpecified: false,
		byDependency: {
			...( defaultConfig.resolve?.byDependency || {} ),
			esm: {
				...( defaultConfig.resolve?.byDependency?.esm || {} ),
				fullySpecified: false,
			},
		},
		alias: {
			...( typeof defaultConfig.resolve?.alias === 'object' &&
			! Array.isArray( defaultConfig.resolve.alias )
				? defaultConfig.resolve.alias
				: {} ),
			...dashboardKitWebpackAliases,
		},
	},

	devtool: isDevelopment ? 'source-map' : false,

	output: {
		...defaultConfig.output,
		path:     path.resolve( __dirname, 'build' ),
		filename: 'js/[name].js',
	},

	module: {
		...defaultConfig.module,
		rules: [ ...dashboardKitMjsRelaxRule, ...patchedRulesIncludingDashboardKit ],
	},

	// Always emit unminified outputs. EmitMinifiedAssetsPlugin below produces
	// the `.min.js` / `.min.css` siblings. Runtime picks one based on WP_DEBUG.
	optimization: {
		...defaultConfig.optimization,
		minimize: false,
	},

	plugins: [
		...( dashboardKitFromGitRepo
			? [
				new webpack.NormalModuleReplacementPlugin(
					/^@pressmaximum\/dashboard-kit\/style\.css$/,
					dashboardKitGitStyleNoopPath
				),
			]
			: [] ),

		...filteredPlugins,

		new MiniCssExtractPlugin( { filename: 'css/[name].css' } ),

		new RtlCssPlugin(),

		// Only minify in production — `npm start` produces readable bundles only.
		...( isDevelopment ? [] : [ new EmitMinifiedAssetsPlugin() ] ),

		// Runs after every other asset producer (REPORT stage) so it
		// catches CRLF that crept in via bundled deps + RTL + .min siblings.
		new NormalizeLineEndingsPlugin(),

		new CopyPlugin( {
			patterns: [
				{ from: 'src/fonts',  to: 'fonts' },
				{ from: 'src/images', to: 'images' },
				{ from: 'src/vendor', to: 'vendor' },
			],
		} ),
	],
};
