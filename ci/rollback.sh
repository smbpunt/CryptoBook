#!/usr/bin/env bash
set -euo pipefail

source "$(dirname "$0")/common.sh"

# Code only: rolling back a migration ("migrate prev") could lose data, it is undone by hand.
# The worker picks up the previous code at its --time-limit at the latest (1 h).
dep rollback
dep frankenphp:reload
