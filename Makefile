app=reap
php_container=reap_php_1
db_container=reap_mysqldb_1
db_name=reap
db_pass=survey54
db_user=survey54
env=development
url=localhost:8082

.PHONY: help all

help:
	@echo
	@echo usage: make access container=application
	@echo
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo

# Omitting @ from the start of each cmd makes pipeline echo the cmd before execution.

ci-install-composer-deps: ## Run composer install
	composer config --global --auth gitlab-token.gitlab.com ${ACCESS_TOKEN}
	composer install --no-dev --no-progress

ci-run-lint-test: ## Run linting tests
	phpcs --standard=PSR2 --ignore=tests -np src/

ci-run-unit-test: ## Run unit tests
	phpunit --configuration tests/unit/phpunit.xml --coverage-text --colors=never --log-junit phpunit-junit.xml

ci-load-db: ## Setup migrations && Seed DB
	phinx migrate -e testing
	phinx seed:run -e testing -s SurveySeeder -s InsightSeeder

ci-run-api-test: ## Start php server on index.php && Run api tests
	cp bootstrap.php index.php
	php -S $(url) </dev/null &>/dev/null &
	phpunit --configuration tests/api-tests/phpunit.xml
	rm index.php

ci-load-env: ## Load env vars
	bash docker/ci/load_env.sh

ci-deploy-app: ## Deploy service
	bash docker/ci/deploy_app.sh

up: ## Start docker containers
	@make down
	@docker-compose up -d
	@bash docker/wait_for_db.sh $(db_container) $(db_user) $(db_pass)
	@make migrate
	@make seed
	@make generate-sample-responses
	@make generate-am-responses

down: ## Stop docker containers
	@docker-compose down
	@sudo rm -rf data/

lint: ## Run lint test
	@bin/phpcs --standard=PSR2 --ignore=tests -np src/

cbf: ## Run cbf
	@bin/phpcbf --standard=PSR2 --ignore=tests -np src/

login-db: ## Log into the DB
	@docker exec -it $(db_container) script /dev/null -c "mysql -u $(db_user) -p$(db_pass) -D $(db_name)"

reset-db: ## Reset the DB
	@docker exec -it $(php_container) bin/phinx seed:run -e $(env) -s AllTruncater

setup: ## Start docker containers && Add entry to host file && Setup dependencies && Run migration && Seed
	@make down
	@sudo rm -rf vendor/ bin/
	@docker-compose up -d
	@echo '\n# $(app) web\n127.0.0.1\t$(app).local\n' | sudo tee -a /etc/hosts
	@docker exec -it $(php_container) script /dev/null -c "composer config --global --auth gitlab-token.gitlab.com 1YYP6wCydUppLtACxye3"
	@docker exec -it $(php_container) script /dev/null -c "composer install"
	@bash docker/wait_for_db.sh $(db_container) $(db_user) $(db_pass)
	@make migrate
	@make seed
	@make generate-sample-responses
	@make generate-all-questions

add-log: ## Setup logs in project
	@mkdir logs && chmod 0777 logs && touch logs/app.log && chmod 0777 logs/*

migrate: ## Setup migrations
	@docker exec -it $(php_container) bin/phinx migrate -e $(env)

reseed: ## Reset and Seed DB
	@make reset-db
	@make seed
	@make generate-sample-responses
	@make generate-all-questions

seed: ## Seed DB
	@docker exec -it $(php_container) bin/phinx seed:run -e $(env) -s SurveySeeder -s InsightSeeder

generate-sample-responses: ## Generate sample data
	@docker exec -it $(php_container) php /var/www/web/src/console.php --task generate-sample-responses

generate-am-responses: ## Generate AM data
	@docker exec -it $(php_container) php /var/www/web/src/console.php --task generate-am-responses

generate-colgate-pilot: ## Generate colgate pilot (with colpal user)
	@docker exec -it $(php_container) php /var/www/web/src/console.php --task generate-colgate-pilot

generate-all-questions: ## Generate survey with questions for all flows
	@docker exec -it $(php_container) php /var/www/web/src/console.php --task generate-all-questions

get-token: ## Get token: make get-token userId=011e5ab8-4021-4d21-8010-cdaa4891a70f type=RESPONDENT|ORGANIZATION|ADMIN
	@docker exec -it $(php_container) php /var/www/web/src/console.php --task get-token --data '{"userId":"$(userId)", "type":"$(type)"}'

update-lib:
	@sudo chown -R ${USER}:${USER} vendor/ bin/
	@composer update survey54/shared-library

ul:
	@composer remove survey54/shared-library
	@composer require survey54/shared-library

push:
	@git add . && git commit -m 'update: quick fix' && git push
