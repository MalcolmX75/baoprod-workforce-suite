class User {
  final int id;
  final String firstName;
  final String lastName;
  final String email;
  final String? phone;
  final String? avatar;
  final String type; // 'admin', 'employer', 'employee'
  final String status; // 'active', 'inactive', 'pending'
  final int? tenantId;
  final Map<String, dynamic>? settings;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  User({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    this.phone,
    this.avatar,
    required this.type,
    required this.status,
    this.tenantId,
    this.settings,
    required this.createdAt,
    required this.updatedAt,
  });
  
  String get fullName => '$firstName $lastName';
  
  String get displayName => fullName;
  
  bool get isAdmin => type == 'admin';
  bool get isEmployer => type == 'employer';
  bool get isEmployee => type == 'employee';
  bool get isActive => status == 'active';
  
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      firstName: json['first_name'] ?? json['firstName'] ?? '',
      lastName: json['last_name'] ?? json['lastName'] ?? '',
      email: json['email'],
      phone: json['phone'],
      avatar: json['avatar'],
      type: json['type'] ?? 'employee',
      status: json['status'] ?? 'active',
      tenantId: json['tenant_id'] ?? json['tenantId'],
      settings: json['settings'] is Map<String, dynamic> 
          ? json['settings'] as Map<String, dynamic>
          : json['settings'] is Map
              ? Map<String, dynamic>.from(json['settings'])
              : null,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : (json['createdAt'] != null 
              ? DateTime.parse(json['createdAt'])
              : DateTime.now()),
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : (json['updatedAt'] != null 
              ? DateTime.parse(json['updatedAt'])
              : DateTime.now()),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'phone': phone,
      'avatar': avatar,
      'type': type,
      'status': status,
      'tenant_id': tenantId,
      'settings': settings,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  User copyWith({
    int? id,
    String? firstName,
    String? lastName,
    String? email,
    String? phone,
    String? avatar,
    String? type,
    String? status,
    int? tenantId,
    Map<String, dynamic>? settings,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return User(
      id: id ?? this.id,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatar: avatar ?? this.avatar,
      type: type ?? this.type,
      status: status ?? this.status,
      tenantId: tenantId ?? this.tenantId,
      settings: settings ?? this.settings,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
  
  @override
  String toString() {
    return 'User(id: $id, name: $fullName, email: $email, type: $type, status: $status)';
  }
  
  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is User && other.id == id;
  }
  
  @override
  int get hashCode => id.hashCode;
}