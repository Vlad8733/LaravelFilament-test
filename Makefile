# VeloraShop Docker Commands
# Usage: make <target>

.PHONY: help build up down restart logs shell mysql redis test fresh assets queue

# Colors
BLUE := \033[34m
GREEN := \033[32m
RESET := \033[0m

help: ## Show this help
	@echo "$(BLUE)VeloraShop Docker Commands$(RESET)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(RESET) %s\n", $$1, $$2}'

# ========================================
# Docker Commands
# ========================================

build: ## Build Docker images
	docker compose build

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

logs: ## View container logs
	docker compose logs -f

logs-app: ## View app container logs
	docker compose logs -f app

# ========================================
# Shell Access
# ========================================

shell: ## Open shell in app container
	docker compose exec app bash

mysql: ## Open MySQL CLI
	docker compose exec mysql mysql -u eshop -psecret eshop

redis: ## Open Redis CLI
	docker compose exec redis redis-cli

# ========================================
# Laravel Commands
# ========================================

install: ## Install dependencies and setup
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	docker compose exec app php artisan storage:link

migrate: ## Run migrations
	docker compose exec app php artisan migrate

fresh: ## Fresh migration with seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Run seeders
	docker compose exec app php artisan db:seed

cache: ## Clear all caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear
	docker compose exec app php artisan app:clear-cache

cache-warm: ## Warm up application caches
	docker compose exec app php artisan app:warm-cache

optimize: ## Optimize for production
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache
	docker compose exec app php artisan app:warm-cache

# ========================================
# Queue & Jobs
# ========================================

queue: ## Start queue worker
	docker compose exec app php artisan queue:work

queue-restart: ## Restart queue workers
	docker compose exec app php artisan queue:restart

# ========================================
# Testing
# ========================================

test: ## Run tests
	docker compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker compose exec app php artisan test --coverage

pint: ## Run Laravel Pint
	docker compose exec app ./vendor/bin/pint

pint-check: ## Check code style without fixing
	docker compose exec app ./vendor/bin/pint --test

# ========================================
# Assets (Development)
# ========================================

npm-install: ## Install npm dependencies
	docker compose run --rm node npm install

npm-dev: ## Build assets for development
	docker compose run --rm node npm run dev

npm-build: ## Build assets for production
	docker compose run --rm node npm run build

# ========================================
# Quick Start
# ========================================

init: build up install npm-install npm-build ## Full project initialization
	@echo "$(GREEN)✓ VeloraShop is ready at http://localhost:8080$(RESET)"

dev: up ## Start development environment
	@echo "$(GREEN)✓ Development server running at http://localhost:8080$(RESET)"
