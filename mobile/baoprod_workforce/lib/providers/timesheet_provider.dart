import 'package:flutter/material.dart';
import '../models/timesheet.dart';
import '../services/api_service.dart';
import '../utils/constants.dart';

class TimesheetProvider extends ChangeNotifier {
  List<Timesheet> _timesheets = [];
  Timesheet? _currentTimesheet;
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMoreData = true;

  List<Timesheet> get timesheets => _timesheets;
  Timesheet? get currentTimesheet => _currentTimesheet;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMoreData => _hasMoreData;
  
  /// Vérifie si l'utilisateur est actuellement pointé (au travail)
  bool get isClockedIn {
    if (_currentTimesheet == null) return false;
    return _currentTimesheet!.heureFin == null && _currentTimesheet!.heureDebut != null;
  }
  
  /// Obtient l'heure de début du pointage actuel
  DateTime? get currentClockInTime {
    if (_currentTimesheet == null) return null;
    return _currentTimesheet!.heureDebut;
  }
  
  /// Obtient le temps écoulé depuis le pointage d'entrée
  Duration? get workTimeElapsed {
    if (!isClockedIn || currentClockInTime == null) return null;
    return DateTime.now().difference(currentClockInTime!);
  }
  
  /// Obtient le temps de travail restant (basé sur 8h par défaut)
  Duration getRemainingWorkTime({int targetHours = 8}) {
    if (!isClockedIn) return Duration(hours: targetHours);
    
    final elapsed = workTimeElapsed ?? Duration.zero;
    final target = Duration(hours: targetHours);
    final remaining = target - elapsed;
    
    return remaining.isNegative ? Duration.zero : remaining;
  }
  
  /// Obtient le statut de pointage formaté
  String get clockingStatus {
    if (!isClockedIn) return 'Non pointé';
    return 'Au travail depuis ${_formatDuration(workTimeElapsed!)}';
  }
  
  /// Formate une durée en texte lisible
  String _formatDuration(Duration duration) {
    final hours = duration.inHours;
    final minutes = duration.inMinutes % 60;
    
    if (hours > 0) {
      return '${hours}h${minutes.toString().padLeft(2, '0')}';
    } else {
      return '${minutes}min';
    }
  }

  /// Charge la liste des timesheets
  Future<void> loadTimesheets({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMoreData = true;
      _timesheets.clear();
    }

    if (!_hasMoreData) return;

    _setLoading(true);
    _clearError();

    // MODE DEMO - Données simulées de timesheets
    await Future.delayed(Duration(milliseconds: 800));
    
    if (_currentPage == 1) {
      final List<Map<String, dynamic>> demoTimesheets = [
        {
          'id': 1,
          'user_id': 1,
          'date_pointage': DateTime.now().toIso8601String(),
          'heure_debut': DateTime.now().subtract(Duration(hours: 8)).toIso8601String(),
          'heure_fin': DateTime.now().subtract(Duration(hours: 0, minutes: 30)).toIso8601String(),
          'heures_travaillees_minutes': 450, // 7.5 hours in minutes
          'latitude': 0.4162,
          'longitude': 9.4673,
          'status': 'submitted',
          'commentaire': 'Journée productive',
          'created_at': DateTime.now().subtract(Duration(hours: 8)).toIso8601String(),
          'updated_at': DateTime.now().subtract(Duration(minutes: 30)).toIso8601String(),
        },
        {
          'id': 2,
          'user_id': 1,
          'date_pointage': DateTime.now().subtract(Duration(days: 1)).toIso8601String(),
          'heure_debut': DateTime.now().subtract(Duration(days: 1, hours: 8)).toIso8601String(),
          'heure_fin': DateTime.now().subtract(Duration(days: 1, hours: 0)).toIso8601String(),
          'heures_travaillees_minutes': 480, // 8 hours in minutes
          'latitude': 0.4162,
          'longitude': 9.4673,
          'status': 'approved',
          'commentaire': null,
          'created_at': DateTime.now().subtract(Duration(days: 1, hours: 8)).toIso8601String(),
          'updated_at': DateTime.now().subtract(Duration(days: 1)).toIso8601String(),
        },
        {
          'id': 3,
          'user_id': 1,
          'date_pointage': DateTime.now().subtract(Duration(days: 2)).toIso8601String(),
          'heure_debut': DateTime.now().subtract(Duration(days: 2, hours: 8)).toIso8601String(),
          'heure_fin': DateTime.now().subtract(Duration(days: 2, hours: 0)).toIso8601String(),
          'heures_travaillees_minutes': 480, // 8 hours in minutes
          'latitude': 0.4162,
          'longitude': 9.4673,
          'status': 'approved',
          'commentaire': 'Formation équipe',
          'created_at': DateTime.now().subtract(Duration(days: 2, hours: 8)).toIso8601String(),
          'updated_at': DateTime.now().subtract(Duration(days: 2)).toIso8601String(),
        },
      ];

      final List<Timesheet> newTimesheets = demoTimesheets
          .map((timesheetData) => Timesheet.fromJson(timesheetData))
          .toList();

      if (refresh) {
        _timesheets = newTimesheets;
      } else {
        _timesheets.addAll(newTimesheets);
      }

      _hasMoreData = false; // Pas plus de données pour la démo
      _currentPage++;

      // Mettre à jour le timesheet actuel
      _updateCurrentTimesheet();

      notifyListeners();
    }
    
    _setLoading(false);

    /* API CALL DISABLED FOR DEMO
    try {
      final response = await ApiService.getTimesheets(
        page: _currentPage,
        limit: 10,
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final List<dynamic> timesheetsData = data['data'] ?? [];

        final List<Timesheet> newTimesheets = timesheetsData
            .map((timesheetData) => Timesheet.fromJson(timesheetData))
            .toList();

        if (refresh) {
          _timesheets = newTimesheets;
        } else {
          _timesheets.addAll(newTimesheets);
        }

        // Vérifier s'il y a plus de données
        _hasMoreData = newTimesheets.length == 10;
        _currentPage++;

        // Mettre à jour le timesheet actuel
        _updateCurrentTimesheet();

        notifyListeners();
      } else {
        _setError('Erreur lors du chargement des timesheets');
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
    */
  }

