.PHONY: up down build

up:
	docker-compose config -q && \
	docker-compose up -d --force-recreate

down:
	docker-compose config -q && \
	docker-compose down --remove-orphans

reboot:
	make down && make up

build:
	docker-compose config -q && \
	docker-compose build --pull

rebuild:
	make down && make build && make up

composer-install:
	docker-compose config -q && \
	docker-compose exec php composer install

composer-update:
	docker-compose config -q && \
	docker-compose exec php composer update