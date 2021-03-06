# Symfony entity history example

Here is example of Doctrine database entities changes based on EntitySubscriber.

Implemented simple example of REST API. Rest api is implemented in one controller and isn't very good designed.
But it's just for demonstration purposes.

There is database changes table structure:
https://dbdiagram.io/d/5d88f707ff5115114db48b01

# Install

1 . Clone repository

```
git clone https://github.com/antonshell/symfony-entity-history-example.git
```

2 . Install dependencies

```
cd symfony-entity-history-example
composer install
```

3 . Configure database connection

```
nano .env.local
```

```
DB_USER=root
DB_PASSWORD=password
DB_HOST=localhost
DB_PORT=3306
DB_NAME=entity_history_example
DATABASE_URL=mysql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?serverVersion=5.7
```

4 . Create database

```
php bin/console doctrine:database:create
```

OR

```
CREATE DATABASE entity_history_example CHARACTER SET utf8 COLLATE utf8_general_ci;
```

5 . Apply migrations

```
php bin/console doctrine:migrations:migrate
```

6 . Run server  

```
php bin/console server:start *:8060
```

If you need to stop server

```
php bin/console server:stop
```

7 . Open test route

```
curl --request GET \
  --url http://127.0.0.1:8060/
```

# API requests examples

1 . Index

```
curl --request GET \
  --url http://127.0.0.1:8060/ \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86
```

2 . Get All

```
curl --request GET \
  --url http://127.0.0.1:8060/cars \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86
```

3 . Get By ID

```
curl --request GET \
  --url http://127.0.0.1:8060/cars/1 \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86
```

4 . Create entity

```
curl --request POST \
  --url http://127.0.0.1:8060/cars/ \
  --header 'content-type: application/json' \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86 \
  --data '{
	"vendor": "BMW",
	"model": "X5",
	"year": "2017"
}'
```

5 . Update entity

```
curl --request PUT \
  --url http://127.0.0.1:8060/cars/2 \
  --header 'content-type: application/json' \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86 \
  --data '{
	"vendor": "BMW",
	"model": "X5",
	"year": "2015"
}'
```

6 . Delete entity

```
curl --request DELETE \
  --url http://127.0.0.1:8060/cars/102 \
  --cookie PHPSESSID=vfnaej1o7gahktrh56omu03o86
```