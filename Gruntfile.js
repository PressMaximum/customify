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
 * Primary entry points (one-shot, end-to-end):
 *   grunt release [--ver=<x.y.z>|patch|minor|major] [--no-publish]
 *     Pre-flight (clean tree + gh auth) → bump version IF --ver passed
 *     (otherwise use whatever is in package.json) → sync style.css header
 *     → `composer install --no-dev --optimize-autoloader` →
 *     `npm run release:assets` (production build emits both unminified
 *     and .min.* siblings, plus .pot) → stage + zip → commit + tag + push
 *     + `gh release create --latest`. `--no-publish` stops after zipping.
 *
 *   grunt beta-release [--ver=<x.y.z-beta.N>] [--no-publish]
 *     Auto-bumping pre-release variant. Without --ver, auto-derives the
 *     next beta tag: bumps `-beta.N` if already on a beta, otherwise
 *     patch-bumps the current stable and appends `-beta.1`. Publishes
 *     with `--prerelease` so GitHub does NOT mark it as latest.
 *
 *   grunt build-zip [--ver=<x.y.z>|patch|minor|major]
 *     Same build pipeline as `release` but stops after producing the zip:
 *     no preflight, no commit/tag/push, no GitHub Release. Use for local
 *     QA, customer hand-off, or staging upload.
 *
 *   grunt zipfile
 *     Stage and zip whatever build/ + vendor/ already contain (no rebuild,
 *     no composer install). The `stage:prepare` substep reads
 *     vendor/composer/installed.json and strips each package down to its
 *     autoload paths + LICENSE — no per-package hand-maintenance needed.
 *
 *   grunt vendor:audit
 *     Print every installed Composer package with the runtime sub-paths
 *     the release pipeline will keep. Use before adding a new dep to
 *     verify the package declares everything it needs in `autoload`.
 */

