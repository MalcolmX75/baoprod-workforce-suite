import 'package:shared_preferences/shared_preferences.dart';
import 'package:hive_flutter/hive_flutter.dart';
import '../utils/constants.dart';

class StorageService {
  static late SharedPreferences _prefs;
  static late Box _box;

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _box = await Hive.openBox('baoprod_workforce');
  }

  // User data
  static Future<void> saveUser(Map<String, dynamic> userData) async {
    await _box.put(AppConstants.userKey, userData);
  }

  static Map<String, dynamic>? getUser() {
    return _box.get(AppConstants.userKey);
  }

  static Future<void> clearUser() async {
    await _box.delete(AppConstants.userKey);
  }

  // Timesheet data
  static Future<void> saveTimesheetData(Map<String, dynamic> data) async {
    await _box.put(AppConstants.timesheetKey, data);
  }

  static Map<String, dynamic>? getTimesheetData() {
    return _box.get(AppConstants.timesheetKey);
  }

  static Future<void> clearTimesheetData() async {
    await _box.delete(AppConstants.timesheetKey);
  }

  // Settings
  static Future<void> saveSettings(Map<String, dynamic> settings) async {
    await _box.put(AppConstants.settingsKey, settings);
  }

  static Map<String, dynamic>? getSettings() {
    return _box.get(AppConstants.settingsKey);
  }

  static Future<void> clearSettings() async {
    await _box.delete(AppConstants.settingsKey);
  }

  // Generic storage methods
  static Future<void> save(String key, dynamic value) async {
    await _box.put(key, value);
  }

  static T? get<T>(String key) {
    return _box.get(key);
  }

  static Future<void> remove(String key) async {
    await _box.delete(key);
  }

  static Future<void> clear() async {
    await _box.clear();
  }
}