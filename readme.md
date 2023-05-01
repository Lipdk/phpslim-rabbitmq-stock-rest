## PHP Challenge

This is a project using Slim 4, RabbitMQ, MySQL, Docker and Nginx with DDEV

## Requirements

- Docker <https://www.docker.com/>
- DDEV <https://ddev.readthedocs.io/en/stable/>

## How to use

### 1. Clone the repository

```sh
  $ git clone <git@repository_url.git>
```

### 2. Go to the project folder

```sh
  $ cd php-challenge
```

### 3. Create a .env file

```sh
  $ cp .env.example .env
```

### 4. Start the application

DDEV will make sure that all the containers will be up and running when you run the command below.
Composer dependencies will be installed automatically, and the application will be available at <http://php-challenge.ddev.site> 

```sh
  $ ddev start
```

The migrations will be executed automatically by the DDEV, but if you want to run manually, you can use the command below:

```sh
  $ ddev phinx migrate -e development
```

Seed will be executed automatically by the DDEV, but if you want to run manually, you can use the command below:
```sh
  $ ddev phinx seed:run
```

An user will be available to use the application with the credentials below:

```
Username: root
Password: secret
Email: admin@email.com
```

## Project URLs

- <https://php-challenge.ddev.site> - Application URL
- <http://php-challenge.ddev.site:15672> - RabbitMQ Management
- <http://php-challenge.ddev.site:8025> - Mailhog UI
- <https://php-challenge.ddev.site:8037> - PHPMyAdmin

## Email Sending

The application is using RabbitMQ to send emails, so you need to start the RabbitMQ consumer with the command below:

```sh
  $ ddev console rabbitmq:consume-email
```

To see the emails you can check Mailhog inbox at <http://php-challenge.ddev.site:8025>

## Routes

### POST ``/user/create``

This route will create a new user in the database.

```json
{
  "name": "User Full Name",
  "password": "secret",
  "email": "email@domain.com",
  "username": "nickname"
}
```

### POST ``/auth``

- This route will authenticate the user, and return a JWT token. 
- The authorization is type Basic Auth, with email and password combination 

Request:
```json
{
  "email": "email@domain.com",
  "password": "secret"
}
```

Response:
```json
{
  "success": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHBpcmVkX2F0IjoiMjAyMy0wNS0wMiAwMDoxMzo1NyIsImVtYWlsIjoiZmVsaXBlQHZveW8uY29tLmJyIn0.cdCeDvSglSevnvUlkIO_lsr6TN6k5C-s9971NlHbuDU"
  }
}
```

### GET ``/stock``

- This endpoint will return all the stocks available in the database.
- The stock code should be sent using a param ``q``, eg: ``stock?q=aapl.us``
- The authorization should be type Bearer Token, with the token returned in the previous endpoint.
- The response will be a JSON with the stock information coming from `Stooq API`
- A log will be stored in the DB, and a message will be sent to the RabbitMQ queue.
When the RabbitMQ consumer is enabled, the email will be sent to the user.

Response:
```json
{
    "name": "APPLE",
    "symbol": "AAPL.US",
    "open": 169.28,
    "high": 170.44999999999999,
    "low": 168.63999999999999,
    "close": 169.59
}
```

### GET ``/history``

The endpoint will return all the logs stored in the database for the given user.
This route also needs the Authorization header with the Bearer Token.

Response example:
```json
[
    {
        "symbol": "GOOGL.US",
        "date": "2023-05-01T23:24:26Z",
        "open": 106.84,
        "high": 107.98999999999999,
        "low": 106.81999999999999,
        "close": 107.2,
        "name": "ALPHABET"
    },
    {
        "symbol": "AAPL.US",
        "date": "2023-05-01T23:22:58Z",
        "open": 169.28,
        "high": 170.44999999999999,
        "low": 168.63999999999999,
        "close": 169.59,
        "name": "APPLE"
    }
]
```

## Postman Collection

A postman collection is available in the `/postman` folder with all the routes available.

## Unit Tests

The unit tests are using PHPUnit, and you can run the tests with the command below:

```sh
  $ ddev composer test
```

## Useful DDEV Commands

Other useful ddev commands are listed below:

- ``ddev sequelpro``: Open Sequel Pro with the database connection. Sequelace is also available
- ``ddev ssh``: SSH into the web container
- ``ddev exec``: Execute a command inside the web container
- ``ddev logs``: Show the logs of the containers
- ``ddev stop``: Stop the container
- ``ddev composer install``: Install the composer dependencies
- ``ddev snapshot``: Create a snapshot of the current state of the project