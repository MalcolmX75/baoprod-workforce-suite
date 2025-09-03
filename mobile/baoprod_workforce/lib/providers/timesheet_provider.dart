import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../models/timesheet.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../utils/constants.dart';

class TimesheetProvider extends ChangeNotifier {
  List<Timesheet> _timesheets = [];
  Timesheet? _currentTimesheet;
  bool _isLoading = false;
  String? _error;
  Position? _currentPosition;
  bool _isLocationLoading = false;
  
  List<Timesheet> get timesheets => _timesheets;
  Timesheet? get currentTimesheet => _currentTimesheet;
  bool get isLoading => _isLoading;
  String? get error => _error;
  Position? get currentPosition => _currentPosition;
  bool get isLocationLoading => _isLocationLoading;
  
  bool get canClockIn => _currentTimesheet == null || _currentTimesheet!.isClockInOnly;
  bool get canClockOut => _currentTimesheet != null && _currentTimesheet!.isClockInOnly;
  
  TimesheetProvider() {
    _loadTimesheetsFromStorage();
  }
  
  Future<void> _loadTimesheetsFromStorage() async {
    try {
      final timesheetData = StorageService.getTimesheetData();
      if (timesheetData != null && timesheetData['timesheets'] != null) {
        _timesheets = (timesheetData['timesheets'] as List)
            .map((json) => Timesheet.fromJson(json))
            .toList();
        
        if (timesheetData['current_timesheet'] != null) {
          _currentTimesheet = Timesheet.fromJson(timesheetData['current_timesheet']);
        }
        
        notifyListeners();
      }
    } catch (e) {
      print('Error loading timesheets from storage: $e');
    }
  }
  
  Future<void> _saveTimesheetsToStorage() async {
    try {
      await StorageService.saveTimesheetData({
        'timesheets': _timesheets.map((t) => t.toJson()).toList(),
        'current_timesheet': _currentTimesheet?.toJson(),
      });
    } catch (e) {
      print('Error saving timesheets to storage: $e');
    }
  }
  
  Future<void> loadTimesheets({int page = 1, int limit = 20}) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.getTimesheets(page: page, limit: limit);
      
      if (response.statusCode == 200) {
        final data = response.data;
        if (data['data'] != null) {
          _timesheets = (data['data'] as List)
              .map((json) => Timesheet.fromJson(json))
              .toList();
          
          await _saveTimesheetsToStorage();
          notifyListeners();
        }
      } else {
        _setError('Erreur lors du chargement des feuilles de temps');
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  Future<bool> clockIn({String? commentaire}) async {
    if (!await _getCurrentLocation()) {
      _setError(AppConstants.locationError);
      return false;
    }
    
    _setLoading(true);
    _clearError();
    
    try {
      final clockInData = {
        'date_pointage': DateTime.now().toIso8601String().split('T')[0],
        'heure_debut': DateTime.now().toIso8601String(),
        'latitude': _currentPosition!.latitude,
        'longitude': _currentPosition!.longitude,
        'commentaire': commentaire,
      };
      
      final response = await ApiService.clockIn(clockInData);
      
      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        _currentTimesheet = Timesheet.fromJson(data);
        
        // Add to timesheets list if not already present
        if (!_timesheets.any((t) => t.id == _currentTimesheet!.id)) {
          _timesheets.insert(0, _currentTimesheet!);
        }
        
        await _saveTimesheetsToStorage();
        notifyListeners();
        return true;
      } else {
        _setError('Erreur lors du pointage d\'entrée');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }
  
  Future<bool> clockOut({String? commentaire}) async {
    if (_currentTimesheet == null) {
      _setError('Aucun pointage d\'entrée trouvé');
      return false;
    }
    
    if (!await _getCurrentLocation()) {
      _setError(AppConstants.locationError);
      return false;
    }
    
    _setLoading(true);
    _clearError();
    
    try {
      final clockOutData = {
        'heure_fin': DateTime.now().toIso8601String(),
        'latitude': _currentPosition!.latitude,
        'longitude': _currentPosition!.longitude,
        'commentaire': commentaire,
      };
      
      final response = await ApiService.clockOut(_currentTimesheet!.id, clockOutData);
      
      if (response.statusCode == 200) {
        final data = response.data;
        final updatedTimesheet = Timesheet.fromJson(data);
        
        // Update current timesheet
        _currentTimesheet = updatedTimesheet;
        
        // Update in timesheets list
        final index = _timesheets.indexWhere((t) => t.id == updatedTimesheet.id);
        if (index != -1) {
          _timesheets[index] = updatedTimesheet;
        }
        
        await _saveTimesheetsToStorage();
        notifyListeners();
        return true;
      } else {
        _setError('Erreur lors du pointage de sortie');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }
  
  Future<bool> _getCurrentLocation() async {
    _setLocationLoading(true);
    
    try {
      // Check if location services are enabled
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _setError('Les services de localisation sont désactivés');
        return false;
      }
      
      // Check location permissions
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          _setError(AppConstants.permissionError);
          return false;
        }
      }
      
      if (permission == LocationPermission.deniedForever) {
        _setError('Les permissions de localisation sont définitivement refusées');
        return false;
      }
      
      // Get current position
      _currentPosition = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: AppConstants.locationTimeout,
      );
      
      // Save last location
      await StorageService.saveLastLocation(
        _currentPosition!.latitude,
        _currentPosition!.longitude,
      );
      
      return true;
    } catch (e) {
      _setError('Erreur lors de l\'obtention de la position: ${e.toString()}');
      return false;
    } finally {
      _setLocationLoading(false);
    }
  }
  
