# Set default shell
set shell := ["bash", "-c"]

drun := "docker run -it -w /data -v ${PWD}:/data:delegated"
drun-base := "docker run -it -w /data -v ${PWD}:/data:delegated --rm registry.gitlab.com/grahamcampbell/php:8.2-base"
drun-cli := "docker run -it -w /data -v ${PWD}:/data:delegated --rm registry.gitlab.com/grahamcampbell/php:8.2-cli"

_default:
  @just --choose

# Run composer commands
composer *args:
    {{drun-base}} composer {{args}}

# Run phpstan commands
phpstan *args:
    {{drun-base}} ./vendor/bin/phpstan {{args}}

phpstan-analyse *args:
    just phpstan analyse src tests {{args}}

phpstan-analyze *args:
    just phpstan-analyse {{args}}

# Run phpstan
psalm *args:
    {{drun-cli}} ./vendor/bin/psalm.phar {{args}}

psalm-baseline:
    just psalm --set-baseline=psalm-baseline.xml

# Run phpunit
phpunit *args:
    {{drun-cli}} ./vendor/bin/phpunit --no-coverage {{args}}

# Run phpunit with coverage
phpunit-coverage *args:
    {{drun}} -e XDEBUG_MODE=coverage --rm registry.gitlab.com/grahamcampbell/php:8.2 vendor/bin/phpunit --coverage-html cov {{args}}

test:
    just phpunit
    just phpstan-analyse

run *args:
    {{drun-cli}} {{args}}
