import 'package:flutter/material.dart';
import '../models/notification.dart';
import '../services/storage_service.dart';

class NotificationProvider extends ChangeNotifier {
  List<AppNotification> _notifications = [];
  bool _isLoading = false;
  String? _error;

  List<AppNotification> get notifications => _notifications;
  List<AppNotification> get unreadNotifications => 
      _notifications.where((n) => !n.isRead).toList();
  List<AppNotification> get importantNotifications => 
      _notifications.where((n) => n.isImportant && !n.isRead).toList();
      
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasError => _error != null;
  
  int get unreadCount => unreadNotifications.length;
  bool get hasUnread => unreadCount > 0;

  Future<void> loadNotifications({bool refresh = false}) async {
    if (refresh) {
      _notifications.clear();
    }

    _setLoading(true);
    _clearError();

    try {
      // MODE DEMO - Notifications simulées
      await Future.delayed(const Duration(milliseconds: 600));
      
      final List<Map<String, dynamic>> demoNotifications = [
        // Notifications récentes
        {
          'id': 1,
          'title': 'Nouvelle offre d\'emploi',
          'message': 'Un nouveau poste de Développeur Mobile Flutter vient d\'être publié chez TechGabon.',
          'type': 'job',
          'created_at': DateTime.now().subtract(const Duration(minutes: 5)).toIso8601String(),
          'is_read': false,
          'is_important': true,
          'action_url': '/job-search',
          'data': {'job_id': 4}
        },
        {
          'id': 2,
          'title': 'Pointage manqué',
          'message': 'Vous avez oublié de pointer votre sortie hier à 18h00.',
          'type': 'timesheet',
          'created_at': DateTime.now().subtract(const Duration(hours: 2)).toIso8601String(),
          'is_read': false,
          'is_important': true,
          'action_url': '/timesheets'
        },
        {
          'id': 3,
          'title': 'Candidature acceptée',
          'message': 'Félicitations ! Votre candidature pour le poste de Développeur Web Laravel a été acceptée.',
          'type': 'job',
          'created_at': DateTime.now().subtract(const Duration(hours: 4)).toIso8601String(),
          'is_read': false,
          'is_important': true,
          'action_url': '/job-search'
        },
        {
          'id': 4,
          'title': 'Rappel pointage',
          'message': 'N\'oubliez pas de pointer votre arrivée ce matin.',
          'type': 'reminder',
          'created_at': DateTime.now().subtract(const Duration(hours: 8)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/clock-in-out'
        },
        {
          'id': 5,
          'title': 'Nouveau message',
          'message': 'Votre employeur a envoyé un message concernant votre horaire de demain.',
          'type': 'message',
          'created_at': DateTime.now().subtract(const Duration(hours: 12)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/chat'
        },
        {
          'id': 6,
          'title': 'Contrat mis à jour',
          'message': 'Votre contrat de travail a été mis à jour. Consultez les modifications.',
          'type': 'contract',
          'created_at': DateTime.now().subtract(const Duration(days: 1)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/contrats'
        },
        {
          'id': 7,
          'title': 'Mise à jour disponible',
          'message': 'Une nouvelle version de l\'application est disponible avec de nouvelles fonctionnalités.',
          'type': 'system',
          'created_at': DateTime.now().subtract(const Duration(days: 2)).toIso8601String(),
          'is_read': false,
          'is_important': false,
        },
        {
          'id': 8,
          'title': 'Feuille de temps validée',
          'message': 'Votre feuille de temps de la semaine dernière a été validée par votre manager.',
          'type': 'timesheet',
          'created_at': DateTime.now().subtract(const Duration(days: 3)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/timesheets'
        },
        {
          'id': 9,
          'title': 'Profil complété',
          'message': 'Merci d\'avoir complété votre profil ! Vos chances de trouver un emploi sont maintenant augmentées.',
          'type': 'system',
          'created_at': DateTime.now().subtract(const Duration(days: 7)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/profile'
        },
        {
          'id': 10,
          'title': 'Bienvenue sur BaoProd !',
          'message': 'Bienvenue dans votre nouvelle application de gestion du travail. Découvrez toutes les fonctionnalités.',
          'type': 'system',
          'created_at': DateTime.now().subtract(const Duration(days: 30)).toIso8601String(),
          'is_read': true,
          'is_important': false,
          'action_url': '/onboarding'
        }
      ];

      _notifications = demoNotifications
          .map((notificationData) => AppNotification.fromJson(notificationData))
          .toList();

      // Trier par date (plus récent en premier)
      _notifications.sort((a, b) => b.createdAt.compareTo(a.createdAt));

      print('📢 ${_notifications.length} notifications chargées');
      notifyListeners();
    } catch (e) {
      _setError('Erreur lors du chargement des notifications: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<void> markAsRead(int notificationId) async {
    try {
      final index = _notifications.indexWhere((n) => n.id == notificationId);
      if (index != -1) {
        _notifications[index] = _notifications[index].copyWith(isRead: true);
        notifyListeners();
        
        // Sauvegarder localement
        await _saveToStorage();
        print('📢 Notification ${notificationId} marquée comme lue');
      }
    } catch (e) {
      print('Erreur marquer comme lu: $e');
    }
  }

  Future<void> markAllAsRead() async {
    try {
      for (int i = 0; i < _notifications.length; i++) {
        if (!_notifications[i].isRead) {
          _notifications[i] = _notifications[i].copyWith(isRead: true);
        }
      }
      
      notifyListeners();
      await _saveToStorage();
      print('📢 Toutes les notifications marquées comme lues');
    } catch (e) {
      print('Erreur marquer toutes comme lues: $e');
    }
  }

  Future<void> deleteNotification(int notificationId) async {
    try {
      _notifications.removeWhere((n) => n.id == notificationId);
      notifyListeners();
      
      await _saveToStorage();
      print('📢 Notification ${notificationId} supprimée');
    } catch (e) {
      print('Erreur suppression notification: $e');
    }
  }

  Future<void> clearAllNotifications() async {
    try {
      _notifications.clear();
      notifyListeners();
      
      await _saveToStorage();
      print('📢 Toutes les notifications supprimées');
    } catch (e) {
      print('Erreur suppression toutes notifications: $e');
    }
  }

  List<AppNotification> getNotificationsByType(NotificationType type) {
    return _notifications.where((n) => n.type == type).toList();
  }

  AppNotification? getNotificationById(int id) {
    try {
      return _notifications.firstWhere((n) => n.id == id);
    } catch (e) {
      return null;
    }
  }

  Future<void> _saveToStorage() async {
    try {
      final notificationsJson = _notifications.map((n) => n.toJson()).toList();
      await StorageService.save('notifications', notificationsJson);
    } catch (e) {
      print('Erreur sauvegarde notifications: $e');
    }
  }

  Future<void> _loadFromStorage() async {
    try {
      final notificationsJson = await StorageService.get<List>('notifications');
      if (notificationsJson != null) {
        _notifications = (notificationsJson as List)
            .map((json) => AppNotification.fromJson(Map<String, dynamic>.from(json)))
            .toList();
        notifyListeners();
      }
    } catch (e) {
      print('Erreur chargement notifications: $e');
    }
  }

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
  }

  @override
  void dispose() {
    super.dispose();
  }
}