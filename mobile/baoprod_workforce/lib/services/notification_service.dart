import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import '../utils/constants.dart';

class NotificationService {
  static NotificationService? _instance;
  static NotificationService get instance => _instance ??= NotificationService._();
  
  NotificationService._();
  
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  
  String? _fcmToken;
  String? get fcmToken => _fcmToken;
  
  /// Initialise le service de notifications
  Future<void> init() async {
    await _initFirebaseMessaging();
    await _initLocalNotifications();
    await _requestPermissions();
  }
  
  /// Initialise Firebase Cloud Messaging
  Future<void> _initFirebaseMessaging() async {
    try {
      // Obtenir le token FCM
      _fcmToken = await _firebaseMessaging.getToken();
      print('FCM Token: $_fcmToken');
      
      // Configurer les gestionnaires de messages
      FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
      FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessage);
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
      
      // Gérer les notifications quand l'app est fermée
      RemoteMessage? initialMessage = await _firebaseMessaging.getInitialMessage();
      if (initialMessage != null) {
        _handleBackgroundMessage(initialMessage);
      }
      
    } catch (e) {
      print('Erreur lors de l\'initialisation de Firebase Messaging: $e');
    }
  }
  
  /// Initialise les notifications locales
  Future<void> _initLocalNotifications() async {
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    
    const DarwinInitializationSettings initializationSettingsIOS =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    
    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsIOS,
    );
    
    await _localNotifications.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );
  }
  
  /// Demande les permissions de notification
  Future<bool> _requestPermissions() async {
    try {
      // Demander la permission pour les notifications
      NotificationSettings settings = await _firebaseMessaging.requestPermission(
        alert: true,
        announcement: false,
        badge: true,
        carPlay: false,
        criticalAlert: false,
        provisional: false,
        sound: true,
      );
      
      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        print('Permission de notification accordée');
        return true;
      } else if (settings.authorizationStatus == AuthorizationStatus.provisional) {
        print('Permission de notification provisoire accordée');
        return true;
      } else {
        print('Permission de notification refusée');
        return false;
      }
    } catch (e) {
      print('Erreur lors de la demande de permission: $e');
      return false;
    }
  }
  
  /// Gère les messages reçus en premier plan
  void _handleForegroundMessage(RemoteMessage message) {
    print('Message reçu en premier plan: ${message.messageId}');
    print('Titre: ${message.notification?.title}');
    print('Corps: ${message.notification?.body}');
    print('Data: ${message.data}');
    
    // Afficher une notification locale
    _showLocalNotification(
      title: message.notification?.title ?? 'Nouvelle notification',
      body: message.notification?.body ?? '',
      payload: message.data.toString(),
    );
  }
  
  /// Gère les messages reçus en arrière-plan
  void _handleBackgroundMessage(RemoteMessage message) {
    print('Message reçu en arrière-plan: ${message.messageId}');
    print('Titre: ${message.notification?.title}');
    print('Corps: ${message.notification?.body}');
    print('Data: ${message.data}');
    
    // Traiter le message selon son type
    _processNotificationData(message.data);
  }
  
  /// Traite les données de notification
  void _processNotificationData(Map<String, dynamic> data) {
    final String type = data['type'] ?? '';
    
    switch (type) {
      case 'clock_in_reminder':
        _handleClockInReminder(data);
        break;
      case 'clock_out_reminder':
        _handleClockOutReminder(data);
        break;
      case 'timesheet_approved':
        _handleTimesheetApproved(data);
        break;
      case 'timesheet_rejected':
        _handleTimesheetRejected(data);
        break;
      case 'contract_update':
        _handleContractUpdate(data);
        break;
      default:
        print('Type de notification non reconnu: $type');
    }
  }
  
  /// Gère les rappels de pointage d'entrée
  void _handleClockInReminder(Map<String, dynamic> data) {
    print('Rappel de pointage d\'entrée reçu');
    // Logique spécifique pour les rappels de pointage d'entrée
  }
  
  /// Gère les rappels de pointage de sortie
  void _handleClockOutReminder(Map<String, dynamic> data) {
    print('Rappel de pointage de sortie reçu');
    // Logique spécifique pour les rappels de pointage de sortie
  }
  
  /// Gère les notifications de timesheet approuvé
  void _handleTimesheetApproved(Map<String, dynamic> data) {
    print('Timesheet approuvé reçu');
    // Logique spécifique pour les timesheets approuvés
  }
  
  /// Gère les notifications de timesheet rejeté
  void _handleTimesheetRejected(Map<String, dynamic> data) {
    print('Timesheet rejeté reçu');
    // Logique spécifique pour les timesheets rejetés
  }
  
  /// Gère les notifications de mise à jour de contrat
  void _handleContractUpdate(Map<String, dynamic> data) {
    print('Mise à jour de contrat reçue');
    // Logique spécifique pour les mises à jour de contrat
  }
  
  /// Affiche une notification locale
  Future<void> _showLocalNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'baoprod_workforce',
      'BaoProd Workforce',
      channelDescription: 'Notifications de l\'application BaoProd Workforce',
      importance: Importance.high,
      priority: Priority.high,
      showWhen: true,
    );
    
    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );
    
    const NotificationDetails notificationDetails = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );
    
    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch.remainder(100000),
      title,
      body,
      notificationDetails,
      payload: payload,
    );
  }
  
  /// Gère le tap sur une notification
  void _onNotificationTapped(NotificationResponse response) {
    print('Notification tapée: ${response.payload}');
    
    if (response.payload != null) {
      // Traiter le payload de la notification
      _processNotificationPayload(response.payload!);
    }
  }
  
  /// Traite le payload d'une notification
  void _processNotificationPayload(String payload) {
    try {
      // Parser le payload et naviguer vers la page appropriée
      // Cette logique dépendra de la structure de votre payload
      print('Traitement du payload: $payload');
    } catch (e) {
      print('Erreur lors du traitement du payload: $e');
    }
  }
  
  /// Envoie une notification locale
  Future<void> showLocalNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    await _showLocalNotification(
      title: title,
      body: body,
      payload: payload,
    );
  }
  
  /// Programme une notification locale
  Future<void> scheduleLocalNotification({
    required int id,
    required String title,
    required String body,
    required DateTime scheduledDate,
    String? payload,
  }) async {
    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'baoprod_workforce_scheduled',
      'BaoProd Workforce - Programmé',
      channelDescription: 'Notifications programmées de l\'application BaoProd Workforce',
      importance: Importance.high,
      priority: Priority.high,
    );
    
    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );
    
    const NotificationDetails notificationDetails = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );
    
    await _localNotifications.zonedSchedule(
      id,
      title,
      body,
      scheduledDate,
      notificationDetails,
      payload: payload,
      uiLocalNotificationDateInterpretation: UILocalNotificationDateInterpretation.absoluteTime,
    );
  }
  
  /// Annule une notification programmée
  Future<void> cancelScheduledNotification(int id) async {
    await _localNotifications.cancel(id);
  }
  
  /// Annule toutes les notifications programmées
  Future<void> cancelAllScheduledNotifications() async {
    await _localNotifications.cancelAll();
  }
  
  /// Obtient le token FCM
  Future<String?> getFCMToken() async {
    try {
      _fcmToken = await _firebaseMessaging.getToken();
      return _fcmToken;
    } catch (e) {
      print('Erreur lors de la récupération du token FCM: $e');
      return null;
    }
  }
  
  /// S'abonne à un topic
  Future<void> subscribeToTopic(String topic) async {
    try {
      await _firebaseMessaging.subscribeToTopic(topic);
      print('Abonné au topic: $topic');
    } catch (e) {
      print('Erreur lors de l\'abonnement au topic $topic: $e');
    }
  }
  
  /// Se désabonne d'un topic
  Future<void> unsubscribeFromTopic(String topic) async {
    try {
      await _firebaseMessaging.unsubscribeFromTopic(topic);
      print('Désabonné du topic: $topic');
    } catch (e) {
      print('Erreur lors du désabonnement du topic $topic: $e');
    }
  }
  
  /// Configure les topics par défaut
  Future<void> setupDefaultTopics() async {
    // S'abonner aux topics par défaut
    await subscribeToTopic('general');
    await subscribeToTopic('timesheets');
    await subscribeToTopic('contracts');
  }
}

/// Gestionnaire de messages en arrière-plan
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  print('Gestionnaire de messages en arrière-plan: ${message.messageId}');
  print('Titre: ${message.notification?.title}');
  print('Corps: ${message.notification?.body}');
  print('Data: ${message.data}');
  
  // Traiter le message en arrière-plan
  // Note: Les opérations UI ne sont pas possibles ici
}