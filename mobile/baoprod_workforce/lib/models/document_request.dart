import 'package:flutter/material.dart';

enum DocumentType {
  contract, // Contrat de travail
  payslip, // Fiche de paie
  certificate, // Attestation de travail
  recommendation, // Lettre de recommandation
  other, // Autre document
}

enum RequestStatus {
  pending, // En attente
  processing, // En cours de traitement
  ready, // Prêt pour téléchargement/signature
  completed, // Terminé
  rejected, // Rejeté
}

enum SignatureType {
  none, // Pas de signature requise
  electronic, // Signature électronique
  physical, // Signature physique (scan)
}

class DocumentRequest {
  final int id;
  final DocumentType type;
  final String title;
  final String description;
  final RequestStatus status;
  final SignatureType signatureType;
  final DateTime requestDate;
  final DateTime? completedDate;
  final DateTime? expiryDate;
  final String? rejectionReason;
  final String? documentUrl;
  final String? signedDocumentUrl;
  final bool isUrgent;
  final Map<String, dynamic>? metadata;

  DocumentRequest({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.status,
    this.signatureType = SignatureType.none,
    required this.requestDate,
    this.completedDate,
    this.expiryDate,
    this.rejectionReason,
    this.documentUrl,
    this.signedDocumentUrl,
    this.isUrgent = false,
    this.metadata,
  });

  String get statusText {
    switch (status) {
      case RequestStatus.pending:
        return 'En attente';
      case RequestStatus.processing:
        return 'En cours';
      case RequestStatus.ready:
        return 'Prêt';
      case RequestStatus.completed:
        return 'Terminé';
      case RequestStatus.rejected:
        return 'Rejeté';
    }
  }

  String get typeText {
    switch (type) {
      case DocumentType.contract:
        return 'Contrat de travail';
      case DocumentType.payslip:
        return 'Fiche de paie';
      case DocumentType.certificate:
        return 'Attestation de travail';
      case DocumentType.recommendation:
        return 'Lettre de recommandation';
      case DocumentType.other:
        return 'Autre document';
    }
  }

  String get signatureTypeText {
    switch (signatureType) {
      case SignatureType.none:
        return 'Aucune signature';
      case SignatureType.electronic:
        return 'Signature électronique';
      case SignatureType.physical:
        return 'Signature physique';
    }
  }

  Color get statusColor {
    switch (status) {
      case RequestStatus.pending:
        return const Color(0xFFFF9800); // Orange
      case RequestStatus.processing:
        return const Color(0xFF2196F3); // Bleu
      case RequestStatus.ready:
        return const Color(0xFF4CAF50); // Vert
      case RequestStatus.completed:
        return const Color(0xFF9C27B0); // Violet
      case RequestStatus.rejected:
        return const Color(0xFFF44336); // Rouge
    }
  }

  bool get canDownload => documentUrl != null && (status == RequestStatus.ready || status == RequestStatus.completed);
  bool get needsSignature => signatureType != SignatureType.none && status == RequestStatus.ready;
  bool get isExpired => expiryDate != null && DateTime.now().isAfter(expiryDate!);
  
  String get formattedRequestDate {
    final now = DateTime.now();
    final difference = now.difference(requestDate).inDays;
    
    if (difference == 0) {
      return "Aujourd'hui";
    } else if (difference == 1) {
      return "Hier";
    } else if (difference < 7) {
      return "Il y a $difference jours";
    } else {
      return "${requestDate.day}/${requestDate.month}/${requestDate.year}";
    }
  }

  factory DocumentRequest.fromJson(Map<String, dynamic> json) {
    return DocumentRequest(
      id: json['id'] ?? 0,
      type: _parseDocumentType(json['type']),
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: _parseRequestStatus(json['status']),
      signatureType: _parseSignatureType(json['signature_type']),
      requestDate: json['request_date'] != null 
          ? DateTime.parse(json['request_date'])
          : DateTime.now(),
      completedDate: json['completed_date'] != null 
          ? DateTime.parse(json['completed_date'])
          : null,
      expiryDate: json['expiry_date'] != null 
          ? DateTime.parse(json['expiry_date'])
          : null,
      rejectionReason: json['rejection_reason'],
      documentUrl: json['document_url'],
      signedDocumentUrl: json['signed_document_url'],
      isUrgent: json['is_urgent'] ?? false,
      metadata: json['metadata'],
    );
  }

  static DocumentType _parseDocumentType(String? type) {
    switch (type?.toLowerCase()) {
      case 'contract':
        return DocumentType.contract;
      case 'payslip':
        return DocumentType.payslip;
      case 'certificate':
        return DocumentType.certificate;
      case 'recommendation':
        return DocumentType.recommendation;
      default:
        return DocumentType.other;
    }
  }

  static RequestStatus _parseRequestStatus(String? status) {
    switch (status?.toLowerCase()) {
      case 'pending':
        return RequestStatus.pending;
      case 'processing':
        return RequestStatus.processing;
      case 'ready':
        return RequestStatus.ready;
      case 'completed':
        return RequestStatus.completed;
      case 'rejected':
        return RequestStatus.rejected;
      default:
        return RequestStatus.pending;
    }
  }

  static SignatureType _parseSignatureType(String? type) {
    switch (type?.toLowerCase()) {
      case 'electronic':
        return SignatureType.electronic;
      case 'physical':
        return SignatureType.physical;
      default:
        return SignatureType.none;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type.name,
      'title': title,
      'description': description,
      'status': status.name,
      'signature_type': signatureType.name,
      'request_date': requestDate.toIso8601String(),
      'completed_date': completedDate?.toIso8601String(),
      'expiry_date': expiryDate?.toIso8601String(),
      'rejection_reason': rejectionReason,
      'document_url': documentUrl,
      'signed_document_url': signedDocumentUrl,
      'is_urgent': isUrgent,
      'metadata': metadata,
    };
  }

  DocumentRequest copyWith({
    int? id,
    DocumentType? type,
    String? title,
    String? description,
    RequestStatus? status,
    SignatureType? signatureType,
    DateTime? requestDate,
    DateTime? completedDate,
    DateTime? expiryDate,
    String? rejectionReason,
    String? documentUrl,
    String? signedDocumentUrl,
    bool? isUrgent,
    Map<String, dynamic>? metadata,
  }) {
    return DocumentRequest(
      id: id ?? this.id,
      type: type ?? this.type,
      title: title ?? this.title,
      description: description ?? this.description,
      status: status ?? this.status,
      signatureType: signatureType ?? this.signatureType,
      requestDate: requestDate ?? this.requestDate,
      completedDate: completedDate ?? this.completedDate,
      expiryDate: expiryDate ?? this.expiryDate,
      rejectionReason: rejectionReason ?? this.rejectionReason,
      documentUrl: documentUrl ?? this.documentUrl,
      signedDocumentUrl: signedDocumentUrl ?? this.signedDocumentUrl,
      isUrgent: isUrgent ?? this.isUrgent,
      metadata: metadata ?? this.metadata,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is DocumentRequest && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}