#!/usr/bin/env bash
set -euo pipefail

if [[ -n "${CI_SERVER_HOST:-}" && -n "${CI_PROJECT_PATH:-}" ]]; then
    export DEPLOY_REPOSITORY="git@${CI_SERVER_HOST}:${CI_PROJECT_PATH}.git"
fi
