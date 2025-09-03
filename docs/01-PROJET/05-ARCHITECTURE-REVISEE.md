# 🏗️ Architecture Révisée - BaoProd Workforce Suite

## 📊 Analyse des Exigences Révisées

**Date** : 3 Janvier 2025  
**Révision** : Architecture unifiée avec portail unique et recherche d'offres mobile

---

## 🎯 Nouvelles Exigences

### **1. Portail Unique avec Accès Conditionnel**
- **Un seul portail web** avec navigation selon le profil
- **Accès conditionnel** aux fonctionnalités selon le rôle
- **Interface adaptative** selon les permissions

### **2. Recherche d'Offres dans l'App Mobile**
- **Recherche d'emplois** même en poste
- **Candidatures** depuis l'application mobile
- **Notifications** pour nouvelles offres

### **3. Respect de Tous les Modules Full Dev**
- **7 modules complets** selon le cahier des charges
- **Fonctionnalités avancées** pour tous les profils
- **Intégration complète** mobile ↔ web

---

## 🏗️ Architecture Révisée

### **Solution : 1 App Mobile + 1 Portail Web Unifié**

```
┌─────────────────────────────────────────────────────────────┐
│                    BaoProd Workforce Suite                  │
├─────────────────────────────────────────────────────────────┤
│  📱 Mobile Flutter          │  🌐 Portail Web Unifié        │
│  - Pointage géolocalisé     │  - Accès conditionnel         │
│  - Recherche d'offres       │  - Navigation par profil      │
│  - Candidatures             │  - Toutes les fonctionnalités │
│  - Timesheets               │  - Signature contrats         │
│  - Notifications            │  - Gestion complète           │
├─────────────────────────────────────────────────────────────┤
│                    🔗 API REST Laravel                      │
│  - Authentification JWT                                     │
│  - Multi-tenant                                             │
│  - 58+ endpoints                                            │
├─────────────────────────────────────────────────────────────┤
│                    🗄️ Base de Données                       │
│  - PostgreSQL (Production)                                  │
│  - Isolation par tenant                                     │
│  - Configuration CEMAC                                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 📱 Application Mobile (Flutter) - Fonctionnalités Étendues

### **👤 Candidat/Intérimaire**
- ✅ **Pointage géolocalisé** (entrée/sortie)
- ✅ **Consultation timesheets** (historique, détails)
- ✅ **Notifications push** (rappels, alertes)
- ✅ **Consultation contrats** (lecture seule)
- ✅ **Profil utilisateur** (modification limitée)
- 🔄 **Recherche d'emplois** (filtres, géolocalisation)
- 🔄 **Candidatures** (application en un clic)
- 🔄 **Suivi candidatures** (statuts, historique)
- 🔄 **Upload CV** (gestion des documents)
- 🔄 **Messagerie** (communication avec employeurs)
- 🔄 **Demande de congés** (formulaire complet)
- 🔄 **Justification d'absences** (upload justificatifs)
- 🔄 **Consultation des soldes** (congés restants)

### **🏢 Employeur/Client** (accès mobile limité)
- 🔄 **Consultation offres** (lecture seule)
- 🔄 **Notifications** (nouvelles candidatures)
- 🔄 **Consultation timesheets** (validation rapide)
- 🔄 **Validation des congés** (approbation/rejet)
- 🔄 **Planification des repos** (jours de repos intérimaire)
- 🔄 **Consultation des justificatifs** (absences)

### **⚙️ Administrateur JLC** (accès complet)
- ✅ **Toutes les fonctionnalités candidat**
- 🔄 **Validation timesheets** (approbation/rejet)
- 🔄 **Supervision pointages** (vue d'ensemble)
- 🔄 **Gestion urgente** (validation rapide)
- 🔄 **Notifications critiques** (alertes système)
- 🔄 **Supervision des absences** (vue globale)
- 🔄 **Validation des justificatifs** (audit)

---

## 🌐 Portail Web Unifié - Architecture

### **URL Unique** : `https://workforce.baoprod.com`

