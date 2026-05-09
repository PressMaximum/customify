#!/usr/bin/env node
/**
 * Generate languages/customify.pot.
 *
 * Tries WP-CLI first (`wp i18n make-pot`) — the canonical, modern tool —
 * and falls back to `grunt makepot` (grunt-wp-i18n, PHP-only, no WP-CLI
 * dependency) when WP-CLI isn't on PATH or fails for any reason.
 */

'use strict';

const { spawnSync } = require( 'child_process' );

const WP_CLI_ARGS = [
	'i18n',
	'make-pot',
	'.',
	'languages/customify.pot',
	'--domain=customify',
	'--slug=customify',
	'--exclude=node_modules,vendor,build,release-staging,src/fonts,src/images,src/vendor,tests,php-tests,bin',
];

function tryWpCli() {
	const probe = spawnSync( 'wp', [ '--version' ], { stdio: 'ignore' } );
	if ( probe.error || probe.status !== 0 ) return false;

	console.log( '▶ wp ' + WP_CLI_ARGS.join( ' ' ) );
	const run = spawnSync( 'wp', WP_CLI_ARGS, { stdio: 'inherit' } );
	if ( run.status !== 0 ) {
		console.error( 'WP-CLI make-pot exited with code ' + run.status );
		return false;
	}
	return true;
}

function fallbackToGrunt() {
	console.log( '▶ npx grunt makepot   (WP-CLI unavailable — falling back to grunt-wp-i18n)' );
	const run = spawnSync( 'npx', [ 'grunt', 'makepot' ], { stdio: 'inherit', shell: process.platform === 'win32' } );
	if ( run.status !== 0 ) {
		console.error( 'grunt makepot failed with code ' + run.status );
		process.exit( run.status || 1 );
	}
}

if ( ! tryWpCli() ) {
	fallbackToGrunt();
}
