import 'package:flutter/material.dart';
import '../models/cv.dart';
import '../services/storage_service.dart';

class CVProvider extends ChangeNotifier {
  List<CV> _cvList = [];
  bool _isLoading = false;
  String? _error;

  List<CV> get cvList => _cvList;
  List<CV> get cvs => _cvList; // Alias pour compatibility
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasError => _error != null;
  
  CV? get defaultCV {
    try {
      return _cvList.where((cv) => cv.isDefault).first;
    } catch (e) {
      return null;
    }
  }
  bool get hasCVs => _cvList.isNotEmpty;

  Future<void> loadCVs({bool refresh = false}) async {
    if (refresh) {
      _cvList.clear();
    }
    _setLoading(true);
    _clearError();
    
    try {
      // MODE DEMO - CV simulés
      await Future.delayed(const Duration(milliseconds: 500));
      
      final List<Map<String, dynamic>> demoCVsData = [
        {
          'id': 1,
          'name': 'CV Développeur Senior',
          'file_name': 'cv_dev_senior.pdf',
          'file_path': '/storage/cvs/cv_dev_senior.pdf',
          'created_at': DateTime.now().subtract(const Duration(days: 30)).toIso8601String(),
          'updated_at': DateTime.now().subtract(const Duration(days: 5)).toIso8601String(),
          'is_default': true,
          'file_size': 245760, // 240 KB
          'file_extension': 'pdf',
        },
        {
          'id': 2,
          'name': 'CV Général',
          'file_name': 'cv_general.pdf',
          'file_path': '/storage/cvs/cv_general.pdf',
          'created_at': DateTime.now().subtract(const Duration(days: 60)).toIso8601String(),
          'updated_at': DateTime.now().subtract(const Duration(days: 10)).toIso8601String(),
          'is_default': false,
          'file_size': 198640, // 194 KB
          'file_extension': 'pdf',
        },
        {
          'id': 3,
          'name': 'CV Comptabilité',
          'file_name': 'cv_comptable.docx',
          'file_path': '/storage/cvs/cv_comptable.docx',
          'created_at': DateTime.now().subtract(const Duration(days: 45)).toIso8601String(),
          'updated_at': DateTime.now().subtract(const Duration(days: 15)).toIso8601String(),
          'is_default': false,
          'file_size': 156800, // 153 KB
          'file_extension': 'docx',
        },
      ];

      _cvList = demoCVsData
          .map((cvData) => CV.fromJson(cvData))
          .toList();

      notifyListeners();
      print('📄 ${_cvList.length} CVs chargés');
    } catch (e) {
      _setError('Erreur lors du chargement des CVs: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> setDefaultCV(int cvId) async {
    try {
      // Retirer le statut par défaut de tous les CVs
      for (int i = 0; i < _cvList.length; i++) {
        _cvList[i] = _cvList[i].copyWith(isDefault: false);
      }
      
      // Définir le nouveau CV par défaut
      final cvIndex = _cvList.indexWhere((cv) => cv.id == cvId);
      if (cvIndex != -1) {
        _cvList[cvIndex] = _cvList[cvIndex].copyWith(isDefault: true);
        notifyListeners();
        
        // Sauvegarder en local
        await _saveToStorage();
        print('📄 CV "${_cvList[cvIndex].name}" défini comme CV par défaut');
        return true;
      }
      return false;
    } catch (e) {
      _setError('Erreur lors de la définition du CV par défaut: $e');
      return false;
    }
  }

  CV? getCVById(int id) {
    try {
      return _cvList.firstWhere((cv) => cv.id == id);
    } catch (e) {
      return null;
    }
  }

  Future<bool> deleteCV(int cvId) async {
    try {
      final cvIndex = _cvList.indexWhere((cv) => cv.id == cvId);
      if (cvIndex != -1) {
        final cv = _cvList[cvIndex];
        _cvList.removeAt(cvIndex);
        
        // Si c'était le CV par défaut, définir un autre comme défaut
        if (cv.isDefault && _cvList.isNotEmpty) {
          _cvList[0] = _cvList[0].copyWith(isDefault: true);
        }
        
        notifyListeners();
        await _saveToStorage();
        print('📄 CV "${cv.name}" supprimé');
        return true;
      }
      return false;
    } catch (e) {
      _setError('Erreur lors de la suppression du CV: $e');
      return false;
    }
  }

  Future<bool> addCV({
    required String name,
    required String fileName,
    required String filePath,
    required int fileSize,
    required String fileExtension,
    bool setAsDefault = false,
  }) async {
    try {
      final newId = _cvList.isNotEmpty 
          ? _cvList.map((cv) => cv.id).reduce((a, b) => a > b ? a : b) + 1
          : 1;

      // Si c'est le premier CV ou si demandé, le définir comme défaut
      final isDefault = setAsDefault || _cvList.isEmpty;
      
      // Si on définit comme défaut, retirer le statut des autres
      if (isDefault) {
        for (int i = 0; i < _cvList.length; i++) {
          _cvList[i] = _cvList[i].copyWith(isDefault: false);
        }
      }

      final newCV = CV(
        id: newId,
        name: name,
        fileName: fileName,
        filePath: filePath,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
        isDefault: isDefault,
        fileSize: fileSize,
        fileExtension: fileExtension,
      );

      _cvList.add(newCV);
      notifyListeners();
      await _saveToStorage();
      
      print('📄 CV "${name}" ajouté ${isDefault ? '(défaut)' : ''}');
      return true;
    } catch (e) {
      _setError('Erreur lors de l\'ajout du CV: $e');
      return false;
    }
  }

  Future<bool> updateDefaultCV(int cvId) async {
    try {
      // Retirer le statut défaut de tous les CVs
      for (int i = 0; i < _cvList.length; i++) {
        _cvList[i] = _cvList[i].copyWith(isDefault: false);
      }
      
      // Définir le CV sélectionné comme défaut
      final cvIndex = _cvList.indexWhere((cv) => cv.id == cvId);
      if (cvIndex != -1) {
        _cvList[cvIndex] = _cvList[cvIndex].copyWith(isDefault: true);
        notifyListeners();
        await _saveToStorage();
        print('📄 CV "${_cvList[cvIndex].name}" défini comme défaut');
        return true;
      }
      return false;
    } catch (e) {
      _setError('Erreur lors de la définition du CV par défaut: $e');
      return false;
    }
  }

  Future<void> _saveToStorage() async {
    try {
      final cvListJson = _cvList.map((cv) => cv.toJson()).toList();
      await StorageService.save('cvs', cvListJson);
    } catch (e) {
      print('Erreur sauvegarde CVs: $e');
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
  }

  @override
  void dispose() {
    super.dispose();
  }
}