#### **Navigation Conditionnelle**
```php
// Exemple de navigation conditionnelle
class NavigationService {
    public function getMenuItems($user) {
        $items = [];
        
        // Items communs à tous
        $items[] = ['name' => 'Dashboard', 'route' => 'dashboard'];
        
        if ($user->isCandidate()) {
            $items[] = ['name' => 'Recherche Emplois', 'route' => 'jobs.search'];
            $items[] = ['name' => 'Mes Candidatures', 'route' => 'applications'];
            $items[] = ['name' => 'Mon CV', 'route' => 'profile.cv'];
        }
        
        if ($user->isEmployer() && $user->hasActiveContract()) {
            $items[] = ['name' => 'Mes Offres', 'route' => 'jobs.manage'];
            $items[] = ['name' => 'Candidatures', 'route' => 'applications.manage'];
            $items[] = ['name' => 'Timesheets', 'route' => 'timesheets.manage'];
            $items[] = ['name' => 'Facturation', 'route' => 'billing'];
        }
        
        if ($user->isAdmin()) {
            $items[] = ['name' => 'Administration', 'route' => 'admin'];
            $items[] = ['name' => 'Reporting', 'route' => 'reports'];
            $items[] = ['name' => 'Configuration', 'route' => 'settings'];
        }
        
        return $items;
    }
}
```

---

## 📋 Modules Complets - Respect du Full Dev

### **Module 1 : Contrats & Signature** ✅
- **Génération automatique** de contrats
- **Templates configurables** par pays CEMAC
- **Signature hybride** : Électronique + Papier
- **Workflow d'approbation** multi-niveaux
- **Archivage sécurisé** avec versioning

### **Module 2 : Timesheets & Pointage** ✅
- **Pointage mobile** avec géolocalisation
- **Calcul automatique** des heures sup/majorées
- **Validation hiérarchique** configurable
- **Export pour paie**
- **Gestion des astreintes** et gardes

### **Module 3 : Paie & Facturation** ✅
- **Calcul automatique** des salaires
- **Gestion des charges** sociales par pays
- **Génération des bulletins** de paie
- **Facturation clients** entreprises
- **Intégration comptabilité**

### **Module 4 : Absences & Congés** ✅
- **Demande en ligne** de congés (mobile + web)
- **Justification d'absences** avec upload de documents
- **Horodatage automatique** envoyé à employeur et client
- **Planification des jours de repos** par le client
- **Workflow d'approbation** configurable
- **Gestion des soldes** automatique
- **Calendrier intégré** avec conflits
- **Notifications** push, email et SMS

### **Module 5 : Reporting & BI** 🔄
- **Tableaux de bord** avancés
- **KPIs métier** (taux de remplissage, turnover)
- **Exports Excel/PDF**
- **Rapports réglementaires**
- **Analytics prédictives**

### **Module 6 : Messagerie & Notifications** 🔄
- **Chat interne** entre parties
- **Notifications push** (mobile)
- **SMS automatiques**
- **Emails personnalisés** avec templates
- **Workflow d'alertes** configurables

### **Module 7 : Compliance & Audit** 🔄
- **Audit trail** complet et inviolable
- **Conformité RGPD**
- **Gestion des consentements**
- **Traçabilité** de toutes les actions
- **Rapports d'audit** automatiques

---

## 🎯 Fonctionnalités par Profil

### **👤 Candidat/Intérimaire**

#### **Mobile**
- Pointage géolocalisé
- Consultation timesheets
- Recherche d'emplois
- Candidatures
- Upload CV
- Notifications
- Messagerie

#### **Portail Web**
- Recherche avancée d'emplois
- Gestion complète du profil
- Suivi des candidatures
- Consultation des contrats
- Gestion des documents
- Historique complet

### **🏢 Employeur/Client** (après contrat actif)

#### **Mobile** (limité)
- Consultation des offres
- Notifications candidatures
- Validation timesheets urgente

#### **Portail Web** (complet)
- Publication et gestion des offres
- Gestion des candidatures
- Suivi des timesheets
- Gestion des contrats
- Facturation
- Reporting entreprise
- Configuration

### **⚙️ Administrateur JLC**

#### **Mobile**
- Toutes les fonctionnalités candidat
- Validation timesheets
- Supervision
- Notifications critiques

#### **Portail Web**
- Gestion complète des utilisateurs
- Configuration système
- Reporting global
- Support technique
- Audit et compliance

---

## 📄 Signature des Contrats - Solutions

### **Option 1 : Signature Électronique (Priorité)**
- **Portail Web** : Signature avec certificat numérique
- **Mobile** : Signature tactile + validation biométrique
- **Légalité** : Conforme aux réglementations CEMAC
- **Traçabilité** : Horodatage, IP, certificat

### **Option 2 : Signature Papier (Fallback)**
- **Génération PDF** : Contrat personnalisé
- **Impression** : Portail web ou mobile
- **Scan/Upload** : Retour du contrat signé
- **Validation** : OCR pour vérification

