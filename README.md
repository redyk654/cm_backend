# Centre Médical — API (backend)

API REST du Centre Médical : gestion des visites médicales, rapports, décisions
d'aptitude et certificats (Module 1). Backend Laravel 12, containerisé.

## Stack

- Laravel 12 (PHP 8.3)
- MySQL 8, Redis (cache + file d'attente)
- Auth : Laravel Sanctum (tokens Bearer)
- PDF : `barryvdh/laravel-dompdf`
- Architecture en couches : Controllers → Form Requests → Services → Repositories → Models

## Conventions

- **Réponses API** : enveloppe unique `{ success, message, data, errors }` (+ `meta`
  pour les listes paginées), via le trait `App\Traits\ApiResponse` dont hérite le
  contrôleur de base.
- **Erreurs de règle métier** : levées depuis les Services avec
  `throw new \DomainException('message explicite')` → rendues en HTTP 422 avec
  l'enveloppe standard (voir `bootstrap/app.php`).
- **Couches** : aucune requête Eloquent hors des Repositories ; aucune logique
  métier dans les Controllers.

## Prérequis

- Docker + Docker Compose

## Démarrage

```bash
make setup     # build, démarrage, .env, clé applicative, migrations + seeders
```

Une fois lancé :

- API : http://localhost:8092
- phpMyAdmin : http://localhost:8093

(Ports configurables via `APP_PORT` / `PMA_PORT` dans `.env`. MySQL et Redis ne
sont pas exposés sur l'hôte : accès via phpMyAdmin ou depuis le conteneur `app`.)

## Commandes

```bash
make up                       # démarre les conteneurs
make down                     # arrête les conteneurs
make shell                    # shell dans le conteneur app
make migrate                  # migrations
make fresh                    # reset base + seeders
make test                     # tests (SQLite en mémoire)
make cache-clear              # vide les caches Laravel
make pint                     # formate le code (Laravel Pint)
make pint-test                # vérifie le formatage sans modifier
make artisan c="route:list"   # commande artisan
make composer c="require ..." # commande composer
```

## Tests

Les tests tournent sur SQLite en mémoire (voir `phpunit.xml`), indépendamment de
MySQL.
