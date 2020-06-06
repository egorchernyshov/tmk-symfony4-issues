help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

start: erase build up db phpunit ## clean current environment, recreate dependencies and spin up again

stop: ## stop environment
		docker-compose down

rebuild: start ## same as start

erase: ## stop and delete containers, clean volumes.
		docker-compose down
		docker-compose rm -v -f

build: ## build environment
		docker-compose build

up: ## spin up environment and initialize composer and project dependencies
		docker-compose up -d
		docker-compose exec php-cli composer install

db: ## recreate database
		docker-compose exec php-cli php bin/console doctrine:database:drop --force --if-exists --no-interaction
		docker-compose exec php-cli php bin/console doctrine:database:create --if-not-exists --no-interaction
		docker-compose exec php-cli php bin/console doctrine:migrations:migrate --no-interaction
		docker-compose exec php-cli php bin/console doctrine:fixtures:load --no-interaction

phpunit: ## execute project unit tests
		docker-compose exec php-cli php bin/phpunit
