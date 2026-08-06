#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
runtime_dir="$root_dir/.ci-runtime"
wp_env=(npx --yes @wordpress/env@11.11.0 --config="$root_dir/.wp-env.json")

if [[ -e "$runtime_dir" ]]; then
	echo "CI runtime directory already exists: $runtime_dir" >&2
	exit 1
fi

shopt -s nullglob
archives=("$root_dir"/customify-*.zip)
if [[ ${#archives[@]} -ne 1 || ! -f "${archives[0]}" ]]; then
	echo "Expected exactly one customify-*.zip artifact." >&2
	exit 1
fi

mkdir -p "$runtime_dir"
unzip -q "${archives[0]}" -d "$runtime_dir"
test -f "$runtime_dir/customify/functions.php"
test -f "$runtime_dir/customify/vendor/autoload.php"

echo "Starting WordPress latest on PHP 8.3..."
"${wp_env[@]}" start
"${wp_env[@]}" run cli wp theme activate customify
# The PHP snippet must not be expanded by Bash.
# shellcheck disable=SC2016
"${wp_env[@]}" run cli wp eval '
	WP_CLI::log( "WordPress runtime: " . get_bloginfo( "version" ) );
	if ( PHP_MAJOR_VERSION !== 8 || PHP_MINOR_VERSION !== 3 ) {
		WP_CLI::error( "Expected PHP 8.3, got " . PHP_VERSION );
	}
	$theme = wp_get_theme();
	if ( "customify" !== $theme->get_stylesheet() || "customify" !== $theme->get_template() ) {
		WP_CLI::error( "Customify production theme is not active." );
	}
	WP_CLI::success( "PHP runtime: " . PHP_VERSION );
'
curl --fail --silent --show-error --output /dev/null http://localhost:8900/
echo "Customify production ZIP activated and rendered successfully."
