# Slim API Tasks

## Run composer install

## Run the test case in docker env - 
- from root dir - docker exec -it <container-name> /bin/sh
- from docker shell - ./vendor/bin/phpunit

## Run the migration command through docker 
- docker-compose exec app php artisan migrate

## Swagger Open API end point -
- http://localhost:8080/docs/openapi.json

