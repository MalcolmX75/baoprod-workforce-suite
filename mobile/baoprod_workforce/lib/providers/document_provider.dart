import 'package:flutter/material.dart';
import '../models/document_request.dart';
import '../services/storage_service.dart';

class DocumentProvider extends ChangeNotifier {
  List<DocumentRequest> _requests = [];
  bool _isLoading = false;
  String? _error;

  List<DocumentRequest> get requests => _requests;
  List<DocumentRequest> get pendingRequests => 
      _requests.where((r) => r.status == RequestStatus.pending).toList();
  List<DocumentRequest> get readyRequests => 
      _requests.where((r) => r.status == RequestStatus.ready).toList();
  List<DocumentRequest> get completedRequests => 
      _requests.where((r) => r.status == RequestStatus.completed).toList();
      
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasError => _error != null;
  
  int get pendingCount => pendingRequests.length;
  int get readyCount => readyRequests.length;

  Future<void> loadDocumentRequests({bool refresh = false}) async {
    if (refresh) {
      _requests.clear();
    }

    _setLoading(true);
    _clearError();

    try {
      // MODE DEMO - Demandes de documents simulées
      await Future.delayed(const Duration(milliseconds: 500));
      
      final List<Map<String, dynamic>> demoRequests = [
        {
          'id': 1,
          'type': 'contract',
          'title': 'Copie contrat de travail',
          'description': 'Demande de copie de votre contrat de travail en cours avec signature électronique.',
          'status': 'ready',
          'signature_type': 'electronic',
          'request_date': DateTime.now().subtract(const Duration(days: 2)).toIso8601String(),
          'document_url': 'https://example.com/documents/contract_123.pdf',
          'is_urgent': false,
        },
        {
          'id': 2,
          'type': 'certificate',
          'title': 'Attestation de travail',
          'description': 'Attestation confirmant votre emploi actuel chez BaoProd.',
          'status': 'processing',
          'signature_type': 'none',
          'request_date': DateTime.now().subtract(const Duration(days: 5)).toIso8601String(),
          'is_urgent': true,
        },
        {
          'id': 3,
          'type': 'payslip',
          'title': 'Fiche de paie - Octobre 2024',
          'description': 'Copie de votre fiche de paie pour le mois d\'octobre 2024.',
          'status': 'completed',
          'signature_type': 'none',
          'request_date': DateTime.now().subtract(const Duration(days: 10)).toIso8601String(),
          'completed_date': DateTime.now().subtract(const Duration(days: 3)).toIso8601String(),
          'document_url': 'https://example.com/documents/payslip_oct2024.pdf',
          'is_urgent': false,
        },
        {
          'id': 4,
          'type': 'recommendation',
          'title': 'Lettre de recommandation',
          'description': 'Lettre de recommandation de votre manager pour votre prochain emploi.',
          'status': 'pending',
          'signature_type': 'physical',
          'request_date': DateTime.now().subtract(const Duration(days: 1)).toIso8601String(),
          'is_urgent': false,
        },
      ];

      _requests = demoRequests
          .map((requestData) => DocumentRequest.fromJson(requestData))
          .toList();

      // Trier par date de demande (plus récent en premier)
      _requests.sort((a, b) => b.requestDate.compareTo(a.requestDate));

      print('📄 ${_requests.length} demandes de documents chargées');
      notifyListeners();
    } catch (e) {
      _setError('Erreur lors du chargement des demandes: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> createDocumentRequest({
    required DocumentType type,
    required String title,
    required String description,
    SignatureType signatureType = SignatureType.none,
    bool isUrgent = false,
    Map<String, dynamic>? metadata,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      // MODE DEMO - Simulation de création de demande
      await Future.delayed(const Duration(milliseconds: 1000));

      final newRequest = DocumentRequest(
        id: _requests.isNotEmpty 
            ? _requests.map((r) => r.id).reduce((a, b) => a > b ? a : b) + 1
            : 1,
        type: type,
        title: title,
        description: description,
        status: RequestStatus.pending,
        signatureType: signatureType,
        requestDate: DateTime.now(),
        isUrgent: isUrgent,
        metadata: metadata,
      );

      _requests.insert(0, newRequest); // Ajouter en premier
      notifyListeners();

      await _saveToStorage();
      print('📄 Nouvelle demande de document créée: $title');
      return true;
    } catch (e) {
      _setError('Erreur lors de la création de la demande: $e');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> downloadDocument(int requestId) async {
    try {
      final request = getRequestById(requestId);
      if (request == null || !request.canDownload) {
        return false;
      }

      // MODE DEMO - Simulation de téléchargement
      await Future.delayed(const Duration(milliseconds: 500));
      
      print('📄 Document téléchargé: ${request.title}');
      return true;
    } catch (e) {
      _setError('Erreur lors du téléchargement: $e');
      return false;
    }
  }

  Future<bool> signDocumentElectronically(int requestId, String signature) async {
    try {
      final index = _requests.indexWhere((r) => r.id == requestId);
      if (index == -1) return false;

      final request = _requests[index];
      if (request.signatureType != SignatureType.electronic || 
          request.status != RequestStatus.ready) {
        return false;
      }

      // MODE DEMO - Simulation de signature électronique
      await Future.delayed(const Duration(milliseconds: 1500));

      _requests[index] = request.copyWith(
        status: RequestStatus.completed,
        completedDate: DateTime.now(),
        signedDocumentUrl: '${request.documentUrl}_signed',
        metadata: {
          ...?request.metadata,
          'electronic_signature': signature,
          'signed_at': DateTime.now().toIso8601String(),
        },
      );

      notifyListeners();
      await _saveToStorage();
      
      print('📄 Document signé électroniquement: ${request.title}');
      return true;
    } catch (e) {
      _setError('Erreur lors de la signature: $e');
      return false;
    }
  }

  Future<bool> uploadSignedDocument(int requestId, String filePath) async {
    try {
      final index = _requests.indexWhere((r) => r.id == requestId);
      if (index == -1) return false;

      final request = _requests[index];
      if (request.signatureType != SignatureType.physical || 
          request.status != RequestStatus.ready) {
        return false;
      }

      // MODE DEMO - Simulation d'upload de document scanné
      await Future.delayed(const Duration(milliseconds: 2000));

      _requests[index] = request.copyWith(
        status: RequestStatus.completed,
        completedDate: DateTime.now(),
        signedDocumentUrl: filePath,
        metadata: {
          ...?request.metadata,
          'physical_signature': true,
          'uploaded_at': DateTime.now().toIso8601String(),
        },
      );

      notifyListeners();
      await _saveToStorage();
      
      print('📄 Document scanné téléversé: ${request.title}');
      return true;
    } catch (e) {
      _setError('Erreur lors du téléversement: $e');
      return false;
    }
  }

  Future<bool> cancelRequest(int requestId) async {
    try {
      _requests.removeWhere((r) => r.id == requestId);
      notifyListeners();
      
      await _saveToStorage();
      print('📄 Demande annulée: $requestId');
      return true;
    } catch (e) {
      _setError('Erreur lors de l\'annulation: $e');
      return false;
    }
  }

  DocumentRequest? getRequestById(int id) {
    try {
      return _requests.firstWhere((r) => r.id == id);
    } catch (e) {
      return null;
    }
  }

  List<DocumentRequest> getRequestsByType(DocumentType type) {
    return _requests.where((r) => r.type == type).toList();
  }

  List<DocumentRequest> getRequestsByStatus(RequestStatus status) {
    return _requests.where((r) => r.status == status).toList();
  }

  Future<void> _saveToStorage() async {
    try {
      final requestsJson = _requests.map((r) => r.toJson()).toList();
      await StorageService.save('document_requests', requestsJson);
    } catch (e) {
      print('Erreur sauvegarde demandes: $e');
    }
  }

  Future<void> _loadFromStorage() async {
    try {
      final requestsJson = await StorageService.get<List>('document_requests');
      if (requestsJson != null) {
        _requests = (requestsJson as List)
            .map((json) => DocumentRequest.fromJson(Map<String, dynamic>.from(json)))
            .toList();
        notifyListeners();
      }
    } catch (e) {
      print('Erreur chargement demandes: $e');
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