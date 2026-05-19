DOCKER_NAME = poker_web_backend-backend-1

# Sestaví docker odznovu
.PHONY: build
build:
	@docker-compose up -d --build

# Nahodí docker
.PHONY: up
up:
	@docker-compose up

# Vypne docker
.PHONY: down
down:
	@docker-compose down

# první spuštění
.PHONY: first
first:
	@docker-compose up -d --build
	@docker-compose exec backend composer install

# spuštění vs code v dané složce projektu
.PHONY: vscode
vscode:
	@code .

# změna práv uživatele v daném projektu
.PHONY: prava
prava:
	@sudo chown -R $USER:$USER .

# windows prohlížeč
.PHONY: win
win:
	@explorer .

# přidá network, použít, pokud chybí
.PHONY: network
network:
	@docker network create poker-network

# vstup do dockeru
.PHONY: bash
bash:
	@docker-compose exec backend bash

# vytvoření nové entity v dockeru
.PHONY: entity
entity: 
	@docker exec -it $(DOCKER_NAME) php bin/console make:entity

# vytvoření nové migrace v dockeru
.PHONY: migrate
migrate:
	@docker exec -it $(DOCKER_NAME) php bin/console make:migration

# nasazení migrací v dockeru pro produkční databázi (poker_app)
.PHONY: migrate-up
migrate-up:
	@docker exec -it $(DOCKER_NAME) php bin/console doctrine:migrations:migrate

.PHONY: migrate-up-test
migrate-up-test:
	@docker exec -it $(DOCKER_NAME) php bin/console doctrine:migrations:migrate --env=test

# sesazení poslední migrace v dockeru
.PHONY: migrate-down
migrate-down:
	@docker exec -it $(DOCKER_NAME) php bin/console doctrine:migrations:migrate prev

.PHONY: migrate-down-test
migrate-down-test:
	@docker exec -it $(DOCKER_NAME) php bin/console doctrine:migrations:migrate prev --env=test

# spuštění testů => nutno spouštět v dockeru
.PHONY: unit
unit:
	@php bin/phpunit


