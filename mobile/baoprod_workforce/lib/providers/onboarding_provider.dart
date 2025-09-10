import 'package:flutter/material.dart';
import '../services/storage_service.dart';

class OnboardingProvider extends ChangeNotifier {
  bool _hasSeenOnboardingThisSession = false;
  bool _isLoading = false;

  bool get hasSeenOnboardingThisSession => _hasSeenOnboardingThisSession;
  bool get isLoading => _isLoading;

  /// Cette méthode détermine si l'onboarding doit être affiché
  /// Selon la demande, il doit s'afficher à chaque ouverture de l'app
  bool get shouldShowOnboarding {
    return !_hasSeenOnboardingThisSession;
  }

  /// Marque l'onboarding comme vu pour cette session uniquement
  void markOnboardingAsSeen() {
    _hasSeenOnboardingThisSession = true;
    notifyListeners();
    print('🎯 Onboarding marqué comme vu pour cette session');
  }

  /// Réinitialise l'état de l'onboarding (appelé au démarrage de l'app)
  Future<void> resetOnboardingForNewSession() async {
    _isLoading = true;

    // Simuler un petit délai
    await Future.delayed(const Duration(milliseconds: 100));

    _hasSeenOnboardingThisSession = false;
    _isLoading = false;
    
    print('🔄 État onboarding réinitialisé pour nouvelle session');
  }

  /// Méthode appelée lors du démarrage de l'application
  Future<void> init() async {
    await resetOnboardingForNewSession();
  }

  @override
  void dispose() {
    super.dispose();
  }
}