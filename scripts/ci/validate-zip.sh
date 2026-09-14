#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$root_dir"

shopt -s nullglob
archives=(customify-*.zip)
if [[ ${#archives[@]} -ne 1 ]]; then
	echo "Expected exactly one customify-*.zip artifact; found ${#archives[@]}." >&2
	exit 1
fi

archive="${archives[0]}"
zip_bytes="$(wc -c < "$archive" | tr -d '[:space:]')"
if (( zip_bytes > 26214400 )); then
	echo "Theme ZIP is unexpectedly large: $zip_bytes bytes (limit: 25 MiB)." >&2
	exit 1
fi

unzip -tqq "$archive"
manifest="$(mktemp "${TMPDIR:-/tmp}/customify-zip.XXXXXX")"
trap 'rm -f "$manifest"' EXIT
unzip -Z1 "$archive" > "$manifest"

required=(
	customify/style.css
	customify/functions.php
	customify/vendor/autoload.php
	customify/vendor/pressmaximum/dashboard-kit/includes/Boot.php
	customify/build/css/frontend/style-theme.min.css
	customify/build/js/frontend/theme.min.js
)
for path in "${required[@]}"; do
	if ! grep -qx "$path" "$manifest"; then
		echo "Theme ZIP is missing required runtime file: $path" >&2
		exit 1
	fi
done

if grep -Eq '^customify/(\.ci-runtime|\.git|\.github|docs|node_modules|scripts|src|tests)(/|$)' "$manifest"; then
	echo "Theme ZIP contains a development-only path." >&2
	exit 1
fi

for path in customify/.wp-env.json customify/AGENTS.md customify/CLAUDE.md customify/Gruntfile.js customify/package.json customify/package-lock.json customify/composer.json customify/composer.lock; do
	if grep -qx "$path" "$manifest"; then
		echo "Theme ZIP contains development-only file: $path" >&2
		exit 1
	fi
done

package_version="$(node -p "require('./package.json').version")"
theme_version="$(unzip -p "$archive" customify/style.css | sed -nE 's/^[[:space:]]*Version:[[:space:]]+([^[:space:]]+).*/\1/p' | head -1)"
if [[ -z "$theme_version" || "$theme_version" != "$package_version" ]]; then
	echo "ZIP version mismatch: package.json=$package_version, style.css=$theme_version" >&2
	exit 1
fi

echo "Validated $archive ($zip_bytes bytes, version $theme_version)."
