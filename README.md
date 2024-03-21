----------------
# Application de gestion d'inventaire (Symfony)

## Base de donnée :


Pour la base de donnée rendez-vous sur [DB](https://dbdiagram.io/d/65ae31cdac844320ae6d126d) !




## Installation pré-requis :

Pour les installations importante vous avez besoin de docker, composer, le framework de Symfony ainsi que npm si vous voulez utiliser le bundle symfony/encore

----------------

## Installation  :

Commencer a faire ces commandes dans l'ordre :
```
npm install
```

```
composer install
```
```
docker compose build
```

```
symfony server:ca:install
```

```
docker compose up -d
```

S'il y a une erreur avec le HTTPS installez :
```
composer require amphp/http-client:^4.2.1
```

```
symfony serve:start ou php bin/console symfony serve:start
```

```
symfony d:m:m ou php bin/console d:m:m
```

(d:m:m -> doctrine:make:migration)

```
symfony d:f:l ou php bin/console d:f:l 
```
(d:f:l -> doctrine:fixture:load)





Et c'est fini votre application est enfin en marche vous pouvez vous connecté grace a l'email :
```
username: stage.it@secours-islamique.org
password: password
```



