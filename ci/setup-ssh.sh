#!/usr/bin/env bash
set -euo pipefail

mkdir -p ~/.ssh
chmod 700 ~/.ssh

if [[ -n "${SSH_PRIVATE_KEY:-}" ]]; then
    # Pattern fiable : variable STRING en base64. La livraison des variables
    # de type FILE par le runner peut produire une clé illisible
    # ("error in libcrypto" à ssh-add).
    printf '%s\n' "${SSH_PRIVATE_KEY}" | base64 -d > ~/.ssh/id_rsa
else
    cp "${SSH_PRIVATE_KEY_FILE}" ~/.ssh/id_rsa
fi
chmod 600 ~/.ssh/id_rsa

ssh-keyscan -H -p "$DEPLOY_PORT" "$DEPLOY_HOST" >> ~/.ssh/known_hosts
chmod 644 ~/.ssh/known_hosts

eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_rsa