  /// Charge plus de timesheets (pagination)
  Future<void> loadMoreTimesheets() async {
    if (!_isLoading && _hasMoreData) {
      await loadTimesheets();
    }
  }

  /// Pointage d'entrée
  Future<bool> clockIn({
    required double latitude,
    required double longitude,
    String? comment,
  }) async {
    _setLoading(true);
    _clearError();

    // MODE DEMO - Simulation du pointage
    await Future.delayed(Duration(seconds: 1));
    
    final now = DateTime.now();
    final demoTimesheet = {
      'id': DateTime.now().millisecondsSinceEpoch,
      'user_id': 1,
      'date_pointage': now.toIso8601String(),
      'heure_debut': now.toIso8601String(),
      'heure_fin': null,
      'heures_travaillees_minutes': 0,
      'latitude': latitude,
      'longitude': longitude,
      'status': 'draft',
      'commentaire': comment,
      'created_at': now.toIso8601String(),
      'updated_at': now.toIso8601String(),
    };
    
    _currentTimesheet = Timesheet.fromJson(demoTimesheet);
    
    // Ajouter à la liste
    final existingIndex = _timesheets.indexWhere(
      (t) => t.datePointage.day == _currentTimesheet!.datePointage.day &&
             t.datePointage.month == _currentTimesheet!.datePointage.month &&
             t.datePointage.year == _currentTimesheet!.datePointage.year,
    );
    
    if (existingIndex == -1) {
      _timesheets.insert(0, _currentTimesheet!);
    } else {
      _timesheets[existingIndex] = _currentTimesheet!;
    }

    notifyListeners();
    _setLoading(false);
    return true;
    
    /* API CALL DISABLED FOR DEMO
    try {
      final clockInData = {
        'latitude': latitude,
        'longitude': longitude,
        'comment': comment,
        'timestamp': DateTime.now().toIso8601String(),
      };

      final response = await ApiService.clockIn(clockInData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        final timesheetData = response.data;
        _currentTimesheet = Timesheet.fromJson(timesheetData);
        
        // Ajouter à la liste si pas déjà présent
        final existingIndex = _timesheets.indexWhere(
          (t) => t.id == _currentTimesheet!.id,
        );
        
        if (existingIndex == -1) {
          _timesheets.insert(0, _currentTimesheet!);
        } else {
          _timesheets[existingIndex] = _currentTimesheet!;
        }

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
    */
  }

