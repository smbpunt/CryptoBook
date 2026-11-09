.DEFAULT_GOAL := help

SHELL := /bin/bash
.SHELLFLAGS := -o pipefail -c

# --- Variables ---
# Compose project = folder name (lowercase, normalised like Compose does): each worktree gets
# its own containers, volumes and images (<project>-php, <project>-web).
export COMPOSE_PROJECT_NAME ?= $(shell basename "$(CURDIR)" | tr '[:upper:]' '[:lower:]' | tr -cd 'a-z0-9_-')
DOCKER_COMP = docker compose
PHP_CONT    = $(DOCKER_COMP) exec php

EXEC     = $(DOCKER_COMP) exec -T php
PHP      = $(EXEC) php
COMPOSER = $(EXEC) composer
SYMFONY  = $(PHP) bin/console

# --- Includes ---
include make/docker.mk
include make/dx.mk

# --- Help ---
.PHONY: help
help: ## Show this help
	@echo ""
	@grep -hE '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'
	@echo ""
