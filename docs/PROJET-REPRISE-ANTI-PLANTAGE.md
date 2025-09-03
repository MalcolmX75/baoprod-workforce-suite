# 📋 Document de Reprise de Projet - Anti-Plantage

## 🚨 Situation Actuelle (Janvier 2025)

**Projet** : Transformation d'un projet JLC Gabon vers BaoProd Workforce Suite (générique)
**Statut** : En cours de renommage et généricisation
**Dernière action** : Cursor-agent a planté, reprise par petits lots

---

## 🎯 Objectif de cette Session

1. **Renommer le projet** de JLC vers BaoProd Workforce Suite
2. **Rendre le projet générique** (pas spécifique à JLC Gabon)
3. **Travailler par petits lots** pour éviter les plantages
4. **Documenter tout** pour reprise facile

---

## 📁 Structure Actuelle du Projet

```
/Users/xdream/projets/baoprod/jlc-gabon/
├── docs/                    # Documentation complète
│   ├── 01-cahiers-des-charges/
│   ├── 02-devis-commerciaux/
│   ├── 03-offres-techniques/
│   ├── 04-legislation/
│   ├── 05-technique/
│   └── 06-conversations/
├── saas/                    # Application Laravel SaaS
├── plugin/                  # Plugin WordPress
└── fulldev/                 # Développement complet
```

---

## 🔄 Plan de Renommage par Lots

### Lot 1 : Documentation Principale ✅
- [x] Analyser la structure actuelle
- [x] Renommer docs/README.md
- [x] Renommer docs/INDEX.md
- [x] Mettre à jour docs/01-cahiers-des-charges/cahier-des-charges-technique-complet.md

### Lot 2 : Documentation Technique ✅
- [x] Mettre à jour docs/05-technique/wordpress-plugin-structure.md
- [x] Mettre à jour docs/05-technique/saas_interim_recommendations.md
- [x] Créer nouveau README principal

### Lot 3 : Code Source
- [x] Analyser structure saas/
- [x] Analyser structure plugin/
- [x] Identifier fichiers à renommer
- [x] **LOT 3A terminé** : Renommage configuration (composer.json, config/app.php, package.json)

### Lot 4 : Configuration
- [x] Mettre à jour package.json
- [x] Mettre à jour composer.json
- [x] Vérifier configurations

---

## 📝 Changements de Nommage

### Ancien → Nouveau
- **JLC Workforce Suite** → **BaoProd Workforce Suite**
- **JCL Gabon** → **BaoProd** (entreprise générique)
- **jlc-workforce-suite** → **baoprod-workforce-suite**
- **JLC\Workforce** → **BaoProd\Workforce**

### Fichiers à Renommer
- Tous les fichiers contenant "JLC" ou "jlc"
- Tous les fichiers contenant "JCL" ou "jcl"
- Namespaces PHP
- Noms de classes
- Variables et constantes

---

## 🛡️ Stratégie Anti-Plantage

### 1. Travail par Petits Lots
- Maximum 3-4 fichiers par lot
- Validation après chaque lot
- Commit fréquent

### 2. Documentation Continue
- Mise à jour de ce document à chaque étape
- Notes sur les problèmes rencontrés
- Solutions appliquées

### 3. Sauvegarde
- Commit Git avant chaque lot
- Branche de travail dédiée
- Rollback possible

---

## 📊 État d'Avancement

### ✅ Terminé
- [x] Analyse de la structure actuelle
- [x] Création du document de reprise
- [x] Lot 1 : Documentation principale
- [x] Lot 2 : Documentation technique
- [x] **Lot 3A : Renommage configuration** (composer.json, config/app.php, package.json)

### 🔄 En Cours
- [ ] Lot 3B : Renommage namespaces (App\ → BaoProd\Workforce\)

### ⏳ En Attente
- [ ] Lot 4 : Développement des modules

---

## 🚨 Points d'Attention

1. **Ne pas tout faire d'un coup** - risque de plantage
2. **Tester après chaque lot** - vérifier que tout fonctionne
3. **Documenter les changements** - pour reprise facile
4. **Sauvegarder régulièrement** - commits Git fréquents

---

## 📞 Informations de Contact

**Entreprise** : BaoProd
**Projet** : BaoProd Workforce Suite
**Développeur** : Assistant IA (Cursor)
**Date de reprise** : Janvier 2025

---

*Ce document doit être mis à jour à chaque étape pour faciliter la reprise en cas de plantage.*