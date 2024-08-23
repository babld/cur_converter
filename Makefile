.DEFAULT_GOAL := help

build: ## собрать контейнеры
	docker compose build

up: ## запустить контейнеры
	docker compose up -d php mysql
	docker compose ps

down: ## остановить и удалить контейнеры
	docker compose down

stop: ## остановить контейнеры
	docker compose stop

initialize: ## создать базу, накатить миграции
	echo "drop database if exists cur" | docker compose exec -T mysql mysql -uroot -pverysecret
	echo "create database cur" | docker compose exec -T mysql mysql -uroot -pverysecret
	echo "create user if not exists 'cur'@'%' identified by 'cur'" | docker compose exec -T mysql mysql -uroot -pverysecret;
	echo "grant all on *.* to 'cur'@'%'" | docker compose exec -T mysql mysql -uroot -pverysecret
	echo "flush privileges" | docker compose exec -T mysql mysql -uroot -pverysecret
	docker compose run --rm php composer install
	docker compose run --rm php /app/yii migrate --interactive=0

composer_install: ## установить зависимости composer
	docker compose run --rm php composer install

composer_update: ## обновить зависимости composer
	docker compose run --rm php composer update

cron_install: ## обновить зависимости composer
	docker compose run --rm php apt install cron

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'
