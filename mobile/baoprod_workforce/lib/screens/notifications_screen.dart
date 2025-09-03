import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../utils/constants.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<dynamic> _notifications = [];
  bool _isLoading = true;
  String _selectedFilter = 'Toutes';
  final List<String> _filterOptions = ['Toutes', 'Non lues', 'Lues', 'Importantes'];

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // TODO: Load notifications from API
      // Simulate API call
      await Future.delayed(const Duration(seconds: 1));
      
      // Mock data for now
      _notifications = [
        {
          'id': 1,
          'title': 'Nouveau contrat disponible',
          'message': 'Un nouveau contrat de Développeur Web est disponible pour signature.',
          'type': 'contrat',
          'is_read': false,
          'is_important': true,
          'created_at': '2025-09-03 08:30:00',
          'data': {'contrat_id': 1, 'poste': 'Développeur Web'},
        },
        {
          'id': 2,
          'title': 'Pointage validé',
          'message': 'Votre pointage du 2 septembre 2025 a été validé par votre superviseur.',
          'type': 'timesheet',
          'is_read': false,
          'is_important': false,
          'created_at': '2025-09-03 07:45:00',
          'data': {'timesheet_id': 1, 'date': '2025-09-02'},
        },
        {
          'id': 3,
          'title': 'Bulletin de paie disponible',
          'message': 'Votre bulletin de paie du mois d\'août 2025 est maintenant disponible.',
          'type': 'paie',
          'is_read': true,
          'is_important': false,
          'created_at': '2025-09-01 16:20:00',
          'data': {'paie_id': 1, 'mois': 'août', 'annee': 2025},
        },
        {
          'id': 4,
          'title': 'Rappel de pointage',
          'message': 'N\'oubliez pas de pointer votre sortie avant 18h00.',
          'type': 'reminder',
          'is_read': true,
          'is_important': false,
          'created_at': '2025-09-03 17:30:00',
          'data': {'action': 'clock_out'},
        },
        {
          'id': 5,
          'title': 'Contrat expiré',
          'message': 'Votre contrat de Mission - Assistant Administratif a expiré le 31 janvier 2025.',
          'type': 'contrat',
          'is_read': true,
          'is_important': true,
          'created_at': '2025-02-01 09:00:00',
          'data': {'contrat_id': 3, 'poste': 'Assistant Administratif'},
        },
      ];
    } catch (e) {
      _showErrorDialog('Erreur lors du chargement des notifications: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  List<dynamic> _getFilteredNotifications() {
    switch (_selectedFilter) {
      case 'Non lues':
        return _notifications.where((n) => !n['is_read']).toList();
      case 'Lues':
        return _notifications.where((n) => n['is_read']).toList();
      case 'Importantes':
        return _notifications.where((n) => n['is_important']).toList();
      default:
        return _notifications;
    }
  }

  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Erreur'),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
          IconButton(
            icon: const Icon(Icons.mark_email_read),
            onPressed: _markAllAsRead,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _buildNotificationsList(),
    );
  }

  Widget _buildNotificationsList() {
    final filteredNotifications = _getFilteredNotifications();

    if (filteredNotifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              _selectedFilter == 'Toutes' 
                  ? 'Aucune notification'
                  : 'Aucune notification $_selectedFilter.toLowerCase()',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Vos notifications apparaîtront ici',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadNotifications,
      child: Column(
        children: [
          // Filter Chip
          if (_selectedFilter != 'Toutes')
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  Text(
                    'Filtre: $_selectedFilter',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const Spacer(),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _selectedFilter = 'Toutes';
                      });
                    },
                    child: const Text('Effacer'),
                  ),
                ],
              ),
            ),
          
          // Notifications List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: filteredNotifications.length,
              itemBuilder: (context, index) {
                final notification = filteredNotifications[index];
                return _buildNotificationCard(notification);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationCard(Map<String, dynamic> notification) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      elevation: notification['is_read'] ? 1 : 3,
      child: InkWell(
        onTap: () {
          _handleNotificationTap(notification);
        },
        borderRadius: BorderRadius.circular(8),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(8),
            color: notification['is_read'] ? null : Colors.blue.withOpacity(0.05),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Icon
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: _getNotificationTypeColor(notification['type']),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Icon(
                    _getNotificationTypeIcon(notification['type']),
                    color: Colors.white,
                    size: 20,
                  ),
                ),
                
                const SizedBox(width: 12),
                
                // Content
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Title
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              notification['title'],
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: notification['is_read'] ? FontWeight.normal : FontWeight.bold,
                              ),
                            ),
                          ),
                          if (notification['is_important'])
                            const Icon(
                              Icons.priority_high,
                              color: Colors.red,
                              size: 16,
                            ),
                        ],
                      ),
                      
                      const SizedBox(height: 4),
                      
                      // Message
                      Text(
                        notification['message'],
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      
                      const SizedBox(height: 8),
                      
                      // Time
                      Row(
                        children: [
                          Icon(
                            Icons.access_time,
                            size: 12,
                            color: Colors.grey[500],
                          ),
                          const SizedBox(width: 4),
                          Text(
                            _formatDateTime(notification['created_at']),
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[500],
                            ),
                          ),
                          const Spacer(),
                          if (!notification['is_read'])
                            Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(
                                color: Colors.blue,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Color _getNotificationTypeColor(String type) {
    switch (type) {
      case 'contrat':
        return Colors.green;
      case 'timesheet':
        return Colors.blue;
      case 'paie':
        return Colors.orange;
      case 'reminder':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  IconData _getNotificationTypeIcon(String type) {
    switch (type) {
      case 'contrat':
        return Icons.description;
      case 'timesheet':
        return Icons.access_time;
      case 'paie':
        return Icons.attach_money;
      case 'reminder':
        return Icons.notifications;
      default:
        return Icons.info;
    }
  }

  String _formatDateTime(String dateTime) {
    try {
      final date = DateTime.parse(dateTime);
      final now = DateTime.now();
      final difference = now.difference(date);

      if (difference.inDays > 0) {
        return 'Il y a ${difference.inDays} jour${difference.inDays > 1 ? 's' : ''}';
      } else if (difference.inHours > 0) {
        return 'Il y a ${difference.inHours}h';
      } else if (difference.inMinutes > 0) {
        return 'Il y a ${difference.inMinutes}min';
      } else {
        return 'À l\'instant';
      }
    } catch (e) {
      return dateTime;
    }
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filtrer les notifications'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: _filterOptions.map((filter) => RadioListTile<String>(
            title: Text(filter),
            value: filter,
            groupValue: _selectedFilter,
            onChanged: (value) {
              setState(() {
                _selectedFilter = value!;
              });
              Navigator.of(context).pop();
            },
          )).toList(),
        ),
      ),
    );
  }

  void _markAllAsRead() {
    setState(() {
      for (var notification in _notifications) {
        notification['is_read'] = true;
      }
    });
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Toutes les notifications ont été marquées comme lues'),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _handleNotificationTap(Map<String, dynamic> notification) {
    // Mark as read
    setState(() {
      notification['is_read'] = true;
    });

    // Handle different notification types
    switch (notification['type']) {
      case 'contrat':
        _handleContratNotification(notification);
        break;
      case 'timesheet':
        _handleTimesheetNotification(notification);
        break;
      case 'paie':
        _handlePaieNotification(notification);
        break;
      case 'reminder':
        _handleReminderNotification(notification);
        break;
      default:
        _showNotificationDetails(notification);
    }
  }

  void _handleContratNotification(Map<String, dynamic> notification) {
    final data = notification['data'];
    if (data != null && data['contrat_id'] != null) {
      // Navigate to contrat details or signing
      context.push('/contrats');
    }
  }

  void _handleTimesheetNotification(Map<String, dynamic> notification) {
    final data = notification['data'];
    if (data != null && data['timesheet_id'] != null) {
      // Navigate to timesheet details
      context.push('/timesheets');
    }
  }

  void _handlePaieNotification(Map<String, dynamic> notification) {
    final data = notification['data'];
    if (data != null && data['paie_id'] != null) {
      // Navigate to paie details
      _showPaieDetails(data);
    }
  }

  void _handleReminderNotification(Map<String, dynamic> notification) {
    final data = notification['data'];
    if (data != null && data['action'] == 'clock_out') {
      // Navigate to clock out
      context.push('/clock-in-out');
    }
  }

  void _showNotificationDetails(Map<String, dynamic> notification) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(notification['title']),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(notification['message']),
            const SizedBox(height: 16),
            Text(
              'Type: ${notification['type']}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            Text(
              'Date: ${notification['created_at']}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }

  void _showPaieDetails(Map<String, dynamic> data) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Bulletin de paie'),
        content: Text(
          'Votre bulletin de paie de ${data['mois']} ${data['annee']} est disponible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Fermer'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Open paie details or download PDF
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Ouverture du bulletin de paie...'),
                ),
              );
            },
            child: const Text('Voir'),
          ),
        ],
      ),
    );
  }
}