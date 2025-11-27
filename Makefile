.PHONY: test test-unit test-integration cups-up cups-down cups-logs install clean lint-init lint lint-fix analyze-init analyze

# Run unit tests via Docker (default)
test: test-unit

# Run unit tests via Docker
test-unit: install
	docker-compose run --rm php ./vendor/bin/phpunit --testsuite unit

# Run integration tests via Docker (requires CUPS server)
test-integration: install cups-up
	docker-compose run --rm php ./vendor/bin/phpunit --testsuite integration --group integration
	@$(MAKE) cups-down

# Run all tests via Docker
test-all: install
	docker-compose up -d --wait cups
	docker-compose run --rm php ./vendor/bin/phpunit
	docker-compose down

# Start CUPS server
cups-up:
	docker-compose up -d --wait cups
	@echo "CUPS server ready on localhost:6631"

# Stop CUPS server
cups-down:
	docker-compose down

# View CUPS server logs
cups-logs:
	docker-compose logs -f cups

# Install dependencies via Docker
install:
	docker-compose run --rm php composer install

# Install linter dependencies
lint-init:
	docker-compose run --rm php composer install --working-dir=.php-cs-fixer

# Run linter (dry-run)
lint: lint-init
	docker-compose run --rm php .php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff

# Run linter and fix issues
lint-fix: lint-init
	docker-compose run --rm php .php-cs-fixer/vendor/bin/php-cs-fixer fix

# Install PHPStan dependencies
analyze-init:
	docker-compose run --rm php composer install --working-dir=.phpstan

# Run PHPStan static analysis
analyze: analyze-init
	docker-compose run --rm php php -d memory_limit=512M .phpstan/vendor/bin/phpstan analyze

# Clean up
clean:
	docker-compose down -v
	rm -rf .phpunit.cache vendor .php-cs-fixer/vendor .phpstan/vendor
