#!/usr/bin/env bash
set -euo pipefail

ZIP_FILE="${1:-}"

if [[ -z "$ZIP_FILE" || ! -f "$ZIP_FILE" ]]; then
	echo "Release ZIP not found: $ZIP_FILE" >&2
	exit 1
fi

FORBIDDEN_PATTERN='(^|/)\.github/|(^|/)node_modules/|(^|/)tests/|(^|/)docs/|AGENTS\.md|docker-compose\.yml|(^|/)vendor/|(^|/)assets/src/|composer\.(json|lock)|phpunit\.xml|playwright\.config|(^|/)\.cursor/'
REQUIRED_ENTRIES=(
	'magazine73/magazine73.php'
	'magazine73/uninstall.php'
	'magazine73/readme.txt'
	'magazine73/license.txt'
	'magazine73/assets/dist/js/magazine73-viewer.js'
	'magazine73/assets/dist/js/magazine73-editor.js'
	'magazine73/languages/magazine73.pot'
	'magazine73/third-party/stpageflip/LICENSE'
	'magazine73/third-party/stpageflip/NOTICE.md'
)

mapfile -t ZIP_ENTRIES < <(zipinfo -1 "$ZIP_FILE")

for entry in "${REQUIRED_ENTRIES[@]}"; do
	found=false
	for zip_entry in "${ZIP_ENTRIES[@]}"; do
		if [[ "$zip_entry" == "$entry" ]]; then
			found=true
			break
		fi
	done

	if [[ "$found" != true ]]; then
		echo "Missing required ZIP entry: $entry" >&2
		exit 1
	fi
done

for zip_entry in "${ZIP_ENTRIES[@]}"; do
	if [[ "$zip_entry" =~ $FORBIDDEN_PATTERN ]]; then
		echo "Forbidden ZIP entry detected: $zip_entry" >&2
		exit 1
	fi
done

echo "Release ZIP verification passed for $ZIP_FILE"
