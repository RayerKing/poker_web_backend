# Sestaví docker odznovu
build:
	@docker-compose up -d --build

# Nahodí docker
up:
	@docker-compose up

# Vypne docker
down:
	@docker-compose down

# první spuštění
first:
	@docker-compose up -d --build
	@docker-compose exec backend composer install

# spuštění vs code v dané složce projektu
vscode:
	@code .

# změna práv uživatele v daném projektu
prava:
	@sudo chown -R $USER:$USER .

# windows prohlížeč
win:
	@explorer .

# přidá network, použít, pokud chybí
network:
	@docker network create poker-network


