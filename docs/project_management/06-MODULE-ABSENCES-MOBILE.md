# 📅 Module Absences & Congés Mobile - BaoProd Workforce Suite

## 📋 Fonctionnalités Absences & Congés sur Mobile

**Date** : 3 Janvier 2025  
**Objectif** : Module complet d'absences et congés sur l'application mobile

---

## 🎯 Fonctionnalités Principales

### **👤 Candidat/Intérimaire (Mobile)**

#### **1. Demande de Congés** 📝
- **Formulaire de demande** : Type, dates, motif
- **Types de congés** : Payés, maladie, personnel, formation
- **Justificatifs** : Upload de documents (certificats médicaux)
- **Horodatage** : Envoi automatique à l'employeur et au client
- **Suivi** : Statut de la demande en temps réel

#### **2. Justification d'Absences** 📄
- **Absences non programmées** : Maladie, urgence
- **Justificatifs obligatoires** : Certificats médicaux, attestations
- **Upload de documents** : Photo/scan des justificatifs
- **Notifications automatiques** : Envoi à employeur et client
- **Traçabilité** : Horodatage et validation

#### **3. Consultation des Soldes** 📊
- **Soldes de congés** : Restants, pris, à venir
- **Historique** : Toutes les demandes et absences
- **Calendrier** : Visualisation des congés accordés
- **Statuts** : En attente, approuvé, refusé

### **🏢 Employeur/Client (Mobile)**

#### **1. Gestion des Demandes** ✅
- **Réception** : Notifications des demandes de congés
- **Validation** : Approbation/rejet avec commentaires
- **Justificatifs** : Consultation des documents uploadés
- **Historique** : Suivi de toutes les demandes

#### **2. Indication des Jours de Repos** 📅
- **Planification** : Définir les jours de repos de l'intérimaire
- **Calendrier** : Interface de planification
- **Notifications** : Envoi automatique à l'intérimaire
- **Modification** : Possibilité de modifier les plannings

#### **3. Suivi des Absences** 📈
- **Tableau de bord** : Vue d'ensemble des absences
- **Statistiques** : Taux d'absentéisme, motifs
- **Alertes** : Absences non justifiées
- **Rapports** : Export des données

### **⚙️ Administrateur JLC (Mobile)**

#### **1. Supervision Complète** 👁️
- **Toutes les demandes** : Vue globale des absences
- **Validation** : Approbation en cas de conflit
- **Audit** : Traçabilité complète des actions
- **Configuration** : Paramètres des types de congés

---

## 📱 Interface Mobile - Écrans

### **Écran Principal - Absences & Congés**
```
┌─────────────────────────────────────┐
│ 📅 Absences & Congés                │
├─────────────────────────────────────┤
│ [📝 Demander un congé]              │
│ [📄 Justifier une absence]          │
│ [📊 Mes soldes]                     │
│ [📅 Mon calendrier]                 │
│ [📋 Mes demandes]                   │
└─────────────────────────────────────┘
```

### **Écran Demande de Congé**
```
┌─────────────────────────────────────┐
│ 📝 Demande de Congé                 │
├─────────────────────────────────────┤
│ Type de congé: [Dropdown]           │
│ Date début: [DatePicker]            │
│ Date fin: [DatePicker]              │
│ Motif: [TextArea]                   │
│ Justificatif: [Upload]              │
│ [Envoyer la demande]                │
└─────────────────────────────────────┘
```

### **Écran Justification d'Absence**
```
┌─────────────────────────────────────┐
│ 📄 Justification d'Absence          │
├─────────────────────────────────────┤
│ Date d'absence: [DatePicker]        │
│ Type: [Maladie/Urgence/Personnel]   │
│ Motif: [TextArea]                   │
│ Justificatif: [Upload Photo/Scan]   │
│ [Envoyer la justification]          │
└─────────────────────────────────────┘
```

### **Écran Gestion Employeur**
```
┌─────────────────────────────────────┐
│ 🏢 Gestion Absences                 │
├─────────────────────────────────────┤
│ [📋 Demandes en attente]            │
│ [📅 Planifier jours de repos]       │
│ [📊 Statistiques]                   │
│ [📄 Justificatifs à valider]        │
└─────────────────────────────────────┘
```

