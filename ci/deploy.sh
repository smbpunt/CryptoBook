#!/usr/bin/env bash
set -euo pipefail

source "$(dirname "$0")/common.sh"

REVISION="${CI_COMMIT_SHA:-$(git rev-parse HEAD)}"

dep deploy --revision="$REVISION"
