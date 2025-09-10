import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../utils/constants.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _isLoading = false;
  String? _error;
  bool _isAuthenticated = false;
  
  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _isAuthenticated;
  
  AuthProvider() {
    _loadUserFromStorage();
  }
  
  Future<void> _loadUserFromStorage() async {
    try {
      final userData = StorageService.getUser();
      if (userData != null) {
        _user = User.fromJson(userData);
        _isAuthenticated = true;
        notifyListeners();
      }
    } catch (e) {
      print('Error loading user from storage: $e');
    }
  }
  
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();
    
    // MODE DEMO - Simulation de connexion pour test
    await Future.delayed(Duration(seconds: 1)); // Simulate network delay
    
    // Créer un utilisateur de démonstration basé sur l'email
    Map<String, dynamic> demoUser = {
      'id': 1,
      'email': email,
      'first_name': email.contains('manolinis') ? 'Emmanuel' :
                    email.contains('admin') ? 'Admin' : 
                    email.contains('employer') ? 'Jean' :
                    email.contains('marie') ? 'Marie' : 'Pierre',
      'last_name': email.contains('manolinis') ? 'Manolinis' :
                   email.contains('admin') ? 'BaoProd' :
                   email.contains('employer') ? 'Dupont' :
                   email.contains('marie') ? 'Mba' : 'Nguema',
      'type': email.contains('admin') ? 'admin' :
              email.contains('employer') ? 'employer' : 'candidate',
      'phone': email.contains('manolinis') ? '+241 06 12 34 56' : '+241 01 23 45 67',
      'avatar': null,
      'status': 'active',
      'tenant_id': 1,
      'created_at': DateTime.now().subtract(Duration(days: 90)).toIso8601String(),
      'updated_at': DateTime.now().toIso8601String(),
      'settings': <String, dynamic>{
        if (email.contains('manolinis'))
          'preferred_language': 'fr',
          'notifications_enabled': true,
          'theme': 'dark'
      },
      'profile_data': <String, dynamic>{
        if (email.contains('manolinis')) ...{
          'skills': ['Flutter', 'Dart', 'PHP', 'Laravel', 'JavaScript', 'MySQL', 'Git'],
          'experience_years': 5,
          'education': 'Master en Informatique',
          'location': 'Libreville, Gabon',
          'bio': 'Développeur Flutter passionné avec 5 ans d\'expérience dans le développement mobile et web.',
          'github': 'manolinis',
          'linkedin': 'emmanuel-manolinis'
        } else if (email.contains('marie'))
          'skills': ['PHP', 'Laravel', 'JavaScript', 'MySQL']
        else if (email.contains('pierre'))
          'skills': ['Comptabilité', 'Gestion', 'Excel', 'Sage']
        else if (email.contains('employer'))
          'company_name': 'Entreprise Gabonaise SARL'
      }
    };
    
    _user = User.fromJson(demoUser);
    await StorageService.saveUser(demoUser);
    _isAuthenticated = true;
    
    notifyListeners();
    _setLoading(false);
    return true;
    
    /* API CALL DISABLED FOR DEMO
    try {
      final response = await ApiService.login(email, password);
      
      if (response.statusCode == 200) {
        final data = response.data['data'];
        
        // Save token
        if (data['token'] != null) {
          await ApiService.setAuthToken(data['token']);
        }
        
        // Save Tenant ID
        if (data['tenant'] != null && data['tenant']['id'] != null) {
          final tenantId = data['tenant']['id'].toString();
          await ApiService.setTenant(tenantId);
        }
        
        // Save user data
        if (data['user'] != null) {
          _user = User.fromJson(data['user']);
          await StorageService.saveUser(data['user']);
          _isAuthenticated = true;
        }
        
        notifyListeners();
        return true;
      } else {
        _setError('Email ou mot de passe incorrect');
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
  
  Future<bool> register(Map<String, dynamic> userData) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.register(userData);
      
      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data['data'];
        
        // Save token if provided
        if (data['token'] != null) {
          await ApiService.setAuthToken(data['token']);
        }

        // Save Tenant ID if provided
        if (data['tenant'] != null && data['tenant']['id'] != null) {
          final tenantId = data['tenant']['id'].toString();
          await ApiService.setTenant(tenantId);
        }
        
        // Save user data
        if (data['user'] != null) {
          _user = User.fromJson(data['user']);
          await StorageService.saveUser(data['user']);
          _isAuthenticated = true;
        }
        
        notifyListeners();
        return true;
      } else {
        _setError('Erreur lors de l\'inscription');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }
  
  Future<void> logout() async {
    _setLoading(true);
    
    try {
      // Call logout API
      await ApiService.logout();
    } catch (e) {
      print('Error during logout API call: $e');
    } finally {
      // Clear local data regardless of API call result
      await _clearUserData();
    }
  }
  
  Future<void> _clearUserData() async {
    _user = null;
    _isAuthenticated = false;
    _clearError();
    
    // Clear storage
    await ApiService.clearAuthToken();
    await ApiService.clearTenant();
    await StorageService.clearUser();
    await StorageService.clearTimesheetData();
    
    _setLoading(false);
    notifyListeners();
  }
  
  Future<bool> refreshProfile() async {
    if (!_isAuthenticated) return false;
    
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.getProfile();
      
      if (response.statusCode == 200) {
        final userData = response.data['data']['user'];
        _user = User.fromJson(userData);
        await StorageService.saveUser(userData);
        notifyListeners();
        return true;
      } else {
        _setError('Erreur lors du chargement du profil');
        return false;
      }
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
    }
  }
  
  Future<bool> updateProfile(Map<String, dynamic> updates) async {
    if (!_isAuthenticated) return false;
    
    _setLoading(true);
    _clearError();
    
    try {
      // This would be implemented when the API endpoint is available
      // final response = await ApiService.updateProfile(updates);
      
      // For now, update locally
      if (_user != null) {
        final updatedUserData = _user!.toJson();
        updates.forEach((key, value) {
          updatedUserData[key] = value;
        });
        
        _user = User.fromJson(updatedUserData);
        await StorageService.saveUser(updatedUserData);
        notifyListeners();
        return true;
      }
      
      return false;
    } catch (e) {
      _setError(_getErrorMessage(e));
      return false;
    } finally {
      _setLoading(false);
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
  bool get isAdmin => _user?.isAdmin ?? false;
  bool get isEmployer => _user?.isEmployer ?? false;
  bool get isEmployee => _user?.isEmployee ?? false;
  
  String get userDisplayName => _user?.displayName ?? 'Utilisateur';
  String get userEmail => _user?.email ?? '';
  String? get userAvatar => _user?.avatar;
  
  @override
  void dispose() {
    super.dispose();
  }
}