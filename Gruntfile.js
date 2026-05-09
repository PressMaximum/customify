/**
 * Customify Gruntfile.
 *
 * Tooling split:
 *   - webpack assets and .pot generation are owned by npm scripts
 *     (`npm start`, `npm run build`, `npm run makepot`). Webpack's
 *     EmitMinifiedAssetsPlugin emits `.min.*` siblings only in production
 *     mode, so `npm start` produces readable bundles while `npm run build`
 *     produces both readable and minified.
 *   - Grunt owns version bumping, style.css header sync, file staging
 *     and the wp.org-safe zip.
 *
 * Primary entry points:
 *   grunt release [--ver=<x.y.z>|patch|minor|major]
 *     Optionally bump version → sync style.css header → run
 *     `npm run release:assets` (production build + .pot) → stage and zip.
 *
 *   grunt zipfile
 *     Stage and zip whatever build/ already contains (no rebuild).
 */

module.exports = function ( grunt ) {
	'use strict';

	const path     = require( 'path' );
	const execSync = require( 'child_process' ).execSync;
	const pkgInfo  = grunt.file.readJSON( 'package.json' );

	const SLUG       = 'customify';
	const STAGE_DIR  = 'release-staging';
	const STAGE_PATH = STAGE_DIR + '/' + SLUG + '/';

	// One canonical list of patterns the wp.org zip must NOT contain.
	const EXCLUDES = [
		// Source / dev tooling
		'!node_modules/**',
		'!src/**',
		'!tests/**',
		'!php-tests/**',
		'!bin/**',
		'!vendor/**',
		'!.git/**',
		'!.github/**',
		'!.gitlab/**',
		'!.idea/**',
		'!.vscode/**',
		'!.claude/**',
		'!' + STAGE_DIR + '/**',

		// Build / package configs
		'!Gruntfile.js',
		'!webpack.config.js',
		'!package.json',
		'!package-lock.json',
		'!yarn.lock',
		'!composer.json',
		'!composer.lock',
		'!phpcs.xml',
		'!phpcs.xml.dist',
		'!phpunit.xml',
		'!phpunit.xml.dist',
		'!codesniffer.ruleset.xml',

		// Hidden / repo metadata
		'!.gitignore',
		'!.gitattributes',
		'!.gitlab-ci.yml',
		'!.editorconfig',
		'!.eslintrc*',
		'!.prettierrc*',
		'!.babelrc*',
		'!.npmrc',
		'!.nvmrc',
		'!.distignore',

		// Miscellaneous
		'!*.sh',
		'!*.zip',
		'!*.log',
		'!**/.DS_Store',
		'!**/Thumbs.db',
		'!**/desktop.ini',

		// Source maps
		'!**/*.map',
		'!**/*.css.map',
		'!**/*.js.map',

		// Project-internal docs
		'!CLAUDE.md',
		'!README.md',

		// Legacy SCSS dir (if present)
		'!sass/**',
		'!assets/**/*.map',
	];

	grunt.initConfig( {
		pkg: pkgInfo,

		copy: {
			release: {
				options: { mode: true },
				src:     [ '**' ].concat( EXCLUDES ),
				dest:    STAGE_PATH,
			},
		},

		compress: {
			release: {
				options: {
					archive: SLUG + '-' + pkgInfo.version + '.zip',
					mode:    'zip',
					pretty:  true,
				},
				files: [
					{ expand: true, cwd: STAGE_DIR + '/', src: [ SLUG + '/**' ] },
				],
			},
		},

		clean: {
			stage: [ STAGE_DIR + '/' ],
			zip:   [ '*.zip' ],
		},

		bumpup: {
			options: { updateProps: { pkg: 'package.json' } },
			file:    'package.json',
		},

		replace: {
			theme_main: {
				src:        [ 'style.css' ],
				overwrite:  true,
				replacements: [ {
					from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
					to:   'Version: <%= pkg.version %>',
				} ],
			},
		},

		// Fallback .pot generator used when WP-CLI is not on PATH. Runs via
		// grunt-wp-i18n which only needs PHP (bundled MakePOT.php). The
		// preferred path is `wp i18n make-pot` via the npm `makepot` script.
		makepot: {
			target: {
				options: {
					domainPath:      'languages/',
					potFilename:     'customify.pot',
					potHeaders:      {
						poedit:                  true,
						'x-poedit-keywordslist': true,
					},
					type:            'wp-theme',
					updateTimestamp: true,
				},
			},
		},

		addtextdomain: {
			options: { textdomain: 'customify' },
			target:  {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!src/**',
						'!tests/**',
						'!php-tests/**',
						'!bin/**',
						'!vendor/**',
						'!build/**',
						'!' + STAGE_DIR + '/**',
					],
				},
			},
		},

		phpcs: {
			application: {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!src/**',
					'!tests/**',
					'!php-tests/**',
					'!build/**',
					'!' + STAGE_DIR + '/**',
				],
			},
			options: {
				bin:      'phpcs',
				standard: 'phpcs.xml',
			},
		},

		phpcbf: {
			options: { bin: 'phpcbf' },
			files:   {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!src/**',
					'!tests/**',
					'!php-tests/**',
					'!build/**',
					'!' + STAGE_DIR + '/**',
				],
			},
		},
	} );

	// ── NPM tasks ───────────────────────────────────────────────────────────
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' ); // still needed for the addtextdomain codemod
	grunt.loadNpmTasks( 'grunt-bumpup' );
	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-phpcbf' );

	grunt.registerTask( 'default', [ 'watch' ] );

	// ── Google Fonts list refresh ───────────────────────────────────────────
	grunt.registerTask( 'google-fonts', function () {
		const done    = this.async();
		const request = require( 'request' );
		const fs      = require( 'fs' );

		request(
			'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDN4eR6IPflX0QhU1UOOHjv71-2KY3BQwA',
			function ( error, response, body ) {
				if ( response && response.statusCode === 200 ) {
					const fonts = {};
					JSON.parse( body ).items.forEach( function ( font ) {
						fonts[ font.family ] = {
							family:   font.family,
							category: font.category,
							variants: font.variants,
							subsets:  font.subsets,
						};
					} );
					fs.writeFile( 'src/fonts/google-fonts.json', JSON.stringify( fonts, undefined, 4 ), function ( err ) {
						if ( ! err ) grunt.log.ok( 'Google Fonts updated.' );
						done( ! err );
					} );
					return;
				}
				done( false );
			}
		);
	} );

	// ── npm bridge ──────────────────────────────────────────────────────────
	function runShell( cmd ) {
		const done = this.async();
		grunt.log.writeln( '▶ ' + cmd );
		try {
			execSync( cmd, { stdio: 'inherit', cwd: path.resolve( __dirname ) } );
			done();
		} catch ( err ) {
			grunt.log.error( cmd + ' failed: ' + err.message );
			done( false );
		}
	}

	grunt.registerTask( 'release:assets', 'Run `npm run release:assets` (build + makepot).', function () {
		runShell.call( this, 'npm run release:assets' );
	} );

	// ── Zip pipeline ────────────────────────────────────────────────────────
	// Assumes build/ is fresh and languages/customify.pot is up to date.
	grunt.registerTask( 'zipfile', [
		'clean:zip',
		'clean:stage',
		'copy:release',
		'compress:release',
		'clean:stage',
	] );

	// ── Release ─────────────────────────────────────────────────────────────
	grunt.registerTask( 'release', 'Build, package and zip the theme for distribution.', function () {
		const ver = grunt.option( 'ver' );
		if ( ver ) {
			grunt.task.run( 'bumpup:' + ver );
		}
		grunt.task.run( 'replace:theme_main' );
		grunt.task.run( 'release:assets' );
		grunt.task.run( 'zipfile' );
	} );
};
