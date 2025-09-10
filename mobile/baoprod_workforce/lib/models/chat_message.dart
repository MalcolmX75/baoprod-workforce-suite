import 'package:flutter/foundation.dart';

enum MessageType {
  text,
  image,
  document,
  system
}

enum MessageSender {
  employee,
  employer,
  system
}

class ChatMessage {
  final int id;
  final String content;
  final MessageType type;
  final MessageSender sender;
  final DateTime timestamp;
  final String senderName;
  final String? imageUrl;
  final String? documentUrl;
  final bool isRead;
  final Map<String, dynamic>? metadata;

  const ChatMessage({
    required this.id,
    required this.content,
    required this.type,
    required this.sender,
    required this.timestamp,
    required this.senderName,
    this.imageUrl,
    this.documentUrl,
    this.isRead = false,
    this.metadata,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] as int,
      content: json['content'] as String,
      type: MessageType.values.firstWhere(
        (e) => e.name == json['type'],
        orElse: () => MessageType.text,
      ),
      sender: MessageSender.values.firstWhere(
        (e) => e.name == json['sender'],
        orElse: () => MessageSender.employee,
      ),
      timestamp: DateTime.parse(json['timestamp'] as String),
      senderName: json['sender_name'] as String,
      imageUrl: json['image_url'] as String?,
      documentUrl: json['document_url'] as String?,
      isRead: json['is_read'] as bool? ?? false,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'content': content,
      'type': type.name,
      'sender': sender.name,
      'timestamp': timestamp.toIso8601String(),
      'sender_name': senderName,
      'image_url': imageUrl,
      'document_url': documentUrl,
      'is_read': isRead,
      'metadata': metadata,
    };
  }

  ChatMessage copyWith({
    int? id,
    String? content,
    MessageType? type,
    MessageSender? sender,
    DateTime? timestamp,
    String? senderName,
    String? imageUrl,
    String? documentUrl,
    bool? isRead,
    Map<String, dynamic>? metadata,
  }) {
    return ChatMessage(
      id: id ?? this.id,
      content: content ?? this.content,
      type: type ?? this.type,
      sender: sender ?? this.sender,
      timestamp: timestamp ?? this.timestamp,
      senderName: senderName ?? this.senderName,
      imageUrl: imageUrl ?? this.imageUrl,
      documentUrl: documentUrl ?? this.documentUrl,
      isRead: isRead ?? this.isRead,
      metadata: metadata ?? this.metadata,
    );
  }

  bool get isFromEmployee => sender == MessageSender.employee;
  bool get isFromEmployer => sender == MessageSender.employer;
  bool get isSystemMessage => sender == MessageSender.system;

  String get formattedTime {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final messageDate = DateTime(timestamp.year, timestamp.month, timestamp.day);
    
    if (messageDate == today) {
      return '${timestamp.hour.toString().padLeft(2, '0')}:${timestamp.minute.toString().padLeft(2, '0')}';
    } else {
      return '${timestamp.day}/${timestamp.month} ${timestamp.hour.toString().padLeft(2, '0')}:${timestamp.minute.toString().padLeft(2, '0')}';
    }
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is ChatMessage && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'ChatMessage(id: $id, sender: $sender, content: $content, timestamp: $timestamp)';
  }
}