.PHONY: start start-with-seed start-without-seed stop reset-db exec shell install composer-install migrate seed reset setup check test unit-test unit-test-all cs-check cs-fix release-start release-finish hotfix-start hotfix-finish feature-start bump-version docker

# Docker container management (host commands)
start: start-with-seed

start-with-seed:
	bash start.sh

start-without-seed:
	bash start.sh --no-seed

stop:
	bash stop.sh

reset-db:
	bash scripts/reset-dev-db.sh

# Container execution helpers
exec:
	docker exec -it mission-ki-php bash -c "cd /app && bash"

shell: exec

# Composer commands (executed inside container)
install:
	docker exec -it mission-ki-php bash -c "cd /app && composer install"

composer-install: install

# Database commands (executed inside container)
migrate:
	docker exec -it mission-ki-php bash -c "cd /app && composer db:migrate"

seed:
	docker exec -it mission-ki-php bash -c "cd /app && composer db:seed"

reset:
	docker exec -it mission-ki-php bash -c "cd /app && composer db:reset"

setup:
	docker exec -it mission-ki-php bash -c "cd /app && composer db:setup"

# Code quality commands (executed inside container)
check:
	docker exec -it mission-ki-php bash -c "cd /app && composer check"

test:
	docker exec -it mission-ki-php bash -c "cd /app && composer test"

unit-test:
	docker exec -it mission-ki-php bash -c "cd /app && php vendor/bin/phpunit tests/TestCase/Model/ --testdox"

unit-test-all:
	docker exec -it mission-ki-php bash -c "cd /app && php vendor/bin/phpunit --testdox"

cs-check:
	docker exec -it mission-ki-php bash -c "cd /app && composer cs-check"

cs-fix:
	docker exec -it mission-ki-php bash -c "cd /app && composer cs-fix"

# Git workflow commands (host commands)
release-start:
	bash scripts/git/release-start.sh

release-finish:
	bash scripts/git/release-finish.sh

hotfix-start:
	bash scripts/git/hotfix-start.sh

hotfix-finish:
	bash scripts/git/hotfix-finish.sh

feature-start:
	bash scripts/git/feature-start.sh

bump-version:
	bash scripts/git/bump-version-and-create-tag.sh
