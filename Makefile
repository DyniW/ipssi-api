CONSOLE=php bin/console
FIG=docker-compose

ifeq ($(OS),Windows_NT)
    EXECROOT=$(FIG) exec app
    EXEC=$(FIG) exec -u $(USERID):$(GROUPID) app
else
    USERID=$(shell id -u)
    GROUPID=$(shell id -g)
    HAS_DOCKER:=$(shell command -v $(FIG) 2> /dev/null)

    ifdef HAS_DOCKER
        ifdef APP_ENV
            EXECROOT=$(FIG) exec -e APP_ENV=$(APP_ENV) app
            EXEC=$(FIG) exec -e APP_ENV=$(APP_ENV) -u $(USERID):$(GROUPID) app
        else
            EXECROOT=$(FIG) exec app
            EXEC=$(FIG) exec -u $(USERID):$(GROUPID) app
        endif
    else
        EXECROOT=
        EXEC=
    endif
endif

.DEFAULT_GOAL := help

.PHONY: help ## Generate list of targets with descriptions
help:
		@grep '##' Makefile \
		| grep -v 'grep\|sed' \
		| sed 's/^\.PHONY: \(.*\) ##[\s|\S]*\(.*\)/\1:\t\2/' \
		| sed 's/\(^##\)//' \
		| sed 's/\(##\)/\t/' \
		| expand -t14

##
## Project setup & day to day shortcuts
##---------------------------------------------------------------------------

.PHONY: start ## Start the project (Install in first place)
start:
start: docker-compose.override.yml .env.dev .env.test
	$(FIG) pull || true
	$(FIG) build
	$(FIG) up -d
	${MAKE} perm
	${MAKE} vendor
	${MAKE} db

.PHONY: stop ## stop the project
stop:
stop:
	$(FIG) down

.PHONY: clear ## Remove all the cache, the logs, the sessions and parameters files
clear: perm
	$(EXECROOT) rm -rf .env docker-compose.override.yml
	$(EXECROOT) rm -rf var vendor public/bundles

.PHONY: cc ## Clear the cache in dev env
cc: perm
	$(EXECROOT) rm -rf var/cache/*
	$(EXEC) $(CONSOLE) cache:clear --no-warmup
	$(EXEC) $(CONSOLE) cache:warmup

.PHONY: exec ## Run bash in the app container
exec:
	$(EXEC) /bin/bash

##
## Database
##---------------------------------------------------------------------------

.PHONY: db ## Update or create the database
db:
	$(EXEC) $(CONSOLE) doctrine:database:create -q && ${MAKE} db-app-init || exit 0

.PHONY: db-app-init
db-app-init:
	${MAKE} db-migrate
	${MAKE} db-app-load

.PHONY: db-reset ## Reset database with migration and load fixtures
db-reset:
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists || exit 0
	$(EXEC) $(CONSOLE) doctrine:database:create
	${MAKE} db-app-init

.PHONY: db-diff ## Generate a migration by comparing your current database to your mapping information
db-diff:
	$(EXEC) $(CONSOLE) doctrine:migration:diff

.PHONY: db-migrate ## Migrate database schema to the latest available version
db-migrate:
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n --allow-no-migration

.PHONY: db-rollback ## Rollback the latest executed migration
db-rollback:
	$(EXEC) $(CONSOLE) d:m:e --down $(shell $(EXEC) $(CONSOLE) d:m:l) -n

.PHONY: db-app-load ## Reset the app database fixtures
db-app-load:
	$(EXEC) $(CONSOLE) doctrine:fixtures:load -n --append

##
## Tests
##---------------------------------------------------------------------------

.PHONY: tests ## Run all our tests
tests: cs
	APP_ENV=test ${MAKE} db-reset
	APP_ENV=test ${MAKE} schema-validate
	${MAKE} phpunit

.PHONY: cs ## Run all our cs
cs: phpcs phpstan

.PHONY: phpunit ## Run phpunit test suite
phpunit: cc
	$(EXEC) vendor/bin/phpunit

.PHONY: phpcs ## Run phpcs with our rule sets
phpcs:
	$(EXEC) vendor/bin/phpcs

.PHONY: phpcbf ## Run phpcbf with our rule sets
phpcbf:
	$(EXEC) vendor/bin/phpcbf

.PHONY: phpstan ## Run phpstan
phpstan:
	$(EXEC) vendor/bin/phpstan analyse src --level 6 -c phpstan.neon

.PHONY: Check Database Schema
schema-validate:
	$(EXEC) $(CONSOLE) doctrine:schema:validate

##
## Dependencies
##---------------------------------------------------------------------------

.PHONY: perm
perm:
	$(EXEC) mkdir -p var || exit 0
	$(EXECROOT) chmod -R 777 -R var || exit 0
	$(EXECROOT) chown $(USERID):$(GROUPID) -R var || exit 0

.PHONY: vendor
vendor:
	$(EXEC) composer install --prefer-dist --no-progress --no-suggest --no-interaction

##
## Dependencies Files
##---------------------------------------------------------------------------

docker-compose.override.yml: docker-compose.override.yml.dist
	cp docker-compose.override.yml.dist docker-compose.override.yml || true
	xcopy docker-compose.override.yml.dist docker-compose.override.yml || true

.env.test: .env.test.dist
	cp .env.test.dist .env.test || true
	xcopy .env.test.dist .env.test || true

.env.dev: .env.dev.dist
	cp .env.dev.dist .env.dev || true
	xcopy .env.dev.dist .env.dev || true
