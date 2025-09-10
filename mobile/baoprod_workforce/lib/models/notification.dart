import 'package:flutter/material.dart';

enum NotificationType {
  job, // Offres d'emploi
  timesheet, // Pointage/Feuilles de temps
  contract, // Contrats
  system, // Système/App
  message, // Messages/Chat
  reminder, // Rappels
}

class AppNotification {
  final int id;
  final String title;
  final String message;
  final NotificationType type;
  final DateTime createdAt;
  final bool isRead;
  final bool isImportant;
  final Map<String, dynamic>? data; // Données supplémentaires pour navigation
  final String? actionUrl; // URL ou route pour navigation
  final String? imageUrl;

  AppNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.createdAt,
    this.isRead = false,
    this.isImportant = false,
    this.data,
    this.actionUrl,
    this.imageUrl,
  });

  String get formattedTime {
    final now = DateTime.now();
    final difference = now.difference(createdAt);

    if (difference.inMinutes < 1) {
      return "Maintenant";
    } else if (difference.inMinutes < 60) {
      return "Il y a ${difference.inMinutes}min";
    } else if (difference.inHours < 24) {
      return "Il y a ${difference.inHours}h";
    } else if (difference.inDays < 7) {
      return "Il y a ${difference.inDays}j";
    } else if (difference.inDays < 30) {
      final weeks = (difference.inDays / 7).floor();
      return "Il y a ${weeks}sem";
    } else {
      final months = (difference.inDays / 30).floor();
      return "Il y a ${months}mois";
    }
  }

  IconData get typeIcon {
    switch (type) {
      case NotificationType.job:
        return Icons.work_outline;
      case NotificationType.timesheet:
        return Icons.access_time;
      case NotificationType.contract:
        return Icons.description;
      case NotificationType.system:
        return Icons.notifications;
      case NotificationType.message:
        return Icons.message;
      case NotificationType.reminder:
        return Icons.alarm;
    }
  }

  Color get typeColor {
    switch (type) {
      case NotificationType.job:
        return Colors.blue;
      case NotificationType.timesheet:
        return Colors.green;
      case NotificationType.contract:
        return Colors.orange;
      case NotificationType.system:
        return Colors.purple;
      case NotificationType.message:
        return Colors.teal;
      case NotificationType.reminder:
        return Colors.red;
    }
  }

  String get typeLabel {
    switch (type) {
      case NotificationType.job:
        return 'Emploi';
      case NotificationType.timesheet:
        return 'Pointage';
      case NotificationType.contract:
        return 'Contrat';
      case NotificationType.system:
        return 'Système';
      case NotificationType.message:
        return 'Message';
      case NotificationType.reminder:
        return 'Rappel';
    }
  }

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      type: _parseNotificationType(json['type']),
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
      isRead: json['is_read'] ?? false,
      isImportant: json['is_important'] ?? false,
      data: json['data'],
      actionUrl: json['action_url'],
      imageUrl: json['image_url'],
    );
  }

  static NotificationType _parseNotificationType(String? type) {
    switch (type?.toLowerCase()) {
      case 'job':
        return NotificationType.job;
      case 'timesheet':
        return NotificationType.timesheet;
      case 'contract':
        return NotificationType.contract;
      case 'system':
        return NotificationType.system;
      case 'message':
        return NotificationType.message;
      case 'reminder':
        return NotificationType.reminder;
      default:
        return NotificationType.system;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'message': message,
      'type': type.name,
      'created_at': createdAt.toIso8601String(),
      'is_read': isRead,
      'is_important': isImportant,
      'data': data,
      'action_url': actionUrl,
      'image_url': imageUrl,
    };
  }

  AppNotification copyWith({
    int? id,
    String? title,
    String? message,
    NotificationType? type,
    DateTime? createdAt,
    bool? isRead,
    bool? isImportant,
    Map<String, dynamic>? data,
    String? actionUrl,
    String? imageUrl,
  }) {
    return AppNotification(
      id: id ?? this.id,
      title: title ?? this.title,
      message: message ?? this.message,
      type: type ?? this.type,
      createdAt: createdAt ?? this.createdAt,
      isRead: isRead ?? this.isRead,
      isImportant: isImportant ?? this.isImportant,
      data: data ?? this.data,
      actionUrl: actionUrl ?? this.actionUrl,
      imageUrl: imageUrl ?? this.imageUrl,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is AppNotification && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}