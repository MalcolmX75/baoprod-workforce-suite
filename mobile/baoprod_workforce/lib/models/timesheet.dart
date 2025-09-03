class Timesheet {
  final int id;
  final int userId;
  final int? jobId;
  final DateTime datePointage;
  final DateTime? heureDebut;
  final DateTime? heureFin;
  final int? heuresTravailleesMinutes;
  final int? heuresSupplementairesMinutes;
  final int? heuresNuitMinutes;
  final int? heuresDimancheMinutes;
  final double? latitude;
  final double? longitude;
  final String? adresse;
  final String? commentaire;
  final String status; // 'draft', 'submitted', 'approved', 'rejected'
  final Map<String, dynamic>? configurationPays;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  Timesheet({
    required this.id,
    required this.userId,
    this.jobId,
    required this.datePointage,
    this.heureDebut,
    this.heureFin,
    this.heuresTravailleesMinutes,
    this.heuresSupplementairesMinutes,
    this.heuresNuitMinutes,
    this.heuresDimancheMinutes,
    this.latitude,
    this.longitude,
    this.adresse,
    this.commentaire,
    required this.status,
    this.configurationPays,
    required this.createdAt,
    required this.updatedAt,
  });
  
  // Calculated properties
  double get heuresTravaillees => (heuresTravailleesMinutes ?? 0) / 60.0;
  double get heuresSupplementaires => (heuresSupplementairesMinutes ?? 0) / 60.0;
  double get heuresNuit => (heuresNuitMinutes ?? 0) / 60.0;
  double get heuresDimanche => (heuresDimancheMinutes ?? 0) / 60.0;
  
  bool get isCompleted => heureDebut != null && heureFin != null;
  bool get isClockInOnly => heureDebut != null && heureFin == null;
  bool get isDraft => status == 'draft';
  bool get isSubmitted => status == 'submitted';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';
  
  String get formattedHeuresTravaillees {
    final hours = heuresTravailleesMinutes ?? 0;
    final h = hours ~/ 60;
    final m = hours % 60;
    return '${h}h ${m.toString().padLeft(2, '0')}min';
  }
  
  String get formattedHeuresSupplementaires {
    final hours = heuresSupplementairesMinutes ?? 0;
    final h = hours ~/ 60;
    final m = hours % 60;
    return '${h}h ${m.toString().padLeft(2, '0')}min';
  }
  
  factory Timesheet.fromJson(Map<String, dynamic> json) {
    return Timesheet(
      id: json['id'],
      userId: json['user_id'] ?? json['userId'],
      jobId: json['job_id'] ?? json['jobId'],
      datePointage: DateTime.parse(json['date_pointage'] ?? json['datePointage']),
      heureDebut: json['heure_debut'] != null 
          ? DateTime.parse(json['heure_debut'] ?? json['heureDebut'])
          : null,
      heureFin: json['heure_fin'] != null 
          ? DateTime.parse(json['heure_fin'] ?? json['heureFin'])
          : null,
      heuresTravailleesMinutes: json['heures_travaillees_minutes'] ?? json['heuresTravailleesMinutes'],
      heuresSupplementairesMinutes: json['heures_supplementaires_minutes'] ?? json['heuresSupplementairesMinutes'],
      heuresNuitMinutes: json['heures_nuit_minutes'] ?? json['heuresNuitMinutes'],
      heuresDimancheMinutes: json['heures_dimanche_minutes'] ?? json['heuresDimancheMinutes'],
      latitude: json['latitude']?.toDouble(),
      longitude: json['longitude']?.toDouble(),
      adresse: json['adresse'],
      commentaire: json['commentaire'],
      status: json['status'] ?? 'draft',
      configurationPays: json['configuration_pays'] ?? json['configurationPays'],
      createdAt: DateTime.parse(json['created_at'] ?? json['createdAt']),
      updatedAt: DateTime.parse(json['updated_at'] ?? json['updatedAt']),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'job_id': jobId,
      'date_pointage': datePointage.toIso8601String().split('T')[0],
      'heure_debut': heureDebut?.toIso8601String(),
      'heure_fin': heureFin?.toIso8601String(),
      'heures_travaillees_minutes': heuresTravailleesMinutes,
      'heures_supplementaires_minutes': heuresSupplementairesMinutes,
      'heures_nuit_minutes': heuresNuitMinutes,
      'heures_dimanche_minutes': heuresDimancheMinutes,
      'latitude': latitude,
      'longitude': longitude,
      'adresse': adresse,
      'commentaire': commentaire,
      'status': status,
      'configuration_pays': configurationPays,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  Timesheet copyWith({
    int? id,
    int? userId,
    int? jobId,
    DateTime? datePointage,
    DateTime? heureDebut,
    DateTime? heureFin,
    int? heuresTravailleesMinutes,
    int? heuresSupplementairesMinutes,
    int? heuresNuitMinutes,
    int? heuresDimancheMinutes,
    double? latitude,
    double? longitude,
    String? adresse,
    String? commentaire,
    String? status,
    Map<String, dynamic>? configurationPays,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Timesheet(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      jobId: jobId ?? this.jobId,
      datePointage: datePointage ?? this.datePointage,
      heureDebut: heureDebut ?? this.heureDebut,
      heureFin: heureFin ?? this.heureFin,
      heuresTravailleesMinutes: heuresTravailleesMinutes ?? this.heuresTravailleesMinutes,
      heuresSupplementairesMinutes: heuresSupplementairesMinutes ?? this.heuresSupplementairesMinutes,
      heuresNuitMinutes: heuresNuitMinutes ?? this.heuresNuitMinutes,
      heuresDimancheMinutes: heuresDimancheMinutes ?? this.heuresDimancheMinutes,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      adresse: adresse ?? this.adresse,
      commentaire: commentaire ?? this.commentaire,
      status: status ?? this.status,
      configurationPays: configurationPays ?? this.configurationPays,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
  
  @override
  String toString() {
    return 'Timesheet(id: $id, date: $datePointage, status: $status, hours: $formattedHeuresTravaillees)';
  }
  
  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Timesheet && other.id == id;
  }
  
  @override
  int get hashCode => id.hashCode;
}