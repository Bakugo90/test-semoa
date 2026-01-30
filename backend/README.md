# Backend - Système de tickets

## Prérequis

- PHP 8.2+
- Composer
- PostgreSQL
- Extensions PHP : pdo_pgsql, sodium

## Installation

```bash
cd backend
composer install
```

Configurer la base de données dans `.env` :
```
DATABASE_URL="postgresql://postgres:root@127.0.0.1:5432/test-semoa?serverVersion=16&charset=utf8"
```

Générer les clés JWT :
```bash
php bin/console lexik:jwt:generate-keypair
```

Créer les tables :
```bash
php bin/console doctrine:schema:update --force
```

## Lancement

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

API disponible sur `http://localhost:8000/api`

## Tests

```bash
php bin/phpunit
```

Avant les tests, synchroniser le schéma en environnement test :
```bash
php bin/console doctrine:schema:update --force --env=test
```

## Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Valider le schéma
php bin/console doctrine:schema:validate

# Créer une migration
php bin/console make:migration

# Lancer les migrations
php bin/console doctrine:migrations:migrate
```

## Architecture

J'ai opté pour une architecture en couches inspirée de la Clean Architecture, adaptée à la taille du projet :

**Domain (Cœur métier)** : Entités (`User`, `Ticket`) et enums (`TicketStatus`, `TicketPriority`) avec leurs règles métier. Par exemple, `TicketStatus::canTransitionTo()` encode directement les transitions autorisées (OPEN → IN_PROGRESS → DONE). Aucune dépendance externe, code testable en isolation.

**Application (Use Cases)** : Services (`UserService`, `TicketService`) qui orchestrent la logique applicative. Ils utilisent les repositories, appliquent les règles métier et coordonnent les opérations. Un service ne connaît ni HTTP ni base de données directement.

**Infrastructure (Détails techniques)** : Repositories qui encapsulent Doctrine. `TicketRepository::findByUser()` gère filtres, tri et pagination. Si demain on change d'ORM, seule cette couche change.

**Présentation (Controllers)** : Endpoints REST qui font uniquement du HTTP : récupérer les inputs, appeler le service, retourner JSON. Aucune logique métier. Exemple : `TicketController::create()` délègue tout à `TicketService`.

**DTOs** : Validation en entrée avec Symfony Validator, formatage en sortie. `TicketCreateDTO` valide les données avant qu'elles atteignent le service, `TicketResponseDTO` normalise la sortie.

Cette séparation permet de tester chaque couche indépendamment et facilite l'évolution du code.

## Design Patterns

**Repository Pattern** : Abstrait l'accès aux données. Les services manipulent des objets métier, pas des requêtes SQL. Facilite les tests avec des repositories mockés et isole la complexité de Doctrine.

**Service Layer Pattern** : Centralise la logique métier réutilisable. `TicketService::canUserModifyTicket()` implémente le RBAC une seule fois, utilisé par update et delete. Évite la duplication dans les controllers.

**DTO Pattern** : Sépare les données HTTP des entités métier. `TicketCreateDTO` accepte `priority` en string et le valide, le service le convertit en enum. Protection contre les mass-assignment et validation explicite.

**Enum avec logique** : `TicketStatus` ne stocke pas que des constantes, il porte la logique de transition. C'est du Domain-Driven Design light : le comportement vit avec les données.

**Dependency Injection** : Tous les services reçoivent leurs dépendances par constructeur. Facilite les tests unitaires et respecte l'inversion de dépendance (SOLID).

## Choix techniques

**Enums PHP 8.2** : Les enums backed permettent de stocker une valeur en base tout en ayant des objets fortement typés en code. `canTransitionTo()` encode les règles métier directement dans l'enum, impossible d'oublier une validation.

**JWT stateless** : Pas de session en base, l'API scale horizontalement. Le token contient l'identité, vérifié à chaque requête. Parfait pour une architecture REST découplée.

**UUID pour les IDs** : Évite les collisions en distribué, masque le nombre d'enregistrements, permet de générer des IDs côté client si besoin. Plus sécurisé que les auto-increment.

**Validation déclarative** : Attributs Symfony Validator sur les DTOs. La validation est visible dans la définition du DTO, pas cachée dans le code. Erreurs automatiques en français via le DTO.

**Standard de réponse uniforme** : `{message, data, meta}` partout. Le frontend sait toujours où trouver les données, les erreurs, la pagination. Simplifie le code client.

**Typage strict partout** : Paramètres, returns, propriétés typés. PHP détecte les erreurs avant l'exécution. Moins de bugs, meilleure autocomplétion IDE.

**Tests d'intégration** : WebTestCase Symfony teste tout le stack HTTP → Service → Repository → BDD. Proche du comportement réel, confiance plus élevée qu'avec des tests unitaires isolés.
