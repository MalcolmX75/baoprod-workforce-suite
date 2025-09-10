import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';

class AppLocalizations {
  AppLocalizations(this.locale);

  final Locale locale;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const _localizedValues = <String, Map<String, String>>{
    'en': {
      // Common
      'cancel': 'Cancel',
      'save': 'Save',
      'delete': 'Delete',
      'edit': 'Edit',
      'close': 'Close',
      'loading': 'Loading...',
      'error': 'Error',
      'success': 'Success',
      'yes': 'Yes',
      'no': 'No',
      'ok': 'OK',
      'confirm': 'Confirm',
      'retry': 'Retry',
      'refresh': 'Refresh',
      'send': 'Send',
      'download': 'Download',
      'upload': 'Upload',
      
      // Navigation
      'home': 'Home',
      'timesheets': 'Timesheets',
      'profile': 'Profile',
      'settings': 'Settings',
      'notifications': 'Notifications',
      'chat': 'Chat',
      'contracts': 'Contracts',
      'jobs': 'Jobs',
      
      // Dashboard
      'hello': 'Hello',
      'good_morning': 'Good morning',
      'good_afternoon': 'Good afternoon',
      'good_evening': 'Good evening',
      'quick_actions': 'Quick Actions',
      'clock_in_out': 'Clock In/Out',
      'clock_in_out_subtitle': 'Time tracking',
      'timesheets_subtitle': 'Time sheets',
      'jobs_subtitle': 'Available offers',
      'contracts_subtitle': 'My contracts',
      'chat_hr_subtitle': 'Contact your HR',
      'notifications_subtitle': 'View all',
      'statistics': 'Statistics',
      'recent_activities': 'Recent Activities',
      'news_promotions': 'News & Promotions',
      
      // Clock in/out
      'clock_in': 'Clock In',
      'clock_out': 'Clock Out',
      'clocked_in': 'Clocked In',
      'clocked_out': 'Clocked Out',
      'working_time_remaining': 'Working time remaining',
      'worked_today': 'Worked today',
      'break_time': 'Break time',
      
      // Jobs
      'job_search': 'Job Search',
      'apply_now': 'Apply Now',
      'applied': 'Applied',
      'favorite': 'Favorite',
      'salary_to_be_discussed': 'Salary to be discussed',
      'full_time': 'Full-time',
      'part_time': 'Part-time',
      'contract': 'Contract',
      'temporary': 'Temporary',
      'remote': 'Remote',
      'on_site': 'On-site',
      'hybrid': 'Hybrid',
      
      // Contracts
      'active_contracts': 'Active Contracts',
      'contract_history': 'Contract History',
      'document_requests': 'Document Requests',
      'ready_documents': 'Ready Documents',
      'request_copy': 'Request Copy',
      'resign': 'Resign',
      'electronic_signature': 'Electronic Signature',
      'physical_signature': 'Physical Signature',
      
      // Chat
      'chat_hr': 'HR Chat',
      'online': 'Online',
      'typing': 'Typing...',
      'mark_all_read': 'Mark all as read',
      'type_message': 'Type your message...',
      'message_sent': 'Message sent',
      'message_copied': 'Message copied',
      'message_info': 'Message Info',
      'sent_by': 'Sent by',
      'date': 'Date',
      'time': 'Time',
      'type': 'Type',
      'read': 'Read',
      
      // Profile
      'personal_info': 'Personal Information',
      'work_info': 'Work Information',
      'documents': 'Documents',
      'change_password': 'Change Password',
      'logout': 'Logout',
      
      // Settings
      'language': 'Language',
      'french': 'French',
      'english': 'English',
      'theme': 'Theme',
      'light_theme': 'Light',
      'dark_theme': 'Dark',
      'system_theme': 'System',
      'notifications_settings': 'Notifications',
      'privacy': 'Privacy',
      'about': 'About',
      
      // Document requests
      'new_document_request': 'New Document Request',
      'document_type': 'Document Type',
      'request_title': 'Request Title',
      'description_optional': 'Description (optional)',
      'signature_required': 'Signature Required',
      'no_signature': 'No signature',
      'urgent_request': 'Urgent Request',
      'priority_processing': 'Priority processing (shorter deadline)',
      'send_request': 'Send Request',
      'contract_work': 'Work Contract',
      'payslip': 'Payslip',
      'work_certificate': 'Work Certificate',
      'recommendation_letter': 'Recommendation Letter',
      'other_document': 'Other Document',
      
      // Notifications
      'unread_notifications': 'Unread',
      'all_notifications': 'All',
      'work_notifications': 'Work',
      'system_notifications': 'System',
      'no_notifications': 'No notifications',
      'mark_as_read': 'Mark as read',
      'notification_deleted': 'Notification deleted',
      
      // Errors
      'network_error': 'Network error',
      'server_error': 'Server error',
      'invalid_credentials': 'Invalid credentials',
      'session_expired': 'Session expired',
      'permission_denied': 'Permission denied',
      'file_not_found': 'File not found',
      'unknown_error': 'Unknown error',
      
      // Onboarding
      'welcome_title': 'Welcome to BaoProd Workforce',
      'welcome_subtitle': 'Your complete work management solution',
      'features_title': 'Powerful Features',
      'features_subtitle': 'Track time, manage contracts, communicate with HR',
      'ready_title': 'You\'re All Set!',
      'ready_subtitle': 'Start managing your work life efficiently',
      'get_started': 'Get Started',
      'next': 'Next',
      'skip': 'Skip',
    },
    'fr': {
      // Common
      'cancel': 'Annuler',
      'save': 'Sauvegarder',
      'delete': 'Supprimer',
      'edit': 'Modifier',
      'close': 'Fermer',
      'loading': 'Chargement...',
      'error': 'Erreur',
      'success': 'Succès',
      'yes': 'Oui',
      'no': 'Non',
      'ok': 'OK',
      'confirm': 'Confirmer',
      'retry': 'Réessayer',
      'refresh': 'Actualiser',
      'send': 'Envoyer',
      'download': 'Télécharger',
      'upload': 'Téléverser',
      
      // Navigation
      'home': 'Accueil',
      'timesheets': 'Feuilles de temps',
      'profile': 'Profil',
      'settings': 'Paramètres',
      'notifications': 'Notifications',
      'chat': 'Chat',
      'contracts': 'Contrats',
      'jobs': 'Emplois',
      
      // Dashboard
      'hello': 'Bonjour',
      'good_morning': 'Bonjour',
      'good_afternoon': 'Bon après-midi',
      'good_evening': 'Bonsoir',
      'quick_actions': 'Actions rapides',
      'clock_in_out': 'Pointer',
      'clock_in_out_subtitle': 'Entrée/Sortie',
      'timesheets_subtitle': 'Feuilles de temps',
      'jobs_subtitle': 'Offres disponibles',
      'contracts_subtitle': 'Mes contrats',
      'chat_hr_subtitle': 'Contactez votre RH',
      'notifications_subtitle': 'Voir toutes',
      'statistics': 'Statistiques',
      'recent_activities': 'Activités récentes',
      'news_promotions': 'Actualités & Promotions',
      
      // Clock in/out
      'clock_in': 'Pointer Entrée',
      'clock_out': 'Pointer Sortie',
      'clocked_in': 'Pointé Entrée',
      'clocked_out': 'Pointé Sortie',
      'working_time_remaining': 'Temps de travail restant',
      'worked_today': 'Travaillé aujourd\'hui',
      'break_time': 'Temps de pause',
      
      // Jobs
      'job_search': 'Recherche d\'emploi',
      'apply_now': 'Postuler',
      'applied': 'Postulé',
      'favorite': 'Favori',
      'salary_to_be_discussed': 'Selon profil',
      'full_time': 'Temps plein',
      'part_time': 'Temps partiel',
      'contract': 'Contrat',
      'temporary': 'Temporaire',
      'remote': 'Télétravail',
      'on_site': 'Sur site',
      'hybrid': 'Hybride',
      
      // Contracts
      'active_contracts': 'Contrats actifs',
      'contract_history': 'Historique des contrats',
      'document_requests': 'Demandes de documents',
      'ready_documents': 'Documents prêts',
      'request_copy': 'Demander copie',
      'resign': 'Démissionner',
      'electronic_signature': 'Signature électronique',
      'physical_signature': 'Signature physique',
      
      // Chat
      'chat_hr': 'Chat RH',
      'online': 'En ligne',
      'typing': 'En train d\'écrire...',
      'mark_all_read': 'Marquer tout comme lu',
      'type_message': 'Tapez votre message...',
      'message_sent': 'Message envoyé',
      'message_copied': 'Message copié',
      'message_info': 'Informations du message',
      'sent_by': 'Envoyé par',
      'date': 'Date',
      'time': 'Heure',
      'type': 'Type',
      'read': 'Lu',
      
      // Profile
      'personal_info': 'Informations personnelles',
      'work_info': 'Informations professionnelles',
      'documents': 'Documents',
      'change_password': 'Changer le mot de passe',
      'logout': 'Déconnexion',
      
      // Settings
      'language': 'Langue',
      'french': 'Français',
      'english': 'Anglais',
      'theme': 'Thème',
      'light_theme': 'Clair',
      'dark_theme': 'Sombre',
      'system_theme': 'Système',
      'notifications_settings': 'Notifications',
      'privacy': 'Confidentialité',
      'about': 'À propos',
      
      // Document requests
      'new_document_request': 'Nouvelle demande de document',
      'document_type': 'Type de document',
      'request_title': 'Titre de la demande',
      'description_optional': 'Description (optionnel)',
      'signature_required': 'Signature requise',
      'no_signature': 'Aucune signature',
      'urgent_request': 'Demande urgente',
      'priority_processing': 'Traitement prioritaire (délai raccourci)',
      'send_request': 'Envoyer la demande',
      'contract_work': 'Contrat de travail',
      'payslip': 'Fiche de paie',
      'work_certificate': 'Attestation de travail',
      'recommendation_letter': 'Lettre de recommandation',
      'other_document': 'Autre document',
      
      // Notifications
      'unread_notifications': 'Non lues',
      'all_notifications': 'Toutes',
      'work_notifications': 'Travail',
      'system_notifications': 'Système',
      'no_notifications': 'Aucune notification',
      'mark_as_read': 'Marquer comme lu',
      'notification_deleted': 'Notification supprimée',
      
      // Errors
      'network_error': 'Erreur réseau',
      'server_error': 'Erreur serveur',
      'invalid_credentials': 'Identifiants invalides',
      'session_expired': 'Session expirée',
      'permission_denied': 'Permission refusée',
      'file_not_found': 'Fichier non trouvé',
      'unknown_error': 'Erreur inconnue',
      
      // Onboarding
      'welcome_title': 'Bienvenue sur BaoProd Workforce',
      'welcome_subtitle': 'Votre solution complète de gestion du travail',
      'features_title': 'Fonctionnalités puissantes',
      'features_subtitle': 'Suivez votre temps, gérez vos contrats, communiquez avec les RH',
      'ready_title': 'Vous êtes prêt !',
      'ready_subtitle': 'Commencez à gérer efficacement votre vie professionnelle',
      'get_started': 'Commencer',
      'next': 'Suivant',
      'skip': 'Passer',
    },
  };