---

## 🔧 Implémentation Technique

### **Modèle Absence**
```dart
class Absence {
  final int id;
  final int userId;
  final String type; // 'conges_payes', 'maladie', 'personnel', 'formation'
  final DateTime startDate;
  final DateTime endDate;
  final String reason;
  final String status; // 'pending', 'approved', 'rejected'
  final String? justification;
  final String? employerComment;
  final String? clientComment;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  // Calculs
  int get durationInDays => endDate.difference(startDate).inDays + 1;
  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';
}
```

### **Service Absences**
```dart
class AbsenceService {
  // Demande de congé
  Future<bool> requestLeave(Map<String, dynamic> data);
  
  // Justification d'absence
  Future<bool> justifyAbsence(Map<String, dynamic> data);
  
  // Upload de justificatif
  Future<String> uploadJustification(File file);
  
  // Consultation des soldes
  Future<Map<String, int>> getLeaveBalances();
  
  // Historique des demandes
  Future<List<Absence>> getAbsenceHistory();
  
  // Validation (employeur)
  Future<bool> approveAbsence(int absenceId, String comment);
  Future<bool> rejectAbsence(int absenceId, String comment);
}
```

### **Provider Absences**
```dart
class AbsenceProvider extends ChangeNotifier {
  List<Absence> _absences = [];
  Map<String, int> _leaveBalances = {};
  bool _isLoading = false;
  
  // Demande de congé
  Future<bool> requestLeave({
    required String type,
    required DateTime startDate,
    required DateTime endDate,
    required String reason,
    File? justification,
  });
  
  // Justification d'absence
  Future<bool> justifyAbsence({
    required DateTime date,
    required String type,
    required String reason,
    required File justification,
  });
  
  // Chargement des données
  Future<void> loadAbsences();
  Future<void> loadLeaveBalances();
}
```

---

## 📊 Workflow des Absences

### **1. Demande de Congé**
```
Intérimaire → Demande → Employeur → Client → Validation
     ↓              ↓         ↓         ↓
  Mobile App    Notification  Review   Final Decision
```

### **2. Justification d'Absence**
```
Intérimaire → Justification → Employeur → Client → Validation
     ↓              ↓            ↓         ↓
  Mobile App    Notification   Review   Final Decision
```

### **3. Planification des Repos**
```
Employeur → Planification → Intérimaire → Confirmation
     ↓            ↓              ↓
  Mobile App   Notification   Acknowledgment
```

---

## 🔔 Notifications et Alertes

### **Notifications Push**
- **Demande reçue** : "Nouvelle demande de congé de [Nom]"
- **Validation requise** : "Demande de congé en attente de validation"
- **Absence non justifiée** : "Absence non justifiée détectée"
- **Rappel** : "N'oubliez pas de justifier votre absence"

### **Notifications Email**
- **Demande de congé** : Détails complets avec justificatifs
- **Validation** : Statut de la demande
- **Absence** : Alerte d'absence non justifiée
- **Rapport** : Résumé mensuel des absences

### **Notifications SMS**
- **Urgent** : Absence non justifiée
- **Rappel** : Justification d'absence en retard
- **Confirmation** : Validation de demande

---

## 📅 Types de Congés et Absences

### **Types de Congés**
```dart
enum LeaveType {
  congesPayes('Congés payés', true, true),
  maladie('Maladie', true, true),
  personnel('Personnel', false, true),
  formation('Formation', true, false),
  maternite('Maternité', true, true),
  paternite('Paternité', true, true);
  
  final String label;
  final bool isPaid;
  final bool requiresJustification;
}
```

### **Types d'Absences**
```dart
enum AbsenceType {
  maladie('Maladie', true),
  urgence('Urgence', true),
  personnel('Personnel', false),
  formation('Formation', false);
  
  final String label;
  final bool requiresJustification;
}
```

---

## 🎨 Interface Utilisateur

