#!/usr/bin/env bash
set -euo pipefail

REQUIRED_VARS=(
    SSH_PRIVATE_KEY
    DEPLOY_HOST
    DEPLOY_USER
    DEPLOY_PATH
    DEPLOY_KEEP_RELEASES
    DEPLOY_PORT
)

missing=()
for var in "${REQUIRED_VARS[@]}"; do
    if [[ -z "${!var:-}" ]]; then
        missing+=("$var")
    fi
done

if [[ ${#missing[@]} -gt 0 ]]; then
    echo "ERROR: Missing required environment variables:"
    printf '  - %s\n' "${missing[@]}"
    exit 1
fi

echo "All required environment variables are set."
