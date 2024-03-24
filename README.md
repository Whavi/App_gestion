----------------
# Application de gestion d'inventaire (Symfony)

## Base de donnée :


Pour la base de donnée rendez-vous sur [DB](https://dbdiagram.io/d/65ae31cdac844320ae6d126d) !




## Installation pré-requis :

Pour les installations importante vous avez besoin de docker, composer, le framework de Symfony ainsi que npm si vous voulez utiliser le bundle symfony/encore

Lorsque vous avez un problème, n'hésitez pas à regarder dans les messages d'erreur dans la console. Généralement, cela provient sûrement des lignes en commentaire dans le php.ini qu'il faut décommenter.

dans le public il y a un dossier `sign` a mettre pour les signatures electronique en local

----------------

## Installation  :

Commencer a faire ces commandes dans l'ordre :

1)
```
npm install
```
2)
```
composer install
```
3)
```
docker compose build
```
4)
```
symfony server:ca:install
```
5)
```
docker compose up -d
```
6)
S'il y a une erreur avec le HTTPS installez :
```
composer require amphp/http-client:^4.2.1
```
7)
```
symfony serve:start ou php bin/console symfony serve:start
```
8) Decommenter l'une de ces 3 variables selon l'environnement utilisé 
```
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
```
9)
```
symfony d:m:m ou php bin/console d:m:m
```

(d:m:m -> doctrine:make:migration)
10)
```
symfony d:f:l ou php bin/console d:f:l 
```
(d:f:l -> doctrine:fixture:load)

----------------




Et c'est fini, votre application est enfin en marche. Vous pouvez vous connecter grâce à l'email :
```
username: stage.it@secours-islamique.org
password: password
```

Ainsi, si vous voulez voir la partie utilisateur, il faudra vous créer un compte et vous y connecter :
Cliquez sur le bouton User -> New Account



