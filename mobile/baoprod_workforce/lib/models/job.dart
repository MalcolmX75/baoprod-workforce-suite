class Job {
  final int id;
  final String title;
  final String description;
  final String location;
  final String category;
  final String contractType; // 'CDI', 'CDD', 'Mission', 'Freelance'
  final String status; // 'open', 'closed', 'paused'
  final double? salaryMin;
  final double? salaryMax;
  final String? salaryCurrency;
  final String? salaryPeriod; // 'hourly', 'daily', 'monthly', 'yearly'
  final int? experienceRequired; // years
  final List<String> requiredSkills;
  final List<String> preferredSkills;
  final String? companyName;
  final String? companyLogo;
  final String? companyDescription;
  final DateTime startDate;
  final DateTime? endDate;
  final int? maxApplications;
  final int currentApplications;
  final bool isRemote;
  final bool isUrgent;
  final bool isFeatured;
  final bool isFavorite;
  final Map<String, dynamic>? metadata;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  Job({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.category,
    required this.contractType,
    required this.status,
    this.salaryMin,
    this.salaryMax,
    this.salaryCurrency,
    this.salaryPeriod,
    this.experienceRequired,
    required this.requiredSkills,
    required this.preferredSkills,
    this.companyName,
    this.companyLogo,
    this.companyDescription,
    required this.startDate,
    this.endDate,
    this.maxApplications,
    required this.currentApplications,
    required this.isRemote,
    required this.isUrgent,
    required this.isFeatured,
    required this.isFavorite,
    this.metadata,
    required this.createdAt,
    required this.updatedAt,
  });
  
  // Calculated properties
  bool get isOpen => status == 'open';
  bool get isClosed => status == 'closed';
  bool get isPaused => status == 'paused';
  
  bool get hasSalaryRange => salaryMin != null && salaryMax != null;
  String get formattedSalaryRange {
    if (!hasSalaryRange) return 'Salaire non spécifié';
    
    final currency = salaryCurrency ?? 'FCFA';
    final period = salaryPeriod ?? 'monthly';
    
    String periodText;
    switch (period) {
      case 'hourly':
        periodText = '/heure';
        break;
      case 'daily':
        periodText = '/jour';
        break;
      case 'monthly':
        periodText = '/mois';
        break;
      case 'yearly':
        periodText = '/an';
        break;
      default:
        periodText = '';
    }
    
    if (salaryMin == salaryMax) {
      return '${salaryMin!.toStringAsFixed(0)} $currency$periodText';
    } else {
      return '${salaryMin!.toStringAsFixed(0)} - ${salaryMax!.toStringAsFixed(0)} $currency$periodText';
    }
  }
  
  String get formattedExperience {
    if (experienceRequired == null) return 'Aucune expérience requise';
    if (experienceRequired == 0) return 'Débutant accepté';
    if (experienceRequired == 1) return '1 an d\'expérience';
    return '$experienceRequired ans d\'expérience';
  }
  
  String get formattedStartDate {
    final now = DateTime.now();
    final difference = startDate.difference(now).inDays;
    
    if (difference < 0) {
      return 'Déjà commencé';
    } else if (difference == 0) {
      return 'Commence aujourd\'hui';
    } else if (difference == 1) {
      return 'Commence demain';
    } else if (difference < 7) {
      return 'Commence dans $difference jours';
    } else if (difference < 30) {
      final weeks = (difference / 7).floor();
      return 'Commence dans $weeks semaine${weeks > 1 ? 's' : ''}';
    } else {
      final months = (difference / 30).floor();
      return 'Commence dans $months mois';
    }
  }
  
  String get formattedEndDate {
    if (endDate == null) return 'Contrat permanent';
    
    final now = DateTime.now();
    final difference = endDate!.difference(now).inDays;
    
    if (difference < 0) {
      return 'Expiré';
    } else if (difference == 0) {
      return 'Se termine aujourd\'hui';
    } else if (difference == 1) {
      return 'Se termine demain';
    } else if (difference < 7) {
      return 'Se termine dans $difference jours';
    } else if (difference < 30) {
      final weeks = (difference / 7).floor();
      return 'Se termine dans $weeks semaine${weeks > 1 ? 's' : ''}';
    } else {
      final months = (difference / 30).floor();
      return 'Se termine dans $months mois';
    }
  }
  
  String get formattedLocation {
    if (isRemote) {
      return 'Télétravail';
    }
    return location;
  }
  
  String get statusText {
    switch (status) {
      case 'open':
        return 'Ouvert';
      case 'closed':
        return 'Fermé';
      case 'paused':
        return 'En pause';
      default:
        return 'Inconnu';
    }
  }
  
  String get contractTypeText {
    switch (contractType) {
      case 'CDI':
        return 'Contrat à Durée Indéterminée';
      case 'CDD':
        return 'Contrat à Durée Déterminée';
      case 'Mission':
        return 'Mission';
      case 'Freelance':
        return 'Freelance';
      default:
        return contractType;
    }
  }
  
  bool get canApply {
    return isOpen && 
           (maxApplications == null || currentApplications < maxApplications!) &&
           DateTime.now().isBefore(startDate);
  }
  
  String get applicationStatus {
    if (!canApply) {
      if (isClosed) return 'Candidatures fermées';
      if (maxApplications != null && currentApplications >= maxApplications!) {
        return 'Limite de candidatures atteinte';
      }
      if (DateTime.now().isAfter(startDate)) {
        return 'Date de début dépassée';
      }
    }
    return 'Candidatures ouvertes';
  }
  
  factory Job.fromJson(Map<String, dynamic> json) {
    return Job(
      id: json['id'],
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      location: json['location'] ?? '',
      category: json['category'] ?? '',
      contractType: json['contract_type'] ?? json['contractType'] ?? 'CDD',
      status: json['status'] ?? 'open',
      salaryMin: json['salary_min']?.toDouble() ?? json['salaryMin']?.toDouble(),
      salaryMax: json['salary_max']?.toDouble() ?? json['salaryMax']?.toDouble(),
      salaryCurrency: json['salary_currency'] ?? json['salaryCurrency'],
      salaryPeriod: json['salary_period'] ?? json['salaryPeriod'],
      experienceRequired: json['experience_required'] ?? json['experienceRequired'],
      requiredSkills: List<String>.from(json['required_skills'] ?? json['requiredSkills'] ?? []),
      preferredSkills: List<String>.from(json['preferred_skills'] ?? json['preferredSkills'] ?? []),
      companyName: json['company_name'] ?? json['companyName'],
      companyLogo: json['company_logo'] ?? json['companyLogo'],
      companyDescription: json['company_description'] ?? json['companyDescription'],
      startDate: DateTime.parse(json['start_date'] ?? json['startDate']),
      endDate: json['end_date'] != null 
          ? DateTime.parse(json['end_date'] ?? json['endDate'])
          : null,
      maxApplications: json['max_applications'] ?? json['maxApplications'],
      currentApplications: json['current_applications'] ?? json['currentApplications'] ?? 0,
      isRemote: json['is_remote'] ?? json['isRemote'] ?? false,
      isUrgent: json['is_urgent'] ?? json['isUrgent'] ?? false,
      isFeatured: json['is_featured'] ?? json['isFeatured'] ?? false,
      isFavorite: json['is_favorite'] ?? json['isFavorite'] ?? false,
      metadata: json['metadata'],
      createdAt: DateTime.parse(json['created_at'] ?? json['createdAt']),
      updatedAt: DateTime.parse(json['updated_at'] ?? json['updatedAt']),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'location': location,
      'category': category,
      'contract_type': contractType,
      'status': status,
      'salary_min': salaryMin,
      'salary_max': salaryMax,
      'salary_currency': salaryCurrency,
      'salary_period': salaryPeriod,
      'experience_required': experienceRequired,
      'required_skills': requiredSkills,
      'preferred_skills': preferredSkills,
      'company_name': companyName,
      'company_logo': companyLogo,
      'company_description': companyDescription,
      'start_date': startDate.toIso8601String(),
      'end_date': endDate?.toIso8601String(),
      'max_applications': maxApplications,
      'current_applications': currentApplications,
      'is_remote': isRemote,
      'is_urgent': isUrgent,
      'is_featured': isFeatured,
      'is_favorite': isFavorite,
      'metadata': metadata,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  Job copyWith({
    int? id,
    String? title,
    String? description,
    String? location,
    String? category,
    String? contractType,
    String? status,
    double? salaryMin,
    double? salaryMax,
    String? salaryCurrency,
    String? salaryPeriod,
    int? experienceRequired,
    List<String>? requiredSkills,
    List<String>? preferredSkills,
    String? companyName,
    String? companyLogo,
    String? companyDescription,
    DateTime? startDate,
    DateTime? endDate,
    int? maxApplications,
    int? currentApplications,
    bool? isRemote,
    bool? isUrgent,
    bool? isFeatured,
    bool? isFavorite,
    Map<String, dynamic>? metadata,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Job(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      location: location ?? this.location,
      category: category ?? this.category,
      contractType: contractType ?? this.contractType,
      status: status ?? this.status,
      salaryMin: salaryMin ?? this.salaryMin,
      salaryMax: salaryMax ?? this.salaryMax,
      salaryCurrency: salaryCurrency ?? this.salaryCurrency,
      salaryPeriod: salaryPeriod ?? this.salaryPeriod,
      experienceRequired: experienceRequired ?? this.experienceRequired,
      requiredSkills: requiredSkills ?? this.requiredSkills,
      preferredSkills: preferredSkills ?? this.preferredSkills,
      companyName: companyName ?? this.companyName,
      companyLogo: companyLogo ?? this.companyLogo,
      companyDescription: companyDescription ?? this.companyDescription,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      maxApplications: maxApplications ?? this.maxApplications,
      currentApplications: currentApplications ?? this.currentApplications,
      isRemote: isRemote ?? this.isRemote,
      isUrgent: isUrgent ?? this.isUrgent,
      isFeatured: isFeatured ?? this.isFeatured,
      isFavorite: isFavorite ?? this.isFavorite,
      metadata: metadata ?? this.metadata,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
  
  @override
  String toString() {
    return 'Job(id: $id, title: $title, location: $location, status: $status, contractType: $contractType)';
  }
  
  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Job && other.id == id;
  }
  
  @override
  int get hashCode => id.hashCode;
}