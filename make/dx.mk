.PHONY: worktree-guard setup-worktree teardown-worktree import\:dump-db icons

## ---- DX (worktrees) ----

# Main repo: given by Orca (ORCA_ROOT_PATH), otherwise parent of the common .git.
MAIN_REPO    := $(or $(ORCA_ROOT_PATH),$(shell dirname "$$(git rev-parse --path-format=absolute --git-common-dir 2>/dev/null)"))
MAIN_PROJECT := $(shell basename "$(MAIN_REPO)" | tr '[:upper:]' '[:lower:]' | tr -cd 'a-z0-9_-')

# In a worktree, let Docker pick a free port (the main repo keeps 8888). See `make url`.
ifneq ($(CURDIR),$(MAIN_REPO))
export HTTP_PORT ?= 0
endif

# Git-ignored files to bring over from the main repo (missing in a fresh worktree):
# - COPY  : files read by the container. Copied, not linked: ./ is mounted in /app, a link
#           to the main repo would point nowhere in there.
# - LINK  : files read on the host only (Claude Code, tools), shared between worktrees.
# - CLONE : heavy folders, cloned (APFS/reflink: instant, no disk space) to skip a full
#           composer install on start.
WORKTREE_COPY   ?= .env.local
WORKTREE_LINK   ?= CLAUDE.local.md .claude/settings.local.json
WORKTREE_CLONE  ?= vendor assets/vendor
WORKTREE_IMAGES  = php web

ifeq ($(shell uname),Darwin)
CP_CLONE = cp -cR
else
CP_CLONE = cp -R --reflink=auto
endif

worktree-guard:
	@if [ -n "$(ORCA_WORKTREE_PATH)" ] && [ "$(CURDIR)" != "$(ORCA_WORKTREE_PATH)" ]; then echo "Run from $(ORCA_WORKTREE_PATH)."; exit 1; fi
	@if [ "$(CURDIR)" = "$(MAIN_REPO)" ]; then echo "Run from a worktree, not from the main repo."; exit 1; fi

setup-worktree: worktree-guard ## Prepare a worktree: local files from the main repo, containers (no rebuild), migrations, fixtures
	@for f in $(WORKTREE_COPY); do \
		if [ -f "$(MAIN_REPO)/$$f" ] && [ ! -e "$$f" ]; then cp "$(MAIN_REPO)/$$f" "$$f" && echo "Copy  : $$f"; fi; \
	done
	@for f in $(WORKTREE_LINK); do \
		if [ -e "$(MAIN_REPO)/$$f" ] && [ ! -e "$$f" ] && [ ! -L "$$f" ]; then \
			mkdir -p "$$(dirname "$$f")" && ln -s "$(MAIN_REPO)/$$f" "$$f" && echo "Link  : $$f"; \
		fi; \
	done
	@for d in $(WORKTREE_CLONE); do \
		if [ -d "$(MAIN_REPO)/$$d" ] && [ ! -e "$$d" ]; then \
			mkdir -p "$$(dirname "$$d")" && $(CP_CLONE) "$(MAIN_REPO)/$$d" "$$d" && echo "Clone : $$d/"; \
		fi; \
	done
	@# Main repo images reused under the worktree project name: no rebuild.
	@for s in $(WORKTREE_IMAGES); do \
		docker image inspect $(COMPOSE_PROJECT_NAME)-$$s >/dev/null 2>&1 \
			|| docker tag $(MAIN_PROJECT)-$$s $(COMPOSE_PROJECT_NAME)-$$s \
			|| { echo "Image $(MAIN_PROJECT)-$$s not found: run make build in $(MAIN_REPO)."; exit 1; }; \
	done
	@$(DOCKER_COMP) up --detach --no-build --wait
	@# Aligns the cloned vendor/ with the worktree composer.lock (fast if up to date) + importmap:install.
	@$(COMPOSER) install --prefer-dist --no-progress --no-interaction
	@$(MAKE) --no-print-directory tailwind migrate fixtures users:show url

teardown-worktree: worktree-guard ## Run before deleting a worktree: removes containers, volumes (database included) and images
	@$(DOCKER_COMP) down --volumes --remove-orphans
	@for s in $(WORKTREE_IMAGES); do \
		docker image rm $(COMPOSE_PROJECT_NAME)-$$s >/dev/null 2>&1 && echo "Image : $(COMPOSE_PROJECT_NAME)-$$s removed" || true; \
	done

## ---- Database import ----

f ?= prod.tar.gz

import\:dump-db: ## Import a pg_dump (tar) into the dev database, f="path/to/dump.tar" (default prod.tar.gz)
	@test -f "$(f)" || { echo "Dump not found: $(f)"; exit 1; }
	@echo "Importing $(f) into database/app..."
	@$(DOCKER_COMP) cp "$(f)" database:/tmp/import.tar
	@$(DOCKER_COMP) exec -T database psql -U app -d postgres -c 'DROP DATABASE IF EXISTS app WITH (FORCE);' >/dev/null
	@$(DOCKER_COMP) exec -T database psql -U app -d postgres -c 'CREATE DATABASE app OWNER app;' >/dev/null
	@$(DOCKER_COMP) exec -T database pg_restore -U app -d app --no-owner --no-privileges --schema=public /tmp/import.tar
	@$(DOCKER_COMP) exec -T database rm -f /tmp/import.tar
	@echo ""
	@echo "Import done. Database stats:"
	@$(DOCKER_COMP) exec -T database psql -U app -d app -c "ANALYZE;" \
		-c "SELECT pg_size_pretty(pg_database_size('app')) AS size;" \
		-c "SELECT relname AS table, reltuples::bigint AS rows FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'public' AND c.relkind = 'r' ORDER BY reltuples DESC, relname LIMIT 10;"

## ---- Icons ----

icons: ## Regenerate the PNG icons from assets/images/favicon.svg
	@docker run --rm -v "$(CURDIR)/assets/images":/w -w /w alpine:3.22 sh -c '\
		apk add --no-cache rsvg-convert >/dev/null && \
		rsvg-convert -w 32 -h 32 favicon.svg -o favicon-32.png && \
		rsvg-convert -w 180 -h 180 -b "#17171C" favicon.svg -o apple-touch-icon.png && \
		rsvg-convert -w 512 -h 512 -b "#17171C" favicon.svg -o icon-512.png'