  Future<void> refreshCurrentTimesheet() async {
    if (_currentTimesheet == null) return;
    
    _setLoading(true);
    _clearError();
    
    try {
      // This would be implemented when the API endpoint is available
      // final response = await ApiService.getTimesheet(_currentTimesheet!.id);
      
      // For now, just reload all timesheets
      await loadTimesheets();
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }
  
  void _setLocationLoading(bool loading) {
    _isLocationLoading = loading;
    notifyListeners();
  }
  
  void _setError(String error) {
    _error = error;
    notifyListeners();
  }
  
  void _clearError() {
    _error = null;
    notifyListeners();
  }
  
  String _getErrorMessage(dynamic error) {
    if (error.toString().contains('SocketException') || 
        error.toString().contains('NetworkException')) {
      return AppConstants.networkError;
    } else if (error.toString().contains('401') || 
               error.toString().contains('Unauthorized')) {
      return AppConstants.authError;
    } else if (error.toString().contains('500') || 
               error.toString().contains('Internal Server Error')) {
      return AppConstants.serverError;
    } else {
      return 'Une erreur inattendue s\'est produite';
    }
  }
  
  // Helper methods
  List<Timesheet> getTodayTimesheets() {
    final today = DateTime.now();
    return _timesheets.where((t) => 
      t.datePointage.year == today.year &&
      t.datePointage.month == today.month &&
      t.datePointage.day == today.day
    ).toList();
  }
  
  List<Timesheet> getWeekTimesheets() {
    final now = DateTime.now();
    final startOfWeek = now.subtract(Duration(days: now.weekday - 1));
    final endOfWeek = startOfWeek.add(const Duration(days: 6));
    
    return _timesheets.where((t) => 
      t.datePointage.isAfter(startOfWeek.subtract(const Duration(days: 1))) &&
      t.datePointage.isBefore(endOfWeek.add(const Duration(days: 1)))
    ).toList();
  }
  
  double getTotalHoursThisWeek() {
    final weekTimesheets = getWeekTimesheets();
    return weekTimesheets.fold(0.0, (sum, t) => sum + t.heuresTravaillees);
  }
  
  double getTotalOvertimeThisWeek() {
    final weekTimesheets = getWeekTimesheets();
    return weekTimesheets.fold(0.0, (sum, t) => sum + t.heuresSupplementaires);
  }
  
  @override
  void dispose() {
    super.dispose();
  }
}