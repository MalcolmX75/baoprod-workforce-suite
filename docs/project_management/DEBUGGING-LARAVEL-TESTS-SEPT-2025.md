# Rapport de Débogage des Tests Laravel - 4 Septembre 2025

Ce document résume les tentatives de débogage effectuées sur la suite de tests PHPUnit/Laravel du projet `/saas`.

## État Initial

- **Score :** 57 tests sur 79 passaient (72%).
- **Erreurs principales :** `BindingResolutionException` pour la classe `tenant` et des erreurs de contrainte de base de données (`NOT NULL`).

## Analyse et Actions Correctives

L'analyse a révélé que le problème principal était lié à l'initialisation de l'environnement de test, spécifiquement concernant le système multi-tenant et la gestion des permissions.

### Tentative 1 : Injection du Tenant

- **Action :** Injection manuelle d'une instance de `Tenant` dans le conteneur de services au sein de la méthode `setUp()` des tests.
- **Résultat :** A résolu l'erreur `BindingResolutionException` mais a révélé des erreurs d'assertions (JSON non conforme) et des erreurs de permissions (403/404).

### Tentative 2 : Correction des Assertions et des Contrôleurs

- **Action :** Correction des structures JSON attendues dans les tests et correction de bugs dans les contrôleurs (ex: variable `$request` non définie).
- **Résultat :** A fait passer plus de tests, mais les erreurs de permissions 403 (Forbidden) et 404 (Not Found) persistaient, suggérant un problème de droits d'accès.

### Tentative 3 : Correction du système de Rôles & Permissions (Spatie)

L'analyse a montré que le projet utilise le package `spatie/laravel-permission` mais que celui-ci n'était pas correctement initialisé dans l'environnement de test.

1.  **Création d'un `RolesAndPermissionsSeeder` :** Un seeder a été créé pour définir les rôles (`admin`, `employer`, `candidate`) et les permissions (`view jobs`, `edit contracts`, etc.).
2.  **Publication des Migrations :** Les tentatives de publication des migrations de Spatie via `vendor:publish` ont échoué. Le fichier de migration a donc été copié manuellement.
3.  **Correction des `Guards` :** Les migrations ont ensuite échoué à cause d'un conflit de `guard` d'authentification (`web` vs `api`). Plusieurs tentatives pour forcer l'utilisation du guard `api` dans les seeders et dans `config/auth.php` ont échoué, révélant un problème de configuration plus profond.

## Conclusion et Prochaine Étape Recommandée

Le débogage est bloqué par une **incohérence fondamentale dans la configuration de l'environnement de test de Laravel**, spécifiquement sur la manière dont le `guard` d'authentification est résolu lors de l'exécution des seeders via `migrate:fresh`.

**Recommandation :** Laisser temporairement les tests en l'état et pivoter sur une autre partie du projet. Une intervention manuelle sur la configuration Laravel sera nécessaire pour résoudre ce problème de fond.
