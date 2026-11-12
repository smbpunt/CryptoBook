.PHONY: ci fix test-db composer-check lint cs-fixer rector phpstan deptrac symfony-lsp

## ---- Quality ----

ci: tailwind test-db composer-check lint cs-fixer rector phpstan deptrac symfony-lsp test ## Run every check: composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, Symfony Language Tools, PHPUnit. `make -k ci` to keep going after a failure

fix: ## Fix the code: Rector, then PHP CS Fixer
	@echo "==> Rector"
	@$(EXEC) vendor/bin/rector process
	@echo "==> PHP CS Fixer"
	@$(EXEC) vendor/bin/php-cs-fixer fix

test-db: ## Migrate the test database
	@echo "==> Test database"
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration --env=test

composer-check: ## Validate composer.json and audit the dependencies
	@echo "==> Composer"
	@$(COMPOSER) validate --no-check-publish
	@$(COMPOSER) audit --locked

lint: ## Lint the container, Twig, YAML and the Doctrine mapping (test database)
	@echo "==> Lint"
	@$(SYMFONY) lint:container
	@$(SYMFONY) lint:twig templates
	@$(SYMFONY) lint:yaml config --parse-tags
	@$(SYMFONY) doctrine:schema:validate --env=test

cs-fixer: ## Check the code style (PHP CS Fixer, dry run)
	@echo "==> PHP CS Fixer"
	@$(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

rector: ## Check Rector rules (dry run)
	@echo "==> Rector"
	@$(EXEC) vendor/bin/rector process --dry-run

phpstan: ## Run PHPStan
	@echo "==> PHPStan"
	@$(EXEC) vendor/bin/phpstan analyse

deptrac: ## Check the dependencies between features (Deptrac)
	@echo "==> Deptrac"
	@$(EXEC) vendor/bin/deptrac analyse

symfony-lsp: ## Run the Symfony Language Tools diagnostics (beta)
	@echo "==> Symfony Language Tools"
	@$(EXEC) sh -c 'command -v symfony-lsp >/dev/null \
		|| { echo "symfony-lsp not found in the php image: run \"make build\", then \"make up\"."; exit 1; }; \
		exec symfony-lsp check'
