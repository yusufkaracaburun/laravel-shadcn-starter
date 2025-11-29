.PHONY: help build build-dev dev staging prod up down restart logs logs-staging logs-prod shell shell-staging shell-prod test optimize migrate migrate-staging migrate-prod

COMPOSE_FILE := .deployment/docker-compose.yml
COMPOSE := docker-compose -f $(COMPOSE_FILE)

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build production Docker image (run from api/ directory)
	docker build -f .deployment/Dockerfile -t laravel-api:latest .

build-dev: ## Build development Docker image (run from api/ directory)
	docker build -f .deployment/Dockerfile.dev -t laravel-api:dev .

dev: ## Start development environment (SQLite, dev dependencies)
	$(COMPOSE) --profile dev up -d
	@echo "Development API is running at http://localhost:8000"

staging: ## Start staging environment (MySQL, Redis, with debugging)
	$(COMPOSE) --profile staging up -d
	@echo "Staging API is running at http://localhost:8001"

prod: ## Start production environment (MySQL, Redis, optimized)
	$(COMPOSE) --profile prod up -d
	@echo "Production API is running at http://localhost:80"

up: dev ## Alias for dev

down: ## Stop all containers
	$(COMPOSE) --profile dev down
	$(COMPOSE) --profile staging down
	$(COMPOSE) --profile prod down

down-dev: ## Stop development containers
	$(COMPOSE) --profile dev down

down-staging: ## Stop staging containers
	$(COMPOSE) --profile staging down

down-prod: ## Stop production containers
	$(COMPOSE) --profile prod down

restart: ## Restart development containers
	$(COMPOSE) --profile dev restart

restart-staging: ## Restart staging containers
	$(COMPOSE) --profile staging restart

restart-prod: ## Restart production containers
	$(COMPOSE) --profile prod restart

logs: ## Show development container logs
	$(COMPOSE) --profile dev logs -f api

logs-staging: ## Show staging container logs
	$(COMPOSE) --profile staging logs -f api-staging

logs-prod: ## Show production container logs
	$(COMPOSE) --profile prod logs -f api-prod

shell: ## Open shell in development API container
	$(COMPOSE) --profile dev exec api sh

shell-staging: ## Open shell in staging API container
	$(COMPOSE) --profile staging exec api-staging sh

shell-prod: ## Open shell in production API container
	$(COMPOSE) --profile prod exec api-prod sh

test: ## Run tests in development container
	$(COMPOSE) --profile dev exec api php artisan test

optimize: ## Optimize for production (run inside container)
	$(COMPOSE) --profile prod exec api-prod php artisan optimize

migrate: ## Run migrations in development
	$(COMPOSE) --profile dev exec api php artisan migrate

migrate-staging: ## Run migrations in staging
	$(COMPOSE) --profile staging exec api-staging php artisan migrate --force

migrate-prod: ## Run migrations in production
	$(COMPOSE) --profile prod exec api-prod php artisan migrate --force

seed: ## Run seeders in development
	$(COMPOSE) --profile dev exec api php artisan db:seed

seed-staging: ## Run seeders in staging
	$(COMPOSE) --profile staging exec api-staging php artisan db:seed

