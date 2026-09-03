.PHONY: help setup up down restart build logs shell composer artisan migrate fresh test cache-clear

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'

setup: ## Installation initiale (build, up, .env, clé, migrations, seeders)
	docker compose build
	docker compose up -d --wait
	docker compose exec app sh -c "[ -f .env ] || cp .env.example .env"
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	@echo "\n\033[32m=== Installation terminee ===\033[0m"
	@echo "API        : http://localhost:8080"
	@echo "phpMyAdmin : http://localhost:8081"

up: ## Démarre les conteneurs
	docker compose up -d

down: ## Arrête les conteneurs
	docker compose down

restart: ## Redémarre les conteneurs
	docker compose restart

build: ## Reconstruit les images
	docker compose build --no-cache

logs: ## Affiche les logs
	docker compose logs -f

shell: ## Ouvre un shell dans le conteneur app
	docker compose exec app bash

composer: ## Lance une commande composer (ex: make composer c="require paquet")
	docker compose exec app composer $(c)

artisan: ## Lance une commande artisan (ex: make artisan c="migrate")
	docker compose exec app php artisan $(c)

migrate: ## Lance les migrations
	docker compose exec app php artisan migrate

fresh: ## Reset la base + seeders
	docker compose exec app php artisan migrate:fresh --seed

test: ## Lance les tests
	docker compose exec app php artisan test

cache-clear: ## Vide tous les caches Laravel
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

pint: ## Formate le code (Laravel Pint)
	docker compose exec app ./vendor/bin/pint

pint-test: ## Vérifie le formatage sans modifier les fichiers
	docker compose exec app ./vendor/bin/pint --test
