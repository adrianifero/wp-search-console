#!/usr/bin/env bash
# Build a WordPress.org-compatible zip: top-level folder must be at-search-console.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

VERSION="$(grep -E '^\s*\* Version:' at-search-console.php | head -1 | awk '{print $3}')"
SLUG="at-search-console"
STAGE="$(mktemp -d)"
OUT="${ROOT}/dist/${SLUG}-${VERSION}.zip"
rm -f "$OUT"

mkdir -p "${ROOT}/dist" "${STAGE}/${SLUG}/img"
# Match .distignore: wordpress.org uses readme.txt, not GitHub README.md.
cp at-search-console.php readme.txt "${STAGE}/${SLUG}/"
cp img/icon-256x256.png img/screenshot-1.png "${STAGE}/${SLUG}/img/"

# Do not ship OS junk or git metadata.
(
  cd "$STAGE"
  zip -r "$OUT" "$SLUG" -x '*/.DS_Store' '*/.git/*'
)

rm -rf "$STAGE"
echo "Wrote $OUT"
unzip -l "$OUT" | head -20