### **Couleurs et Statuts**
```dart
class AbsenceStatus {
  static const Color pending = Colors.orange;
  static const Color approved = Colors.green;
  static const Color rejected = Colors.red;
  static const Color expired = Colors.grey;
}
```

### **Icônes**
```dart
class AbsenceIcons {
  static const IconData conges = Icons.beach_access;
  static const IconData maladie = Icons.medical_services;
  static const IconData personnel = Icons.person;
  static const IconData formation = Icons.school;
  static const IconData upload = Icons.upload_file;
  static const IconData calendar = Icons.calendar_today;
}
```

---

## 📊 Statistiques et Reporting

### **Métriques Intérimaire**
- **Congés pris** : Nombre de jours par type
- **Congés restants** : Solde disponible
- **Absences** : Nombre et motifs
- **Taux de présence** : Pourcentage de présence

### **Métriques Employeur**
- **Demandes en attente** : Nombre de validations
- **Taux d'absentéisme** : Par intérimaire
- **Coûts** : Impact financier des absences
- **Tendances** : Évolution dans le temps

### **Métriques Admin**
- **Vue globale** : Tous les intérimaires
- **Alertes** : Absences non justifiées
- **Conformité** : Respect des procédures
- **Audit** : Traçabilité complète

---

## 🔒 Sécurité et Conformité

### **Validation des Documents**
- **Format** : PDF, JPG, PNG acceptés
- **Taille** : Maximum 5MB par document
- **Sécurité** : Chiffrement des uploads
- **Audit** : Traçabilité des téléchargements

### **Conformité RGPD**
- **Consentement** : Gestion des données personnelles
- **Rétention** : Durée de conservation des justificatifs
- **Suppression** : Droit à l'oubli
- **Export** : Export des données personnelles

---

## 🚀 Intégration avec les Autres Modules

### **Timesheets**
- **Absence automatique** : Pas de pointage possible
- **Heures manquées** : Calcul automatique
- **Impact paie** : Déduction des heures non travaillées

### **Contrats**
- **Clauses** : Respect des conditions contractuelles
- **Renouvellement** : Impact sur la reconduction
- **Terminaison** : Absences répétées non justifiées

### **Paie**
- **Déduction** : Calcul automatique des absences
- **Indemnités** : Congés payés, maladie
- **Bulletins** : Mention des absences

---

## 📱 Écrans Mobiles Détaillés

### **1. Écran Principal Absences**
```dart
class AbsencesScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Absences & Congés')),
      body: Column(
        children: [
          _buildQuickActions(),
          _buildLeaveBalances(),
          _buildRecentRequests(),
          _buildCalendar(),
        ],
      ),
    );
  }
}
```

### **2. Écran Demande de Congé**
```dart
class RequestLeaveScreen extends StatefulWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Demander un congé')),
      body: Form(
        child: Column(
          children: [
            _buildLeaveTypeDropdown(),
            _buildDateRangePicker(),
            _buildReasonField(),
            _buildJustificationUpload(),
            _buildSubmitButton(),
          ],
        ),
      ),
    );
  }
}
```

### **3. Écran Justification d'Absence**
```dart
class JustifyAbsenceScreen extends StatefulWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Justifier une absence')),
      body: Form(
        child: Column(
          children: [
            _buildAbsenceDatePicker(),
            _buildAbsenceTypeDropdown(),
            _buildReasonField(),
            _buildJustificationUpload(),
            _buildSubmitButton(),
          ],
        ),
      ),
    );
  }
}
```

---

## 🎯 Prochaines Étapes

### **Immédiat**
1. **Créer les modèles** Absence et LeaveType
2. **Implémenter le service** AbsenceService
3. **Développer le provider** AbsenceProvider

### **Court Terme**
1. **Créer les écrans** de demande et justification
2. **Intégrer l'upload** de justificatifs
3. **Implémenter les notifications**

### **Moyen Terme**
1. **Écran de gestion** pour les employeurs
2. **Planification des repos** par les clients
3. **Statistiques et reporting**

---

*Module Absences & Congés Mobile - 3 Janvier 2025*  
*Fonctionnalités complètes pour tous les profils utilisateurs*