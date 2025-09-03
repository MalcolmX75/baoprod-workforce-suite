import 'package:shared_preferences/shared_preferences.dart';
import 'package:hive_flutter/hive_flutter.dart';
import '../utils/constants.dart';

class StorageService {
  static late Box _box;
  
  static Future<void> init() async {
    _box = await Hive.openBox('baoprod_workforce');
  }
  
  // SharedPreferences methods
  static Future<void> setString(String key, String value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(key, value);
  }
  
  static Future<String?> getString(String key) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(key);
  }
  
  static Future<void> setBool(String key, bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(key, value);
  }
  
  static Future<bool?> getBool(String key) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(key);
  }
  
  static Future<void> setInt(String key, int value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(key, value);
  }
  
  static Future<int?> getInt(String key) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(key);
  }
  
  static Future<void> remove(String key) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(key);
  }
  
  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }
  
  // Hive methods for complex data
  static Future<void> setObject(String key, dynamic value) async {
    await _box.put(key, value);
  }
  
  static T? getObject<T>(String key) {
    return _box.get(key);
  }
  
  static Future<void> removeObject(String key) async {
    await _box.delete(key);
  }
  
  static Future<void> clearAll() async {
    await _box.clear();
  }
  
  // Specific methods for app data
  static Future<void> saveUser(Map<String, dynamic> user) async {
    await setObject(AppConstants.userKey, user);
  }
  
  static Map<String, dynamic>? getUser() {
    return getObject<Map<String, dynamic>>(AppConstants.userKey);
  }
  
  static Future<void> clearUser() async {
    await removeObject(AppConstants.userKey);
  }
  
  static Future<void> saveSettings(Map<String, dynamic> settings) async {
    await setObject(AppConstants.settingsKey, settings);
  }
  
  static Map<String, dynamic>? getSettings() {
    return getObject<Map<String, dynamic>>(AppConstants.settingsKey);
  }
  
  static Future<void> saveTimesheetData(Map<String, dynamic> timesheetData) async {
    await setObject(AppConstants.timesheetKey, timesheetData);
  }
  
  static Map<String, dynamic>? getTimesheetData() {
    return getObject<Map<String, dynamic>>(AppConstants.timesheetKey);
  }
  
  static Future<void> clearTimesheetData() async {
    await removeObject(AppConstants.timesheetKey);
  }
  
  // Location data
  static Future<void> saveLastLocation(double latitude, double longitude) async {
    await setObject('last_location', {
      'latitude': latitude,
      'longitude': longitude,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }
  
  static Map<String, dynamic>? getLastLocation() {
    return getObject<Map<String, dynamic>>('last_location');
  }
  
  // App state
  static Future<void> saveAppState(Map<String, dynamic> state) async {
    await setObject('app_state', state);
  }
  
  static Map<String, dynamic>? getAppState() {
    return getObject<Map<String, dynamic>>('app_state');
  }
  
  // Cache management
  static Future<void> clearCache() async {
    await clearAll();
    await clear();
  }
  
  // Data export/import
  static Map<String, dynamic> exportAllData() {
    return {
      'user': getUser(),
      'settings': getSettings(),
      'timesheet_data': getTimesheetData(),
      'last_location': getLastLocation(),
      'app_state': getAppState(),
    };
  }
  
  static Future<void> importData(Map<String, dynamic> data) async {
    if (data['user'] != null) {
      await saveUser(data['user']);
    }
    if (data['settings'] != null) {
      await saveSettings(data['settings']);
    }
    if (data['timesheet_data'] != null) {
      await saveTimesheetData(data['timesheet_data']);
    }
    if (data['last_location'] != null) {
      await setObject('last_location', data['last_location']);
    }
    if (data['app_state'] != null) {
      await saveAppState(data['app_state']);
    }
  }
}