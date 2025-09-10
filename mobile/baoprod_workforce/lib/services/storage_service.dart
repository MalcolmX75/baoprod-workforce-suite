import 'dart:convert';
import '../utils/constants.dart';

// In-memory storage stub for demo purposes
class StorageService {
  static Map<String, String> _storage = {};

  static Future<void> init() async {
    print('📁 Storage service initialized (in-memory mode)');
  }

  // User data
  static Future<void> saveUser(Map<String, dynamic> userData) async {
    _storage[AppConstants.userKey] = jsonEncode(userData);
    print('👤 User data saved to storage');
  }

  static Map<String, dynamic>? getUser() {
    final userStr = _storage[AppConstants.userKey];
    if (userStr != null) {
      try {
        return jsonDecode(userStr) as Map<String, dynamic>;
      } catch (e) {
        print('Error decoding user data: $e');
        return null;
      }
    }
    return null;
  }

  static Future<void> clearUser() async {
    _storage.remove(AppConstants.userKey);
    print('👤 User data cleared from storage');
  }

  // Timesheet data
  static Future<void> saveTimesheetData(Map<String, dynamic> data) async {
    _storage[AppConstants.timesheetKey] = jsonEncode(data);
    print('⏰ Timesheet data saved to storage');
  }

  static Map<String, dynamic>? getTimesheetData() {
    final dataStr = _storage[AppConstants.timesheetKey];
    if (dataStr != null) {
      try {
        return jsonDecode(dataStr) as Map<String, dynamic>;
      } catch (e) {
        print('Error decoding timesheet data: $e');
        return null;
      }
    }
    return null;
  }

  static Future<void> clearTimesheetData() async {
    _storage.remove(AppConstants.timesheetKey);
    print('⏰ Timesheet data cleared from storage');
  }

  // Settings
  static Future<void> saveSettings(Map<String, dynamic> settings) async {
    _storage[AppConstants.settingsKey] = jsonEncode(settings);
    print('⚙️ Settings saved to storage');
  }

  static Map<String, dynamic>? getSettings() {
    final settingsStr = _storage[AppConstants.settingsKey];
    if (settingsStr != null) {
      try {
        return jsonDecode(settingsStr) as Map<String, dynamic>;
      } catch (e) {
        print('Error decoding settings: $e');
        return null;
      }
    }
    return null;
  }

  static Future<void> clearSettings() async {
    _storage.remove(AppConstants.settingsKey);
    print('⚙️ Settings cleared from storage');
  }

  // Generic storage methods
  static Future<void> save(String key, dynamic value) async {
    if (value is String) {
      _storage[key] = value;
    } else {
      // For complex objects, encode as JSON string
      _storage[key] = jsonEncode(value);
    }
    print('💾 Saved $key to storage');
  }

  static T? get<T>(String key) {
    try {
      final value = _storage[key];
      if (value == null) return null;
      
      if (T == String) {
        return value as T;
      } else {
        // Try to decode JSON string to object
        return jsonDecode(value) as T;
      }
    } catch (e) {
      print('Error getting value for key $key: $e');
      return null;
    }
  }

  static Future<void> remove(String key) async {
    _storage.remove(key);
    print('🗑️ Removed $key from storage');
  }

  static Future<void> clear() async {
    _storage.clear();
    print('🗑️ Cleared all storage');
  }
}