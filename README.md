
# php-crud

get cruded

## Deployment

Use .env.example file to create your own .env file 

Postgres

```bash
    docker-compose up
```

Install

```bash
  composer install
```

## Sample usage

if you're mad enough to actually use it

#### /register

```json
{
    "username": "iamunique",
    "password": "buga",
    "firstName": "Joe",
    "lastName": "Chip",
    "email": "help@me.com" 
}
```

#### /login

```json
{
    "username": "",
    "password": "",
}
```

#### /update-product

```json
{
    "id": "1ee1390a-2cf3-6fa6-9b16-d363742bd60b",
    "name": "newName",
    "quantity": 21,
    "location": "inside your walls"
}
```

There is more but, idk, go figure

## Stack

[Symfony](https://symfony.com/)

[JWT](https://jwt.io/)

[Docka](https://www.docker.com/)






## Authors

- [@BsUrbk](https://www.github.com/BsUrbk)