module.exports = function ( grunt ) {
	'use strict';

	const path     = require( 'path' );
	const execSync = require( 'child_process' ).execSync;
	const pkgInfo  = grunt.file.readJSON( 'package.json' );

	const SLUG       = 'customify';
	const STAGE_DIR  = 'release-staging';
	const STAGE_PATH = STAGE_DIR + '/' + SLUG + '/';

	// Read the canonical theme version from style.css. We treat style.css
	// as the single source of truth (WP loads the version from there) and
	// no longer write to package.json during release — `package.json` is
	// left untouched so it doesn't keep landing in release commits.
	function readThemeVersion() {
		const css = grunt.file.read( 'style.css' );
		const m   = css.match( /^\s*Version:\s*(\S+)/m );
		return m ? m[ 1 ] : null;
	}

	// One canonical list of patterns the wp.org zip must NOT contain.
	// NOTE: `vendor/` is intentionally KEPT — it is loaded at runtime via
	// `vendor/autoload.php` (see functions.php) to expose
	// PressMaximum\DashboardKit\* classes consumed by inc/admin/dashboard-v2*.
	// Before running `grunt release`, vendor/ must be re-installed with
	// `composer install --no-dev --optimize-autoloader` — handled by the
	// `composer:install:prod` task wired into the `release` pipeline below.
	const EXCLUDES = [
		// Source / dev tooling
		'!node_modules/**',
		'!src/**',
		'!tests/**',
		'!php-tests/**',
		'!bin/**',
		'!docs/**',
		'!.git/**',
		'!.github/**',
		'!.gitlab/**',
		'!.idea/**',
		'!.vscode/**',
		'!.claude/**',
		'!' + STAGE_DIR + '/**',

		// vendor/ runtime sub-tree — drop dev junk from each package
		'!vendor/**/.git/**',
		'!vendor/**/.github/**',
		'!vendor/**/.gitlab/**',
		'!vendor/**/tests/**',
		'!vendor/**/test/**',
		'!vendor/**/docs/**',
		'!vendor/**/doc/**',
		'!vendor/**/examples/**',
		'!vendor/**/example/**',
		'!vendor/**/*.md',
		'!vendor/**/*.dist',
		'!vendor/**/phpunit.xml*',
		'!vendor/**/phpcs.xml*',
		'!vendor/**/.editorconfig',
		'!vendor/**/.gitignore',
		'!vendor/**/.gitattributes',
		'!vendor/**/composer.json',
		'!vendor/**/composer.lock',
		'!vendor/**/package.json',
		'!vendor/**/package-lock.json',
		'!vendor/**/webpack.config.js',
		'!vendor/**/vitest.config.js',
		'!vendor/**/.eslintrc*',
		'!vendor/**/.prettierrc*',
		'!vendor/**/.babelrc*',

		// Build / package configs
		'!Gruntfile.js',
		'!webpack.config.js',
		'!package.json',
		'!package-lock.json',
		'!yarn.lock',
		'!pnpm-lock.yaml',
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
		'!.travis.yml',
		'!.editorconfig',
		'!.eslintrc*',
		'!.prettierrc*',
		'!.babelrc*',
		'!.npmrc',
		'!.nvmrc',
		'!.distignore',
		'!.jscsrc',
		'!.jshintignore',
		'!**/.DS_Store',

		// Miscellaneous
		'!*.sh',
		'!*.zip',
		'!*.log',

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
				// Per-package vendor strip patterns are appended at task-run
				// time by `stage:prepare` (reads vendor/composer/installed.json
				// to derive the runtime "keep" set for each installed package
				// + LICENSE files). Keep THIS list package-agnostic.
				src:  [ '**' ].concat( EXCLUDES ),
				dest: STAGE_PATH,
			},
		},

		compress: {
			release: {
				options: {
					// `<%= pkg.version %>` resolves at task-run time after any
					// `bumpup` step, so beta-release / release in the same
					// grunt invocation produce a zip named for the new version.
					archive: SLUG + '-<%= pkg.version %>.zip',
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

		// `.pot` generator. Runs via grunt-wp-i18n which only needs PHP
		// (bundled MakePOT.php). Invoked by `npm run makepot` (see
		// package.json) — no WP-CLI dependency, runs consistently across
		// every dev environment + CI.
		//
		// `exclude` compiles each entry as `new RegExp(entry)` and tests
		// against the file path. Add new exclusions here only — there is no
		// secondary path to keep in lockstep.
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
					exclude:         [
						// Build artifacts + vendor
						'node_modules/.*', 'vendor/.*', 'build/.*', 'release-staging/.*',
						// Non-translatable source assets
						'src/.*',
						// Test folders — every common naming convention
						'tests/.*', 'php-tests/.*', 'test/.*', '__tests__/.*',
						'spec/.*', '__mocks__/.*',
						// Tooling / docs
						'bin/.*', 'docs/.*',
						// Any hidden folder (name starts with `.`), at any depth.
						// The double-escaped dot is required because each entry is
						// compiled with `new RegExp()`. Catches .git, .github,
						// .claude, .vscode, .idea, and any future hidden folders
						// without manual updates.
						'\\..+/.*', '.*/\\..+/.*',
					],
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

	// ── set-version ─────────────────────────────────────────────────────────
	// In-memory version setter. Replaces `bumpup:<ver>` for the release
	// pipeline: writes the target version to grunt's `pkg.version` config
	// so downstream tasks (`replace:theme_main` for style.css, the
	// `compress:release` archive filename) pick it up — WITHOUT touching
	// package.json on disk. Keeps package.json out of every release commit.
	grunt.registerTask( 'set-version', 'Set pkg.version in memory (does not modify package.json).', function ( ver ) {
		if ( ! ver ) {
			grunt.fail.fatal( 'set-version requires a version arg, e.g. set-version:0.4.16-beta.1' );
		}
		grunt.config( 'pkg.version', ver );
		grunt.log.writeln( '  pkg.version (in-memory) = ' + ver + '  (package.json untouched)' );
	} );

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

	// Sync shell helpers used by tasks that need to chain multiple commands
	// inside a single task body (e.g. release:publish). Unlike runShell,
	// these do not call this.async() — execSync already blocks, so the
	// async wrapper is unnecessary when we're not isolating one command
	// per task.
	function shellSync( cmd ) {
		grunt.log.writeln( '▶ ' + cmd );
		execSync( cmd, { stdio: 'inherit', cwd: path.resolve( __dirname ) } );
	}

	function shellOK( cmd ) {
		try {
			execSync( cmd, { stdio: 'pipe', cwd: path.resolve( __dirname ) } );
			return true;
		} catch ( e ) {
			return false;
		}
	}

	grunt.registerTask( 'release:assets', 'Run `npm run release:assets` (build + makepot).', function () {
		runShell.call( this, 'npm run release:assets' );
	} );

	// Re-install Composer deps as production (no dev packages, optimized
	// autoloader). The resulting vendor/ tree is what ships in the zip.
	grunt.registerTask( 'composer:install:prod', 'Composer install (no-dev, optimized autoloader).', function () {
		runShell.call( this, 'composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist' );
	} );

	// ── Release publish helpers ─────────────────────────────────────────────
	// Pre-flight aborts the pipeline BEFORE any file mutation if the
	// environment can't complete a publish: dirty git tree, missing gh
	// CLI, or gh not authenticated. Cheap to run, saves an aborted
	// release halfway through.
	grunt.registerTask( 'release:preflight', 'Validate git + gh environment before mutating anything.', function () {
		if ( ! shellOK( 'git rev-parse --is-inside-work-tree' ) ) {
			grunt.fail.fatal( 'Not inside a git repository.' );
		}
		if ( ! shellOK( 'git diff-index --quiet HEAD --' ) ) {
			grunt.fail.fatal( 'Working tree has uncommitted changes. Commit or stash them, then re-run.' );
		}
		if ( ! shellOK( 'command -v gh' ) ) {
			grunt.fail.fatal( 'GitHub CLI `gh` not found in PATH. Install with `brew install gh`, then `gh auth login`.' );
		}
		if ( ! shellOK( 'gh auth status' ) ) {
			grunt.fail.fatal( '`gh` is installed but not authenticated. Run `gh auth login`.' );
		}
		grunt.log.ok( 'Pre-flight passed (clean tree, gh authenticated).' );
	} );

	// Commit the version bump + rebuilt assets, push, tag, and create the
	// GitHub Release with the zip attached. Auto-detects pre-release from
	// the SemVer suffix (`-beta.N`, `-rc.N`, etc.) and flags the release
	// accordingly. Pass `--no-publish` to grunt to skip this step and
	// keep everything local (useful for QA on the zip before shipping).
	grunt.registerTask( 'release:publish', 'Commit, tag, push, and create the GitHub Release with the zip attached.', function () {
		const v   = grunt.config.get( 'pkg.version' );
		const zip = SLUG + '-' + v + '.zip';
		const tag = 'v' + v;
		const isPrerelease = /-/.test( v );

		if ( grunt.option( 'no-publish' ) ) {
			grunt.log.subhead( '⚠ Skipping publish (--no-publish set).' );
			grunt.log.writeln( '  Manual publish later:' );
			grunt.log.writeln( '    git add style.css languages/customify.pot composer.lock' );
			grunt.log.writeln( '    git commit -m "Release version ' + v + '"' );
			grunt.log.writeln( '    git tag -a ' + tag + ' -m "Release ' + tag + '" && git push --follow-tags' );
			grunt.log.writeln( '    gh release create ' + tag + ' ' + zip + ' --title "' + tag + '" --generate-notes ' + ( isPrerelease ? '--prerelease' : '--latest' ) );
			return;
		}

		// Stage the canonical release-touched paths.
		//  - `build/` is .gitignored (commit c1a725ac).
		//  - `package.json` is intentionally NOT modified during release
		//    (set-version is in-memory only), so it never lands in this
		//    commit; manage the npm version separately if desired.
		shellSync( 'git add style.css languages/customify.pot composer.lock' );

		// Commit only if there are actually staged changes. Rare edge
		// case: re-running release with the same version + no asset diff.
		if ( shellOK( 'git diff --cached --quiet' ) ) {
			grunt.log.writeln( '  (no staged changes — skipping commit)' );
		} else {
			shellSync( 'git commit -m "Release version ' + v + '"' );
		}

		// Annotated tag (-a -m): required for `git push --follow-tags` to
		// actually push the tag, plus carries author/date/message for the
		// GitHub Release page.
		shellSync( 'git tag -a ' + tag + ' -m "Release ' + tag + '"' );
		shellSync( 'git push --follow-tags' );

		const ghFlags = isPrerelease ? '--prerelease' : '--latest';
		shellSync( 'gh release create ' + tag + ' ' + zip + ' --title "' + tag + '" --generate-notes ' + ghFlags );

		grunt.log.subhead( '✅ Release ' + tag + ' published.' );
	} );

	// ── Vendor strip helpers ────────────────────────────────────────────────
	// Composer publishes the canonical list of installed packages along
	// with each one's autoload declaration to
	// `vendor/composer/installed.json`. That declaration tells us exactly
	// which sub-paths inside the package contain runtime code; everything
	// else (JS source, tests, docs, build configs) is dev junk we can
	// safely strip from the wp.org-safe zip.

	/** Read packages from installed.json (Composer 1 and 2 layouts). */
	function readInstalledPackages() {
		const file = path.join( __dirname, 'vendor/composer/installed.json' );
		if ( ! grunt.file.exists( file ) ) {
			return null;
		}
		const data = grunt.file.readJSON( file );
		return Array.isArray( data ) ? data : ( data.packages || [] );
	}

	/** Flatten a package's autoload section into [{path, kind}] entries. */
	function autoloadPathsFor( pkg ) {
		const a   = pkg.autoload || {};
		const out = [];
		[ 'psr-4', 'psr-0' ].forEach( function ( k ) {
			if ( ! a[ k ] ) { return; }
			Object.keys( a[ k ] ).forEach( function ( ns ) {
				const v = a[ k ][ ns ];
				( Array.isArray( v ) ? v : [ v ] ).forEach( function ( p ) {
					out.push( { path: p, kind: k } );
				} );
			} );
		} );
		( a.classmap || [] ).forEach( function ( p ) { out.push( { path: p, kind: 'classmap' } ); } );
		( a.files    || [] ).forEach( function ( p ) { out.push( { path: p, kind: 'files'    } ); } );
		return out;
	}

	/**
	 * Build glob patterns that strip every vendor package down to its
	 * runtime autoload paths + LICENSE file. Returns [] when installed.json
	 * is missing (treat as "ship vendor/ as-is"), preserving the legacy
	 * `grunt zipfile` behaviour for environments without composer install.
	 */
	function buildVendorPatterns() {
		const packages = readInstalledPackages();
		if ( ! packages ) {
			grunt.log.warn( 'vendor/composer/installed.json missing — skipping per-package strip. Run `composer install` first for a leaner zip.' );
			return [];
		}
		const patterns = [];
		packages.forEach( function ( pkg ) {
			const base     = 'vendor/' + pkg.name;
			const autoload = autoloadPathsFor( pkg );
			if ( autoload.length === 0 ) {
				grunt.log.writeln( '  • ' + pkg.name + ': no autoload declared — keeping full package' );
				return;
			}
			// Blanket exclude the package first; positive patterns below
			// re-include the narrow runtime subset.
			patterns.push( '!' + base + '/**' );
			autoload.forEach( function ( item ) {
				const cleaned = item.path.replace( /^\/+|\/+$/g, '' );
				const prefix  = cleaned ? base + '/' + cleaned : base;
				if ( item.kind === 'files' ) {
					// autoload.files entries are always file paths.
					patterns.push( prefix );
				} else {
					// psr-4 / psr-0 / classmap entries can be file or dir;
					// emit both forms so glob matches whichever applies.
					patterns.push( prefix );
					patterns.push( prefix + '/**' );
				}
			} );
			// Always preserve LICENSE for legal compliance (GPL/MIT/...
			// redistribution). Cover common spelling + extension variants.
			patterns.push( base + '/{LICENSE,LICENCE,License,Licence,license,licence}{,.md,.txt,.rst}' );
		} );
		return patterns;
	}

	// Inject the dynamic vendor patterns into copy.release.src right
	// before staging. Glob processing is left-to-right, so these patterns
	// — appended LAST — re-include runtime code that earlier blanket
	// EXCLUDES patterns (`!vendor/<star><star>/<star>.md` etc) may have stripped.
	grunt.registerTask( 'stage:prepare', 'Inject vendor-aware copy patterns from installed.json.', function () {
		const dynamic = buildVendorPatterns();
		if ( dynamic.length === 0 ) {
			return;
		}
		const current = grunt.config.get( 'copy.release.src' );
		grunt.config.set( 'copy.release.src', current.concat( dynamic ) );
		grunt.log.ok( 'Vendor patterns applied: ' + dynamic.length + ' (per-package strip + LICENSE keep).' );
	} );

	/**
	 * Standalone audit task — print a per-package report of what the
	 * release pipeline will keep from vendor/. Use this before adding a
	 * new Composer dep to validate the autoload declaration covers
	 * everything the runtime needs.
	 */
	grunt.registerTask( 'vendor:audit', 'Show vendor packages and their runtime autoload paths.', function () {
		const packages = readInstalledPackages();
		if ( ! packages ) {
			grunt.log.warn( 'vendor/composer/installed.json missing. Run `composer install` first.' );
			return;
		}
		if ( packages.length === 0 ) {
			grunt.log.writeln( 'No vendor packages installed.' );
			return;
		}
		grunt.log.subhead( 'Vendor autoload audit (' + packages.length + ' package' + ( packages.length > 1 ? 's' : '' ) + ')' );
		packages.forEach( function ( pkg ) {
			const paths = autoloadPathsFor( pkg );
			grunt.log.writeln( '' );
			grunt.log.writeln( '  ' + pkg.name + '  [' + ( pkg.type || 'library' ) + ']' );
			if ( paths.length === 0 ) {
				grunt.log.writeln( '    (no autoload — whole package kept in zip)' );
				return;
			}
			paths.forEach( function ( p ) {
				grunt.log.writeln( '    keep: ' + ( p.path || '<package root>' ) + '  (' + p.kind + ')' );
			} );
			grunt.log.writeln( '    keep: LICENSE*  (legal compliance)' );
		} );
	} );

	// ── Zip pipeline ────────────────────────────────────────────────────────
	// Assumes build/ is fresh and languages/customify.pot is up to date.
	grunt.registerTask( 'zipfile', [
		'clean:zip',
		'clean:stage',
		'stage:prepare',
		'copy:release',
		'compress:release',
		'clean:stage',
	] );

	// ── Release ─────────────────────────────────────────────────────────────
	// One-shot end-to-end pipeline. Steps:
	//   1. Pre-flight (clean tree + gh auth)
	//   2. Bump version ONLY if --ver=<x.y.z> is passed. Otherwise the
	//      release uses whatever version is currently in package.json
	//      (i.e. the dev bumped it manually before running).
	//   3. Sync style.css header to package.json version
	//   4. composer install --no-dev --optimize-autoloader  → vendor/ for ship
	//   5. npm run release:assets  → production webpack (emits BOTH unminified
	//      and .min.* via EmitMinifiedAssetsPlugin) + makepot
	//   6. Stage (auto vendor strip from installed.json) + zip
	//   7. Commit + tag + push + `gh release create --latest` (or skip with
	//      --no-publish for local QA).
	grunt.registerTask( 'release', 'Build, package, zip, and publish the theme to GitHub Releases.', function () {
		grunt.task.run( 'release:preflight' );

		const ver = grunt.option( 'ver' );
		if ( ver ) {
			grunt.task.run( 'set-version:' + ver );
		}
		grunt.task.run( 'replace:theme_main' );
		grunt.task.run( 'composer:install:prod' );
		grunt.task.run( 'release:assets' );
		grunt.task.run( 'zipfile' );
		grunt.task.run( 'release:publish' );
	} );

	// ── Build zip (no git, no publish) ─────────────────────────────────────
	// Same build pipeline as `release` but stops after producing the zip.
	// No preflight (no clean-tree / gh-auth check), no commit, no tag, no
	// push, no `gh release create`. Use this to produce a shippable zip
	// locally — e.g. for manual QA, customer hand-off, or staging upload —
	// without mutating git state.
	//
	// Steps:
	//   1. Bump version ONLY if --ver=<x.y.z> is passed (same semantics as
	//      `release`); otherwise use whatever is in package.json.
	//   2. Sync style.css header to package.json version.
	//   3. composer install --no-dev --optimize-autoloader  → vendor/ for ship.
	//   4. npm run release:assets  → production webpack + makepot.
	//   5. Stage (auto vendor strip from installed.json) + zip.
	//
	// Output: customify-<version>.zip in the repo root.
	grunt.registerTask( 'build-zip', 'Build assets and produce the shippable zip without touching git.', function () {
		const ver = grunt.option( 'ver' );
		if ( ver ) {
			grunt.task.run( 'set-version:' + ver );
		}
		grunt.task.run( 'replace:theme_main' );
		grunt.task.run( 'composer:install:prod' );
		grunt.task.run( 'release:assets' );
		grunt.task.run( 'zipfile' );
	} );

	// ── Beta release ────────────────────────────────────────────────────────
	// Same pipeline as `release` but lands a pre-release (`X.Y.Z-beta.N`)
	// version intended for GitHub Releases marked `--prerelease`.
	//
	// Version resolution:
	//   --ver=<x.y.z-beta.N>    Use this exact version.
	//   (no flag) + current pkg is `X.Y.Z-beta.N`  → bump beta counter to N+1.
	//   (no flag) + current pkg is stable `X.Y.Z`  → patch-bump and start at
	//                                                  `X.Y.(Z+1)-beta.1`.
	//
	// SemVer ordering: `0.4.14-beta.3 < 0.4.14`, so when the beta cycle
	// stabilizes you run `grunt release --ver=0.4.14` to drop the suffix.
	//
	// Produces: customify-<X.Y.Z-beta.N>.zip in the repo root. Publish via:
	//   gh release create v<X.Y.Z-beta.N> customify-<X.Y.Z-beta.N>.zip --prerelease
	grunt.registerTask( 'beta-release', 'Build, package, zip, and publish a pre-release beta to GitHub Releases.', function () {
		grunt.task.run( 'release:preflight' );

		const explicit = grunt.option( 'ver' );
		let target;

		if ( explicit ) {
			target = explicit;
		} else {
			// Derive from style.css (canonical theme version) instead of
			// package.json — release no longer writes to package.json, so
			// pkgInfo.version is stale across runs and would produce
			// duplicate beta tags. style.css gets updated each release.
			const current = readThemeVersion();
			if ( ! current ) {
				grunt.fail.fatal( 'Cannot read Version: header from style.css. Pass --ver=X.Y.Z-beta.N explicitly.' );
			}
			const betaMatch = current.match( /^(\d+\.\d+\.\d+)-beta\.(\d+)$/ );

			if ( betaMatch ) {
				target = betaMatch[ 1 ] + '-beta.' + ( parseInt( betaMatch[ 2 ], 10 ) + 1 );
			} else {
				const stable = current.match( /^(\d+)\.(\d+)\.(\d+)$/ );
				if ( ! stable ) {
					grunt.fail.fatal(
						'Cannot derive a beta version from style.css Version "' + current +
						'". Pass --ver=X.Y.Z-beta.N explicitly.'
					);
				}
				target = stable[ 1 ] + '.' + stable[ 2 ] + '.' + ( parseInt( stable[ 3 ], 10 ) + 1 ) + '-beta.1';
			}
		}

		grunt.log.subhead( '▶ beta-release → version ' + target );
		grunt.task.run( 'set-version:' + target );
		grunt.task.run( 'replace:theme_main' );
		grunt.task.run( 'composer:install:prod' );
		grunt.task.run( 'release:assets' );
		grunt.task.run( 'zipfile' );
		grunt.task.run( 'release:publish' );
	} );
};
