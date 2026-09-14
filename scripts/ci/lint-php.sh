#!/usr/bin/env bash
set -euo pipefail

checked=0
failed=0
while IFS= read -r -d '' file; do
	checked=$((checked + 1))
	if ! php -l "$file" >/dev/null; then
		failed=1
	fi
done < <(git ls-files -z '*.php')

if (( failed != 0 )); then
	echo "PHP syntax validation failed." >&2
	exit 1
fi

echo "Validated PHP syntax in $checked tracked files."
