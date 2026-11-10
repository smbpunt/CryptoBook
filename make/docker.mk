.PHONY: build up down logs bash url test tailwind scheduler migrate fixtures users\:show

## ---- Docker ----

build: ## Build Docker images
	@$(DOCKER_COMP) build --pull

up: ## Start containers (detached)
	@$(DOCKER_COMP) up --detach --wait
	@$(MAKE) --no-print-directory url

down: ## Stop containers
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Tail containers logs
	@$(DOCKER_COMP) logs --tail=0 --follow

bash: ## Shell into the php container
	@$(PHP_CONT) sh

url: ## Show the app URL (port chosen by Docker in a worktree)
	@echo "App : http://localhost:$$($(DOCKER_COMP) port web 80 | sed 's/.*://')"

test: ## Run PHPUnit, pass options via c="...", e.g. make test c="--filter Ownership"
	@$(eval c ?=)
	@$(PHP) bin/phpunit $(c)

tailwind: ## Build the Tailwind CSS (var/tailwind/), pass c="--watch" to rebuild on change
	@$(eval c ?=)
	@$(SYMFONY) tailwind:build $(c)

scheduler: ## Run the Scheduler worker in the foreground, Ctrl+C to stop (no permanent worker in dev: Fixer quota)
	@$(PHP_CONT) php bin/console messenger:consume scheduler_default -vv

## ---- Database ----

migrate: ## Run migrations on the dev and test databases
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration --env=test

fixtures: ## Load fixtures into the dev database (purges it)
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

users\:show: ## List 5 users (admins first), fixtures password: "password"
	@$(SYMFONY) dbal:run-sql "SELECT id, email, roles FROM \"user\" ORDER BY roles::text LIKE '%ROLE_ADMIN%' DESC, id LIMIT 5"