  /// Pointage de sortie
  Future<bool> clockOut({
    required double latitude,
    required double longitude,
    String? comment,
  }) async {
    if (_currentTimesheet == null) {
      _setError('Aucun pointage d\'entrée trouvé');
      return false;
    }

    _setLoading(true);
    _clearError();

    // MODE DEMO - Simulation du pointage de sortie
    await Future.delayed(Duration(seconds: 1));
    
    final now = DateTime.now();
    final clockIn = _currentTimesheet!.heureDebut!;
    final hoursWorked = now.difference(clockIn).inMinutes;
    
    final updatedTimesheet = {
      'id': _currentTimesheet!.id,
      'user_id': _currentTimesheet!.userId,
      'date_pointage': _currentTimesheet!.datePointage.toIso8601String(),
      'heure_debut': clockIn.toIso8601String(),
      'heure_fin': now.toIso8601String(),
      'heures_travaillees_minutes': hoursWorked,
      'latitude': latitude,
      'longitude': longitude,
      'status': 'completed',
      'commentaire': comment ?? _currentTimesheet!.commentaire,
      'created_at': _currentTimesheet!.createdAt.toIso8601String(),
      'updated_at': now.toIso8601String(),
    };
    
    _currentTimesheet = Timesheet.fromJson(updatedTimesheet);
    
    // Mettre à jour dans la liste
    final index = _timesheets.indexWhere(
      (t) => t.id == _currentTimesheet!.id,
    );
    
    if (index != -1) {
      _timesheets[index] = _currentTimesheet!;
    }

    notifyListeners();
    _setLoading(false);
    return true;
    
    /* API CALL DISABLED FOR DEMO
    try {
      final clockOutData = {
        'latitude': latitude,
        'longitude': longitude,
        'comment': comment,
        'timestamp': DateTime.now().toIso8601String(),
      };

      final response = await ApiService.clockOut(
        _currentTimesheet!.id,
        clockOutData,
      );

      if (response.statusCode == 200) {
        final timesheetData = response.data;
        final updatedTimesheet = Timesheet.fromJson(timesheetData);
        
        // Mettre à jour le timesheet actuel
        _currentTimesheet = updatedTimesheet;
        
        // Mettre à jour dans la liste
        final index = _timesheets.indexWhere(
          (t) => t.id == updatedTimesheet.id,
        );
        
        if (index != -1) {
          _timesheets[index] = updatedTimesheet;
        }

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
    */
  }

  /// Crée un nouveau timesheet
  Future<bool> createTimesheet(Map<String, dynamic> timesheetData) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await ApiService.createTimesheet(timesheetData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        final newTimesheet = Timesheet.fromJson(response.data);
        _timesheets.insert(0, newTimesheet);
        notifyListeners();
        return true;
      } else {
        _setError('Erreur lors de la création du timesheet');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Met à jour le timesheet actuel
  void _updateCurrentTimesheet() {
    final today = DateTime.now();
    Timesheet? todayTimesheet;
    
    try {
      todayTimesheet = _timesheets.firstWhere(
        (t) => t.datePointage.year == today.year &&
               t.datePointage.month == today.month &&
               t.datePointage.day == today.day,
      );
    } catch (e) {
      todayTimesheet = null;
    }

    _currentTimesheet = todayTimesheet;
  }

  /// Obtient les timesheets d'une période
  List<Timesheet> getTimesheetsForPeriod(DateTime start, DateTime end) {
    return _timesheets.where((t) {
      return t.datePointage.isAfter(start.subtract(const Duration(days: 1))) &&
             t.datePointage.isBefore(end.add(const Duration(days: 1)));
    }).toList();
  }

  /// Obtient les timesheets de la semaine
  List<Timesheet> getThisWeekTimesheets() {
    final now = DateTime.now();
    final startOfWeek = now.subtract(Duration(days: now.weekday - 1));
    final endOfWeek = startOfWeek.add(const Duration(days: 6));
    
    return getTimesheetsForPeriod(startOfWeek, endOfWeek);
  }

  /// Obtient les timesheets du mois
  List<Timesheet> getThisMonthTimesheets() {
    final now = DateTime.now();
    final startOfMonth = DateTime(now.year, now.month, 1);
    final endOfMonth = DateTime(now.year, now.month + 1, 0);
    
    return getTimesheetsForPeriod(startOfMonth, endOfMonth);
  }

  /// Calcule le total des heures travaillées
  double getTotalHoursWorked(List<Timesheet> timesheets) {
    return timesheets.fold(0.0, (total, timesheet) {
      return total + timesheet.heuresTravaillees;
    });
  }

  /// Calcule le total des heures supplémentaires
  double getTotalOvertimeHours(List<Timesheet> timesheets) {
    return timesheets.fold(0.0, (total, timesheet) {
      return total + timesheet.heuresSupplementaires;
    });
  }

  /// Obtient les statistiques des timesheets
  Map<String, dynamic> getTimesheetStatistics() {
    final thisWeek = getThisWeekTimesheets();
    final thisMonth = getThisMonthTimesheets();
    
    return {
      'thisWeek': {
        'totalHours': getTotalHoursWorked(thisWeek),
        'overtimeHours': getTotalOvertimeHours(thisWeek),
        'daysWorked': thisWeek.length,
      },
      'thisMonth': {
        'totalHours': getTotalHoursWorked(thisMonth),
        'overtimeHours': getTotalOvertimeHours(thisMonth),
        'daysWorked': thisMonth.length,
      },
      'totalTimesheets': _timesheets.length,
    };
  }

  /// Rafraîchit les données
  Future<void> refresh() async {
    await loadTimesheets(refresh: true);
  }

  /// Efface les données
  void clear() {
    _timesheets.clear();
    _currentTimesheet = null;
    _currentPage = 1;
    _hasMoreData = true;
    _clearError();
    notifyListeners();
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

  @override
  void dispose() {
    super.dispose();
  }
}