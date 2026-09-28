#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 2 ]]; then
	echo "Usage: $0 <base-sha> <head-sha>" >&2
	exit 2
fi

base_sha="$1"
head_sha="$2"
js_files=()
style_files=()

# These legacy files predate the current WordPress lint rules and still carry
# large, unrelated baselines. Linting an entire file merely because a PR
# touches a few lines turns the incremental gate into a full-file cleanup.
# Keep the exceptions explicit and remove entries as each file is modularized
# or its baseline is paid down.
legacy_js_baseline=(
	"src/backend/customizer/js/control.js"
)
legacy_style_baseline=(
	"src/backend/customizer/scss/_control.scss"
	"src/backend/customizer/scss/customizer.scss"
	"src/frontend/scss/base/_base.scss"
	"src/frontend/scss/base/_skins.scss"
	"src/frontend/scss/footer/_footer-common.scss"
)

is_legacy_baseline_file() {
	local file="$1"
	shift
	local baseline_file

	for baseline_file in "$@"; do
		if [[ "$file" == "$baseline_file" ]]; then
			return 0
		fi
	done

	return 1
}

while IFS= read -r -d '' file; do
	[[ -f "$file" ]] || continue
	case "$file" in
		src/*.js|src/*.jsx|src/*.mjs)
			if is_legacy_baseline_file "$file" "${legacy_js_baseline[@]}"; then
				echo "Skipping JavaScript file with legacy lint baseline: $file"
			else
				js_files+=("$file")
			fi
			;;
		src/*.css|src/*.scss)
			if is_legacy_baseline_file "$file" "${legacy_style_baseline[@]}"; then
				echo "Skipping stylesheet with legacy lint baseline: $file"
			else
				style_files+=("$file")
			fi
			;;
	esac
done < <(git diff --name-only -z --diff-filter=ACMR "$base_sha" "$head_sha")

if [[ ${#js_files[@]} -eq 0 ]]; then
	echo "No changed JavaScript source files to lint."
else
	npx wp-scripts lint-js "${js_files[@]}"
fi

if [[ ${#style_files[@]} -eq 0 ]]; then
	echo "No changed stylesheet source files to lint."
else
	npx wp-scripts lint-style "${style_files[@]}"
fi
