import 'package:flutter/material.dart';
import '../services/storage_service.dart';

class LanguageProvider extends ChangeNotifier {
  Locale _currentLocale = const Locale('fr'); // Default to French
  
  Locale get currentLocale => _currentLocale;
  
  String get currentLanguageCode => _currentLocale.languageCode;
  
  String get currentLanguageName {
    switch (_currentLocale.languageCode) {
      case 'en':
        return 'English';
      case 'fr':
        return 'Français';
      default:
        return 'Français';
    }
  }

  List<Locale> get supportedLocales => [
    const Locale('fr'),
    const Locale('en'),
  ];

  Future<void> init() async {
    await _loadSavedLanguage();
  }

  Future<void> _loadSavedLanguage() async {
    try {
      final savedLanguage = await StorageService.get<String>('selected_language');
      if (savedLanguage != null && _isValidLanguageCode(savedLanguage)) {
        _currentLocale = Locale(savedLanguage);
        notifyListeners();
        print('📱 Langue chargée: $savedLanguage');
      } else {
        // Use system locale if available, otherwise default to French
        final systemLocale = WidgetsBinding.instance.platformDispatcher.locale;
        if (_isValidLanguageCode(systemLocale.languageCode)) {
          _currentLocale = Locale(systemLocale.languageCode);
        } else {
          _currentLocale = const Locale('fr');
        }
        await _saveLanguage(_currentLocale.languageCode);
        notifyListeners();
        print('📱 Langue par défaut définie: ${_currentLocale.languageCode}');
      }
    } catch (e) {
      print('Erreur lors du chargement de la langue: $e');
      _currentLocale = const Locale('fr');
    }
  }

  bool _isValidLanguageCode(String languageCode) {
    return supportedLocales.any((locale) => locale.languageCode == languageCode);
  }

  Future<void> changeLanguage(String languageCode) async {
    if (!_isValidLanguageCode(languageCode)) {
      print('Code de langue non supporté: $languageCode');
      return;
    }

    if (_currentLocale.languageCode == languageCode) {
      print('Langue déjà sélectionnée: $languageCode');
      return;
    }

    _currentLocale = Locale(languageCode);
    await _saveLanguage(languageCode);
    notifyListeners();
    print('📱 Langue changée vers: $languageCode');
  }

  Future<void> _saveLanguage(String languageCode) async {
    try {
      await StorageService.save('selected_language', languageCode);
    } catch (e) {
      print('Erreur lors de la sauvegarde de la langue: $e');
    }
  }

  Future<void> setToSystemLanguage() async {
    final systemLocale = WidgetsBinding.instance.platformDispatcher.locale;
    final systemLanguageCode = systemLocale.languageCode;
    
    if (_isValidLanguageCode(systemLanguageCode)) {
      await changeLanguage(systemLanguageCode);
    } else {
      // Fallback to French if system language is not supported
      await changeLanguage('fr');
    }
  }

  Future<void> toggleLanguage() async {
    final newLanguage = _currentLocale.languageCode == 'fr' ? 'en' : 'fr';
    await changeLanguage(newLanguage);
  }

  Map<String, String> get languageOptions => {
    'fr': 'Français',
    'en': 'English',
  };

  String getLanguageDisplayName(String languageCode) {
    return languageOptions[languageCode] ?? languageCode.toUpperCase();
  }

  bool get isFrench => _currentLocale.languageCode == 'fr';
  bool get isEnglish => _currentLocale.languageCode == 'en';

  @override
  void dispose() {
    super.dispose();
  }
}