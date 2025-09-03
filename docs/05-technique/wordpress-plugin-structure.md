# Guide de structure et bonnes pratiques — Plugin WordPress complexe (BaoProd Workforce Suite)

Objectif: définir une structure solide, maintenable et testable pour un plugin WordPress complexe, intégrable avec un backend SaaS, avec de bonnes pratiques Git et CI, sans Docker.

## 1) Positionnement architectural (SaaS vs FullDev vs Plugin)

- **Recommandation**: construire un **SaaS cœur produit** (API multi-tenant) et garder le **plugin WordPress comme adaptateur** (portail, formulaires, affichage), tandis que le **FullDev** (front/app custom) réutilise la même API.
- **Avantages**:
  - Un seul modèle métier canonique (SaaS) → cohérence, scalabilité, réutilisation.
  - Le plugin reste fin (UI/embeds, auth, synchronisation) → time-to-market rapide.
  - FullDev peut viser des besoins enterprise sans fork du domaine.
- **Chemin conseillé**:
  1. MVP plugin (publication d’offres + candidatures) → validation marché.
  2. **SaaS v1** (auth, offres, candidatures, contrats, timesheets) → API stable.
  3. Élargir plugin (tableaux de bord, portail client) + FullDev front si besoin.

## 2) Arborescence de plugin (proposée)

Racine repo (simplifiée):
```
plugin/
  baoprod-workforce-suite/
    baoprod-workforce-suite.php    # Fichier principal (header WP)
    uninstall.php                  # Nettoyage à la désinstallation
    src/                           # Code PHP namespacé (PSR-4)
      Core/
        Plugin.php
        ServiceContainer.php
        Hooks.php
        Assets.php
      Admin/
        Menu.php
        Settings.php
        Screens/
      Public/
        Shortcodes.php
        Blocks/
      Domain/
        Entities/
        Repositories/
      Application/
        UseCases/
      Infrastructure/
        Persistence/
        Rest/
        Http/APIClient.php         # Client API SaaS (WP_HTTP)
        Cron/
        Security/
      Rest/
        Controllers/
          JobsController.php
          CandidatesController.php
          TimesheetsController.php
    templates/
      admin/
      public/
    languages/
      jlc-workforce-suite.pot
    resources/
      js/
      scss/
      images/
    assets/
      dist/                        # Build JS/CSS
    blocks/
      job-listing/
        block.json
        edit.tsx
        save.tsx
    composer.json
    package.json
    phpcs.xml
    phpstan.neon
    .editorconfig
    readme.txt                     # Format WP.org
    README.md                      # Doc développeur
```

## 3) Bootstrap plugin (sans Docker)

### 3.1 Fichier principal minimal
```php
<?php
/**
 * Plugin Name: BaoProd Workforce Suite
 * Description: Gestion intérim/missions (BaoProd) avec intégration SaaS.
 * Version: 0.1.0
 * Author: BAO Prod
 * Text Domain: baoprod-workforce-suite
 */

if (!defined('ABSPATH')) { exit; }

require_once __DIR__ . '/vendor/autoload.php';

add_action('plugins_loaded', function () {
    \BaoProd\Workforce\Core\Plugin::boot();
});
```

### 3.2 Classe noyau
```php
<?php
namespace BaoProd\Workforce\Core;

final class Plugin {
    public static function boot(): void {
        (new ServiceContainer())
            ->register(new Hooks())
            ->register(new Assets())
            // ->register(new ...)
        ;
    }
}
```

### 3.3 REST API (exemple)
```php
add_action('rest_api_init', function () {
  register_rest_route('baoprod/v1', '/jobs', [
    'methods'  => 'GET',
    'callback' => [\BaoProd\Workforce\Rest\Controllers\JobsController::class, 'index'],
    'permission_callback' => '__return_true',
  ]);
});
```

## 4) Données: CPT vs tables custom

- **CPT** (`job`, `mission`, `timesheet`, `contract`) pour bénéficier de l’écosystème WP (WP_Query, metabox, Gutenberg).
- **Tables custom** pour volumes/performances (ex: `wp_baoprod_timesheets`) via `dbDelta()` et **requêtes préparées** `$wpdb`.
- Recommandation: commencer CPT + `postmeta` → migrer timesheets vers table dédiée si nécessaire.

## 5) Intégration SaaS

- `Infrastructure/Http/APIClient.php` utilisant **WP HTTP API** (`wp_remote_get/post`) + gestion tokens (option chiffrée) + retries.
- Espace de noms `baoprod/v1` côté plugin pour routes internes; côté SaaS, namespace `api/v1`.
- Sync jobs (cron) pour aligner offres, candidatures, statuts; éviter la duplication de vérité.

## 6) Build front (sans Docker)

