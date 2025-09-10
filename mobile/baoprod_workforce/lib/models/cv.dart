class CV {
  final int id;
  final String name;
  final String fileName;
  final String filePath;
  final DateTime createdAt;
  final DateTime updatedAt;
  final bool isDefault;
  final int fileSize; // in bytes
  final String fileExtension; // pdf, doc, docx

  CV({
    required this.id,
    required this.name,
    required this.fileName,
    required this.filePath,
    required this.createdAt,
    required this.updatedAt,
    this.isDefault = false,
    required this.fileSize,
    required this.fileExtension,
  });

  String get formattedFileSize {
    if (fileSize < 1024) {
      return '${fileSize} B';
    } else if (fileSize < 1024 * 1024) {
      return '${(fileSize / 1024).toStringAsFixed(1)} KB';
    } else {
      return '${(fileSize / (1024 * 1024)).toStringAsFixed(1)} MB';
    }
  }

  String get displayExtension => fileExtension.toLowerCase();

  factory CV.fromJson(Map<String, dynamic> json) {
    return CV(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      fileName: json['file_name'] ?? json['fileName'] ?? '',
      filePath: json['file_path'] ?? json['filePath'] ?? '',
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at'])
          : DateTime.now(),
      isDefault: json['is_default'] ?? json['isDefault'] ?? false,
      fileSize: json['file_size'] ?? json['fileSize'] ?? 0,
      fileExtension: json['file_extension'] ?? json['fileExtension'] ?? 'pdf',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'file_name': fileName,
      'file_path': filePath,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'is_default': isDefault,
      'file_size': fileSize,
      'file_extension': fileExtension,
    };
  }

  CV copyWith({
    int? id,
    String? name,
    String? fileName,
    String? filePath,
    DateTime? createdAt,
    DateTime? updatedAt,
    bool? isDefault,
    int? fileSize,
    String? fileExtension,
  }) {
    return CV(
      id: id ?? this.id,
      name: name ?? this.name,
      fileName: fileName ?? this.fileName,
      filePath: filePath ?? this.filePath,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      isDefault: isDefault ?? this.isDefault,
      fileSize: fileSize ?? this.fileSize,
      fileExtension: fileExtension ?? this.fileExtension,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is CV && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}