// import 'package:permission_handler/permission_handler.dart'; // Temporarily removed
import '../utils/constants.dart';

class NotificationService {
  static NotificationService? _instance;
  static NotificationService get instance => _instance ??= NotificationService._();
  
  NotificationService._();
  
  /// Initialise le service de notifications (version stub)
  Future<void> init() async {
    await _requestPermissions();
  }
  
  /// Demande les permissions de notification (stub)
  Future<void> _requestPermissions() async {
    print('📱 Demande de permission de notification (simulée)');
  }
  
  /// Affiche une notification locale (version stub)
  Future<void> showLocalNotification({
    required String title,
    required String body,
    String? payload,
    int id = 0,
  }) async {
    // Version stub - affiche dans la console en attendant
    print('📱 Notification: $title - $body');
  }
  
  /// Affiche une notification de rappel de pointage
  Future<void> showClockInReminder() async {
    await showLocalNotification(
      title: 'Rappel de pointage',
      body: 'N\'oubliez pas de pointer votre arrivée !',
      payload: 'clock_in_reminder',
      id: 1,
    );
  }
  
  /// Affiche une notification de rappel de sortie
  Future<void> showClockOutReminder() async {
    await showLocalNotification(
      title: 'Rappel de pointage',
      body: 'N\'oubliez pas de pointer votre sortie !',
      payload: 'clock_out_reminder',
      id: 2,
    );
  }
  
  /// Affiche une notification d'approbation
  Future<void> showApprovalNotification({
    required String title,
    required String message,
  }) async {
    await showLocalNotification(
      title: title,
      body: message,
      payload: 'approval_notification',
      id: 3,
    );
  }
  
  /// Affiche une notification de nouveau job
  Future<void> showNewJobNotification({
    required String jobTitle,
    required String company,
  }) async {
    await showLocalNotification(
      title: 'Nouvel emploi disponible',
      body: '$jobTitle chez $company',
      payload: 'new_job',
      id: 4,
    );
  }
  
  /// Annule une notification (version stub)
  Future<void> cancelNotification(int id) async {
    print('🗑️ Notification $id annulée');
  }
  
  /// Annule toutes les notifications (version stub)
  Future<void> cancelAllNotifications() async {
    print('🗑️ Toutes les notifications annulées');
  }
  
  /// Programme une notification (version stub)
  Future<void> scheduleNotification({
    required int id,
    required String title,
    required String body,
    required DateTime scheduledDate,
    String? payload,
  }) async {
    print('⏰ Notification programmée: $title à ${scheduledDate.toString()}');
  }
  
  /// Programme un rappel de pointage quotidien
  Future<void> scheduleDailyClockInReminder() async {
    await cancelNotification(100);
    
    final tomorrow = DateTime.now().add(const Duration(days: 1));
    final scheduledTime = DateTime(tomorrow.year, tomorrow.month, tomorrow.day, 8, 0);
    
    await scheduleNotification(
      id: 100,
      title: 'Rappel de pointage',
      body: 'N\'oubliez pas de pointer votre arrivée !',
      scheduledDate: scheduledTime,
      payload: 'daily_clock_in_reminder',
    );
  }
  
  /// Programme un rappel de pointage de sortie
  Future<void> scheduleDailyClockOutReminder() async {
    await cancelNotification(101);
    
    final tomorrow = DateTime.now().add(const Duration(days: 1));
    final scheduledTime = DateTime(tomorrow.year, tomorrow.month, tomorrow.day, 17, 0);
    
    await scheduleNotification(
      id: 101,
      title: 'Rappel de pointage',
      body: 'N\'oubliez pas de pointer votre sortie !',
      scheduledDate: scheduledTime,
      payload: 'daily_clock_out_reminder',
    );
  }
}