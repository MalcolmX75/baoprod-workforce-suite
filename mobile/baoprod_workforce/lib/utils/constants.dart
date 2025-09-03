class AppConstants {
  // App Info
  static const String appName = 'BaoProd Workforce';
  static const String appVersion = '1.0.0';
  static const String appBuildNumber = '1';
  
  // API Configuration
  static const String baseUrl = 'https://workforce.baoprod.com/api';
  static const String apiVersion = 'v1';
  static const Duration apiTimeout = Duration(seconds: 30);
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String settingsKey = 'app_settings';
  static const String timesheetKey = 'timesheet_data';
  
  // Location Settings
  static const double defaultLatitude = 0.4162; // Libreville, Gabon
  static const double defaultLongitude = 9.4673;
  static const double locationAccuracy = 10.0; // meters
  static const Duration locationTimeout = Duration(seconds: 10);
  
  // Timesheet Settings
  static const int maxHoursPerDay = 12;
  static const int maxHoursPerWeek = 60;
  static const Duration clockInBuffer = Duration(minutes: 15);
  
  // UI Constants
  static const double defaultPadding = 16.0;
  static const double smallPadding = 8.0;
  static const double largePadding = 24.0;
  static const double borderRadius = 12.0;
  static const double buttonHeight = 48.0;
  
  // Animation Durations
  static const Duration shortAnimation = Duration(milliseconds: 200);
  static const Duration mediumAnimation = Duration(milliseconds: 300);
  static const Duration longAnimation = Duration(milliseconds: 500);
  
  // Error Messages
  static const String networkError = 'Erreur de connexion. Vérifiez votre connexion internet.';
  static const String serverError = 'Erreur du serveur. Veuillez réessayer plus tard.';
  static const String authError = 'Erreur d\'authentification. Veuillez vous reconnecter.';
  static const String locationError = 'Impossible d\'obtenir votre position.';
  static const String permissionError = 'Permission refusée.';
  
  // Success Messages
  static const String loginSuccess = 'Connexion réussie';
  static const String clockInSuccess = 'Pointage d\'entrée enregistré';
  static const String clockOutSuccess = 'Pointage de sortie enregistré';
  static const String timesheetSaved = 'Feuille de temps sauvegardée';
  
  // CEMAC Countries
  static const List<String> cemacCountries = [
    'GA', // Gabon
    'CM', // Cameroun
    'TD', // Tchad
    'CF', // République Centrafricaine
    'GQ', // Guinée Équatoriale
    'CG', // Congo
  ];
  
  // Currencies
  static const Map<String, String> currencies = {
    'GA': 'XOF', // Gabon - Franc CFA
    'CM': 'XAF', // Cameroun - Franc CFA
    'TD': 'XAF', // Tchad - Franc CFA
    'CF': 'XAF', // RCA - Franc CFA
    'GQ': 'XAF', // Guinée Équatoriale - Franc CFA
    'CG': 'XAF', // Congo - Franc CFA
  };
}