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

while IFS= read -r -d '' file; do
	[[ -f "$file" ]] || continue
	case "$file" in
		src/*.js|src/*.jsx|src/*.mjs)
			js_files+=("$file")
			;;
		src/*.css|src/*.scss)
			style_files+=("$file")
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
