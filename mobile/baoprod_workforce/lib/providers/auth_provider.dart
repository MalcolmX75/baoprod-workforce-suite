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
    
    try {
      final response = await ApiService.login(email, password);
      
      if (response.statusCode == 200) {
        final data = response.data;
        
        // Save token
        if (data['token'] != null) {
          await ApiService.setAuthToken(data['token']);
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
  }
  
  Future<bool> register(Map<String, dynamic> userData) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await ApiService.register(userData);
      
      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        
        // Save token if provided
        if (data['token'] != null) {
          await ApiService.setAuthToken(data['token']);
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
        final userData = response.data;
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