  static List<Locale> get supportedLocales {
    return _localizedValues.keys.map((languageTag) => Locale(languageTag)).toList();
  }

  String get(String key) {
    return _localizedValues[locale.languageCode]?[key] ?? key;
  }

  // Getters for common translations
  String get cancel => get('cancel');
  String get save => get('save');
  String get delete => get('delete');
  String get edit => get('edit');
  String get close => get('close');
  String get loading => get('loading');
  String get error => get('error');
  String get success => get('success');
  String get yes => get('yes');
  String get no => get('no');
  String get ok => get('ok');
  String get confirm => get('confirm');
  String get retry => get('retry');
  String get refresh => get('refresh');
  String get send => get('send');
  String get download => get('download');
  String get upload => get('upload');

  // Navigation
  String get home => get('home');
  String get timesheets => get('timesheets');
  String get profile => get('profile');
  String get settings => get('settings');
  String get notifications => get('notifications');
  String get chat => get('chat');
  String get contracts => get('contracts');
  String get jobs => get('jobs');

  // Dashboard
  String get hello => get('hello');
  String get goodMorning => get('good_morning');
  String get goodAfternoon => get('good_afternoon');
  String get goodEvening => get('good_evening');
  String get quickActions => get('quick_actions');
  String get clockInOut => get('clock_in_out');
  String get clockInOutSubtitle => get('clock_in_out_subtitle');
  String get timesheetsSubtitle => get('timesheets_subtitle');
  String get jobsSubtitle => get('jobs_subtitle');
  String get contractsSubtitle => get('contracts_subtitle');
  String get chatHrSubtitle => get('chat_hr_subtitle');
  String get notificationsSubtitle => get('notifications_subtitle');
  String get statistics => get('statistics');
  String get recentActivities => get('recent_activities');
  String get newsPromotions => get('news_promotions');

  // Settings
  String get language => get('language');
  String get french => get('french');
  String get english => get('english');
  String get theme => get('theme');
  String get lightTheme => get('light_theme');
  String get darkTheme => get('dark_theme');
  String get systemTheme => get('system_theme');
  String get notificationsSettings => get('notifications_settings');
  String get privacy => get('privacy');
  String get about => get('about');
}

class AppLocalizationsDelegate extends LocalizationsDelegate<AppLocalizations> {
  const AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => ['en', 'fr'].contains(locale.languageCode);

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(AppLocalizations(locale));
  }

  @override
  bool shouldReload(AppLocalizationsDelegate old) => false;
}