- Outils: **Node 20+** (nvm/Volta), **Vite** pour JS/TS/SCSS.
- `package.json` (extraits):
```json
{
  "scripts": {
    "dev": "vite --config resources/vite.config.ts",
    "build": "vite build --config resources/vite.config.ts",
    "lint:js": "eslint \"resources/**/*.{ts,tsx}\"",
    "typecheck": "tsc -p tsconfig.json"
  },
  "devDependencies": {
    "typescript": "^5.5.0",
    "vite": "^5.4.0",
    "eslint": "^9.8.0"
  }
}
```

## 7) PHP: Composer, qualité, tests

- `composer.json` (extraits):
```json
{
  "name": "baoprod/baoprod-workforce-suite",
  "type": "wordpress-plugin",
  "autoload": { "psr-4": { "BaoProd\\Workforce\\": "src/" } },
  "require": { "php": ">=8.1" },
  "require-dev": {
    "phpunit/phpunit": "^10",
    "squizlabs/php_codesniffer": "^3.9",
    "phpstan/phpstan": "^1.11",
    "brain/monkey": "^2.6"
  },
  "scripts": {
    "lint": "phpcs",
    "stan": "phpstan analyse src",
    "test": "phpunit"
  }
}
```
- **PHPCS**: règles **WordPress** + PSR-12 (fichier `phpcs.xml`).
- **PHPStan**: niveau 6+; chemins `src/`.
- **PHPUnit**: tests unitaires avec Brain Monkey; tests d’intégration avec WP test suite.

## 8) Sécurité & RGPD

- Toujours échapper/saniter (`esc_html`, `wp_kses_post`, `sanitize_text_field`).
- **Nonces** pour actions, **capabilities** fines (`manage_jlc`, `view_timesheets`).
- Requêtes `$wpdb` préparées, pas d’input non filtré.
- Données sensibles: stocker tokens chiffrés, rotation, audit trail minimal.
- i18n: `load_plugin_textdomain`, fichiers `.pot` dans `languages/`.

## 9) Git — bonnes pratiques (sans Docker)

- **Stratégie**: trunk-based.
  - Branches: `feat/*`, `fix/*`, `chore/*`, `docs/*`.
  - Conventions de commit: **Conventional Commits** (`feat:`, `fix:`, `refactor:`...).
  - PR obligatoire, revues, checks CI verts.
- **Versioning**: SemVer, tags `v0.1.0`.
- **Changelog**: généré (ex: `changeset` ou `git-cliff`).
- **.gitignore** (extraits):
```
/vendor/
/node_modules/
/assets/dist/
.DS_Store
*.log
.idea/
```
- **.gitattributes**: normaliser fins de ligne (`* text=auto eol=lf`).

## 10) CI GitHub Actions (sans Docker)

Workflow minimal (PHP + Node):
```yaml
name: plugin-ci
on: [push, pull_request]
jobs:
  php:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install --prefer-dist --no-progress
      - run: composer lint && composer stan && composer test
  js:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: npm ci
      - run: npm run build
```

## 11) Packaging & release

- Script zip (ex: `scripts/build.sh`) qui copie uniquement: `*.php`, `src/`, `assets/dist/`, `templates/`, `languages/`, `readme.txt`, `composer.*`, en excluant dev.
- Tag Git `vX.Y.Z`, créer release GitHub, joindre ZIP.
- `readme.txt` au format WP.org (stable tag, tested up to, short description, FAQ).

## 12) Dév local rapide (sans Docker)

- Prérequis: `php@8.2`, `mysql@8` via Homebrew, `wp-cli`, `composer`, `node`.
- Exemple d’installation locale:
```bash
brew install php mysql composer node
brew services start mysql
# Installer WP dans ./wp-dev
mkdir -p wp-dev && cd wp-dev
wp core download --locale=fr_FR
wp config create --dbname=wpdev --dbuser=root --dbpass='' --dbhost=127.0.0.1 --skip-check
wp db create
wp core install --url=http://localhost:8080 --title="BaoProd Dev" --admin_user=admin --admin_password=admin --admin_email=dev@example.com
wp server --port=8080 &
```
- Symlink du plugin: `wp-content/plugins/baoprod-workforce-suite -> ../../plugin/baoprod-workforce-suite`.

## 13) Checklist par lot (qualité)

- Ticket/issue clair avec critères d’acceptation.
- Branche dédiée + commits conventionnels.
- Tests unitaires/integ mis à jour et verts.
- PHPCS/Stan/ESLint OK.
- Revue par pair + sécurité (nonce/caps/escapes) validée.
- Build assets + smoke test local.
- PR mergée, tag si nécessaire, notes de version.

---

Besoin: je peux générer automatiquement le squelette dans `plugin/baoprod-workforce-suite/` (fichiers + `composer.json`/`package.json`), ou créer la CI et les fichiers de config recommandés. Indique-moi si je déploie la structure initiale maintenant.
