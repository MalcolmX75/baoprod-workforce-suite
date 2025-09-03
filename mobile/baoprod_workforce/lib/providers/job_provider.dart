import 'package:flutter/material.dart';
import '../models/job.dart';
import '../services/api_service.dart';
import '../utils/constants.dart';

class JobProvider extends ChangeNotifier {
  List<Job> _jobs = [];
  List<Job> _applications = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMoreData = true;
  
  List<Job> get jobs => _jobs;
  List<Job> get applications => _applications;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMoreData => _hasMoreData;
  
  /// Charge la liste des emplois
  Future<void> loadJobs({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMoreData = true;
      _jobs.clear();
    }
    
    if (!_hasMoreData) return;
    
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.getJobs(
        page: _currentPage,
        limit: 10,
      );
      
      if (response.statusCode == 200) {
        final data = response.data;
        final List<dynamic> jobsData = data['data'] ?? [];
        
        final List<Job> newJobs = jobsData
            .map((jobData) => Job.fromJson(jobData))
            .toList();
        
        if (refresh) {
          _jobs = newJobs;
        } else {
          _jobs.addAll(newJobs);
        }
        
        // Vérifier s'il y a plus de données
        _hasMoreData = newJobs.length == 10;
        _currentPage++;
        
        notifyListeners();
      } else {
        _setError('Erreur lors du chargement des emplois');
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Charge plus d'emplois (pagination)
  Future<void> loadMoreJobs() async {
    if (!_isLoading && _hasMoreData) {
      await loadJobs();
    }
  }
  
  /// Charge un emploi spécifique
  Future<Job?> loadJob(int jobId) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.getJob(jobId);
      
      if (response.statusCode == 200) {
        final jobData = response.data;
        final job = Job.fromJson(jobData);
        
        // Mettre à jour l'emploi dans la liste si il existe
        final index = _jobs.indexWhere((j) => j.id == jobId);
        if (index != -1) {
          _jobs[index] = job;
          notifyListeners();
        }
        
        return job;
      } else {
        _setError('Erreur lors du chargement de l\'emploi');
        return null;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return null;
    } finally {
      _setLoading(false);
    }
  }
  
  /// Charge les candidatures de l'utilisateur
  Future<void> loadApplications({bool refresh = false}) async {
    if (refresh) {
      _applications.clear();
    }
    
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.getApplications();
      
      if (response.statusCode == 200) {
        final data = response.data;
        final List<dynamic> applicationsData = data['data'] ?? [];
        
        _applications = applicationsData
            .map((appData) => Job.fromJson(appData))
            .toList();
        
        notifyListeners();
      } else {
        _setError('Erreur lors du chargement des candidatures');
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Postule à un emploi
  Future<bool> applyToJob(int jobId, Map<String, dynamic> applicationData) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.createApplication({
        'job_id': jobId,
        ...applicationData,
      });
      
      if (response.statusCode == 201 || response.statusCode == 200) {
        // Recharger les candidatures
        await loadApplications(refresh: true);
        return true;
      } else {
        _setError('Erreur lors de la candidature');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }
  
  /// Recherche des emplois
  Future<void> searchJobs(String query) async {
    if (query.isEmpty) {
      await loadJobs(refresh: true);
      return;
    }
    
    _setLoading(true);
    _clearError();
    
    try {
      // Note: Cette fonctionnalité dépend de l'implémentation de l'API
      // Pour l'instant, on filtre localement
      final filteredJobs = _jobs.where((job) =>
          job.title.toLowerCase().contains(query.toLowerCase()) ||
          job.description.toLowerCase().contains(query.toLowerCase()) ||
          job.location.toLowerCase().contains(query.toLowerCase())
      ).toList();
      
      _jobs = filteredJobs;
      notifyListeners();
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Filtre les emplois par catégorie
  Future<void> filterJobsByCategory(String category) async {
    _setLoading(true);
    _clearError();
    
    try {
      final filteredJobs = _jobs.where((job) =>
          job.category.toLowerCase() == category.toLowerCase()
      ).toList();
      
      _jobs = filteredJobs;
      notifyListeners();
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Filtre les emplois par localisation
  Future<void> filterJobsByLocation(String location) async {
    _setLoading(true);
    _clearError();
    
    try {
      final filteredJobs = _jobs.where((job) =>
          job.location.toLowerCase().contains(location.toLowerCase())
      ).toList();
      
      _jobs = filteredJobs;
      notifyListeners();
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Filtre les emplois par type de contrat
  Future<void> filterJobsByContractType(String contractType) async {
    _setLoading(true);
    _clearError();
    
    try {
      final filteredJobs = _jobs.where((job) =>
          job.contractType.toLowerCase() == contractType.toLowerCase()
      ).toList();
      
      _jobs = filteredJobs;
      notifyListeners();
    } catch (e) {
      _setError(_getErrorMessage(e));
    } finally {
      _setLoading(false);
    }
  }
  
  /// Obtient un emploi par ID
  Job? getJobById(int jobId) {
    try {
      return _jobs.firstWhere((job) => job.id == jobId);
    } catch (e) {
      return null;
    }
  }
  
  /// Vérifie si l'utilisateur a postulé à un emploi
  bool hasAppliedToJob(int jobId) {
    return _applications.any((app) => app.id == jobId);
  }
  
  /// Obtient les emplois favoris
  List<Job> get favoriteJobs {
    return _jobs.where((job) => job.isFavorite).toList();
  }
  
  /// Obtient les emplois récents
  List<Job> get recentJobs {
    final sortedJobs = List<Job>.from(_jobs);
    sortedJobs.sort((a, b) => b.createdAt.compareTo(a.createdAt));
    return sortedJobs.take(5).toList();
  }
  
  /// Obtient les emplois par statut
  List<Job> getJobsByStatus(String status) {
    return _jobs.where((job) => job.status == status).toList();
  }
  
  /// Obtient les statistiques des emplois
  Map<String, int> getJobStatistics() {
    final stats = <String, int>{};
    
    for (final job in _jobs) {
      stats[job.status] = (stats[job.status] ?? 0) + 1;
    }
    
    return stats;
  }
  
  /// Rafraîchit les données
  Future<void> refresh() async {
    await loadJobs(refresh: true);
    await loadApplications(refresh: true);
  }
  
  /// Efface les données
  void clear() {
    _jobs.clear();
    _applications.clear();
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