### **Option 3 : Signature Mixte**
- **Électronique** : Pour les contrats standards
- **Papier** : Pour les contrats complexes/importants
- **Choix** : Selon préférence client ou réglementation

---

## 🔄 Flux d'Intégration Mobile ↔ Web

### **Synchronisation des Données**
```php
// Synchronisation temps réel
class SyncService {
    public function syncUserData($userId) {
        // Synchroniser profil, CV, candidatures
        // Entre mobile et portail web
    }
    
    public function syncTimesheets($userId) {
        // Synchroniser pointages et timesheets
        // Entre mobile et portail web
    }
    
    public function syncApplications($userId) {
        // Synchroniser candidatures
        // Entre mobile et portail web
    }
}
```

### **Notifications Unifiées**
- **Push Mobile** : Rappels, alertes, nouvelles offres
- **Email** : Confirmations, rapports, communications
- **SMS** : Alertes critiques, codes de validation
- **In-App** : Messages, notifications internes

---

## 📊 Matrice des Fonctionnalités Révisée

| Fonctionnalité | Mobile Candidat | Mobile Employeur | Mobile Admin | Portail Web |
|----------------|-----------------|------------------|--------------|-------------|
| **Pointage** | ✅ Principal | ❌ | 🔄 Supervision | 🔄 Consultation |
| **Recherche emplois** | ✅ Complète | 🔄 Consultation | 🔄 Consultation | ✅ Avancée |
| **Candidatures** | ✅ Complète | 🔄 Consultation | 🔄 Gestion | ✅ Complète |
| **Gestion offres** | ❌ | 🔄 Consultation | ✅ Gestion | ✅ Complète |
| **Timesheets** | ✅ Consultation | 🔄 Validation | ✅ Gestion | ✅ Gestion |
| **Contrats** | 🔄 Consultation | ✅ Gestion | ✅ Gestion | ✅ Complète |
| **Signature** | 🔄 Basique | ✅ Complète | ✅ Complète | ✅ Complète |
| **Absences & Congés** | ✅ Complète | ✅ Gestion | ✅ Supervision | ✅ Complète |
| **Facturation** | ❌ | ✅ Consultation | ✅ Gestion | ✅ Gestion |
| **Reporting** | 🔄 Personnel | ✅ Entreprise | ✅ Global | ✅ Complet |
| **Configuration** | 🔄 Profil | ✅ Entreprise | ✅ Système | ✅ Complet |

**Légende** : ✅ Principal | 🔄 Secondaire | ❌ Non disponible

---

## 🚀 Avantages de l'Architecture Révisée

### **1. Simplicité**
- **Un seul portail** à maintenir
- **Navigation intuitive** selon le profil
- **Interface cohérente** entre mobile et web

### **2. Flexibilité**
- **Accès conditionnel** aux fonctionnalités
- **Recherche d'emplois** même en poste
- **Signature hybride** selon préférence

### **3. Complétude**
- **Tous les modules** du full dev respectés
- **Fonctionnalités avancées** pour tous les profils
- **Intégration complète** mobile ↔ web

### **4. Évolutivité**
- **Architecture modulaire** et extensible
- **Ajout facile** de nouvelles fonctionnalités
- **Scalabilité** pour de nouveaux pays

---

## 📅 Planning de Développement Révisé

### **Semaine 1 : Portail Unifié**
- Structure et authentification
- Navigation conditionnelle
- Dashboard adaptatif

### **Semaine 2 : Modules Candidat**
- Recherche d'emplois avancée
- Gestion des candidatures
- Upload et gestion CV

### **Semaine 3 : Modules Employeur**
- Publication et gestion des offres
- Gestion des candidatures
- Suivi des timesheets

### **Semaine 4 : Modules Admin + Signature**
- Administration complète
- Signature électronique
- Intégration mobile ↔ web

---

## 🎯 Conclusion

L'architecture révisée respecte toutes vos exigences :

✅ **Portail unique** avec accès conditionnel  
✅ **Recherche d'offres** dans l'app mobile  
✅ **Tous les modules** du full dev respectés  
✅ **Signature hybride** électronique + papier  
✅ **Intégration complète** mobile ↔ web  

Cette solution offre la **simplicité** d'un portail unique tout en conservant la **richesse fonctionnelle** de tous les modules prévus dans le full dev.

---

*Architecture révisée le 3 Janvier 2025*  
*Solution : 1 App Mobile + 1 Portail Web Unifié*  
*Respect complet des 7 modules du full dev*