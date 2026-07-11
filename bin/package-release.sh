#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SLUG="magazine73"
PLUGIN_DIR="$ROOT_DIR/plugin/${PLUGIN_SLUG}"
BUILD_DIR="$ROOT_DIR/build"
MAIN_FILE="$PLUGIN_DIR/${PLUGIN_SLUG}.php"
EXPECTED_VERSION="${1:-}"

if [[ ! -f "$MAIN_FILE" ]]; then
	echo "Plugin main file not found: $MAIN_FILE" >&2
	exit 1
fi

HEADER_VERSION="$(grep -E '^\s*\*\s*Version:' "$MAIN_FILE" | head -n1 | awk '{print $3}')"
CONSTANT_VERSION="$(grep -E "define\(\s*'MAGAZINE73_VERSION'" "$MAIN_FILE" | sed -E "s/.*'([0-9.]+)'.*/\1/")"
README_VERSION="$(grep -E '^Stable tag:' "$PLUGIN_DIR/readme.txt" | awk '{print $3}')"

if [[ "$HEADER_VERSION" != "$CONSTANT_VERSION" || "$HEADER_VERSION" != "$README_VERSION" ]]; then
	echo "Version mismatch across plugin header, constant, and readme.txt" >&2
	echo "header=$HEADER_VERSION constant=$CONSTANT_VERSION readme=$README_VERSION" >&2
	exit 1
fi

if [[ -n "$EXPECTED_VERSION" && "$EXPECTED_VERSION" != "$HEADER_VERSION" ]]; then
	echo "Expected version $EXPECTED_VERSION but plugin is $HEADER_VERSION" >&2
	exit 1
fi

if [[ -f "$ROOT_DIR/package.json" ]]; then
	npm --prefix "$ROOT_DIR" ci
	npm --prefix "$ROOT_DIR" run build
fi

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/${PLUGIN_SLUG}"
rsync -a --delete "$PLUGIN_DIR/" "$BUILD_DIR/${PLUGIN_SLUG}/"

OUTPUT_ZIP="$ROOT_DIR/${PLUGIN_SLUG}-${HEADER_VERSION}.zip"
rm -f "$OUTPUT_ZIP"
(
	cd "$BUILD_DIR"
	zip -rq "$OUTPUT_ZIP" "$PLUGIN_SLUG"
)

if zipinfo -1 "$OUTPUT_ZIP" | grep -Eq '(^|/)\.github/|(^|/)node_modules/|(^|/)tests/|(^|/)docs/|AGENTS\.md|docker-compose\.yml'; then
	echo "Release ZIP contains excluded development files" >&2
	exit 1
fi

echo "$OUTPUT_ZIP"
