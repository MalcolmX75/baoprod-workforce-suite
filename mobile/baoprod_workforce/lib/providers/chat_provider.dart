import 'package:flutter/material.dart';
import '../models/chat_message.dart';
import '../services/storage_service.dart';

class ChatProvider extends ChangeNotifier {
  List<ChatMessage> _messages = [];
  bool _isLoading = false;
  bool _isTyping = false;
  String? _error;

  List<ChatMessage> get messages => _messages;
  bool get isLoading => _isLoading;
  bool get isTyping => _isTyping;
  String? get error => _error;
  bool get hasError => _error != null;

  int get unreadCount => _messages.where((m) => !m.isRead && m.sender == MessageSender.employer).length;
  bool get hasUnreadMessages => unreadCount > 0;

  Future<void> loadMessages({bool refresh = false}) async {
    if (refresh) {
      _messages.clear();
    }

    _setLoading(true);
    _clearError();

    try {
      // MODE DEMO - Messages de démonstration
      await Future.delayed(const Duration(milliseconds: 500));
      
      final List<Map<String, dynamic>> demoMessages = [
        {
          'id': 1,
          'content': 'Bienvenue dans votre espace de communication BaoProd ! Vous pouvez ici échanger directement avec votre employeur.',
          'type': 'system',
          'sender': 'system',
          'timestamp': DateTime.now().subtract(const Duration(days: 7)).toIso8601String(),
          'sender_name': 'Système BaoProd',
          'is_read': true,
        },
        {
          'id': 2,
          'content': 'Bonjour, j\'espère que vous vous portez bien. Je voulais vous féliciter pour votre excellent travail cette semaine.',
          'type': 'text',
          'sender': 'employer',
          'timestamp': DateTime.now().subtract(const Duration(days: 3)).toIso8601String(),
          'sender_name': 'M. Dupont (RH)',
          'is_read': true,
        },
        {
          'id': 3,
          'content': 'Merci beaucoup pour ces encouragements ! Cela me fait vraiment plaisir.',
          'type': 'text',
          'sender': 'employee',
          'timestamp': DateTime.now().subtract(const Duration(days: 3, hours: 2)).toIso8601String(),
          'sender_name': 'Vous',
          'is_read': true,
        },
        {
          'id': 4,
          'content': 'J\'ai une petite question concernant les congés du mois prochain. Serait-il possible d\'en discuter ?',
          'type': 'text',
          'sender': 'employee',
          'timestamp': DateTime.now().subtract(const Duration(days: 2)).toIso8601String(),
          'sender_name': 'Vous',
          'is_read': true,
        },
        {
          'id': 5,
          'content': 'Bien sûr ! Nous pouvons programmer un entretien pour discuter de vos congés. Quand seriez-vous disponible ?',
          'type': 'text',
          'sender': 'employer',
          'timestamp': DateTime.now().subtract(const Duration(days: 1)).toIso8601String(),
          'sender_name': 'M. Dupont (RH)',
          'is_read': true,
        },
        {
          'id': 6,
          'content': 'Parfait ! Je suis libre jeudi après-midi ou vendredi matin. Qu\'est-ce qui vous arrange le mieux ?',
          'type': 'text',
          'sender': 'employee',
          'timestamp': DateTime.now().subtract(const Duration(hours: 8)).toIso8601String(),
          'sender_name': 'Vous',
          'is_read': true,
        },
        {
          'id': 7,
          'content': 'Jeudi après-midi sera parfait. Rendez-vous à 14h30 dans mon bureau. Bonne journée !',
          'type': 'text',
          'sender': 'employer',
          'timestamp': DateTime.now().subtract(const Duration(hours: 2)).toIso8601String(),
          'sender_name': 'M. Dupont (RH)',
          'is_read': false,
        },
      ];

      _messages = demoMessages
          .map((messageData) => ChatMessage.fromJson(messageData))
          .toList();

      // Trier par timestamp (plus ancien en premier)
      _messages.sort((a, b) => a.timestamp.compareTo(b.timestamp));

      print('💬 ${_messages.length} messages de chat chargés');
      notifyListeners();
    } catch (e) {
      _setError('Erreur lors du chargement des messages: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> sendMessage({
    required String content,
    MessageType type = MessageType.text,
    String? imageUrl,
    String? documentUrl,
    Map<String, dynamic>? metadata,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      // MODE DEMO - Simulation d'envoi de message
      await Future.delayed(const Duration(milliseconds: 800));

      final newMessage = ChatMessage(
        id: _messages.isNotEmpty 
            ? _messages.map((m) => m.id).reduce((a, b) => a > b ? a : b) + 1
            : 1,
        content: content,
        type: type,
        sender: MessageSender.employee,
        timestamp: DateTime.now(),
        senderName: 'Vous',
        imageUrl: imageUrl,
        documentUrl: documentUrl,
        isRead: true,
        metadata: metadata,
      );

      _messages.add(newMessage);
      notifyListeners();

      await _saveToStorage();

      // Simuler une réponse automatique de l'employeur après un délai
      if (content.toLowerCase().contains('bonjour') || 
          content.toLowerCase().contains('salut')) {
        _simulateEmployerResponse('Bonjour ! Comment allez-vous aujourd\'hui ?');
      } else if (content.toLowerCase().contains('merci')) {
        _simulateEmployerResponse('De rien ! N\'hésitez pas si vous avez d\'autres questions.');
      } else if (content.toLowerCase().contains('question')) {
        _simulateEmployerResponse('Je vous écoute, quelle est votre question ?');
      } else {
        _simulateEmployerResponse('Message bien reçu, je vais vous répondre dans les plus brefs délais.');
      }

      print('💬 Message envoyé: $content');
      return true;
    } catch (e) {
      _setError('Erreur lors de l\'envoi du message: $e');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  Future<void> _simulateEmployerResponse(String response) async {
    await Future.delayed(const Duration(seconds: 2));
    
    _setTyping(true);
    await Future.delayed(const Duration(seconds: 1));
    
    final responseMessage = ChatMessage(
      id: _messages.map((m) => m.id).reduce((a, b) => a > b ? a : b) + 1,
      content: response,
      type: MessageType.text,
      sender: MessageSender.employer,
      timestamp: DateTime.now(),
      senderName: 'M. Dupont (RH)',
      isRead: false,
    );

    _messages.add(responseMessage);
    _setTyping(false);
    notifyListeners();
    await _saveToStorage();
  }

  Future<void> markAsRead(int messageId) async {
    try {
      final index = _messages.indexWhere((m) => m.id == messageId);
      if (index != -1) {
        _messages[index] = _messages[index].copyWith(isRead: true);
        notifyListeners();
        await _saveToStorage();
      }
    } catch (e) {
      print('Erreur marquer comme lu: $e');
    }
  }

  Future<void> markAllAsRead() async {
    try {
      for (int i = 0; i < _messages.length; i++) {
        if (!_messages[i].isRead && _messages[i].sender == MessageSender.employer) {
          _messages[i] = _messages[i].copyWith(isRead: true);
        }
      }
      notifyListeners();
      await _saveToStorage();
    } catch (e) {
      print('Erreur marquer tout comme lu: $e');
    }
  }

  Future<bool> deleteMessage(int messageId) async {
    try {
      _messages.removeWhere((m) => m.id == messageId);
      notifyListeners();
      await _saveToStorage();
      return true;
    } catch (e) {
      _setError('Erreur lors de la suppression: $e');
      return false;
    }
  }

  ChatMessage? getMessageById(int id) {
    try {
      return _messages.firstWhere((m) => m.id == id);
    } catch (e) {
      return null;
    }
  }

  List<ChatMessage> getMessagesByType(MessageType type) {
    return _messages.where((m) => m.type == type).toList();
  }

  Future<void> _saveToStorage() async {
    try {
      final messagesJson = _messages.map((m) => m.toJson()).toList();
      await StorageService.save('chat_messages', messagesJson);
    } catch (e) {
      print('Erreur sauvegarde messages: $e');
    }
  }

  Future<void> _loadFromStorage() async {
    try {
      final messagesJson = await StorageService.get<List>('chat_messages');
      if (messagesJson != null) {
        _messages = (messagesJson as List)
            .map((json) => ChatMessage.fromJson(Map<String, dynamic>.from(json)))
            .toList();
        notifyListeners();
      }
    } catch (e) {
      print('Erreur chargement messages: $e');
    }
  }

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setTyping(bool typing) {
    _isTyping = typing;
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