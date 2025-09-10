import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/timesheet_provider.dart';
import '../utils/constants.dart';
import '../utils/app_theme.dart';

class TimesheetListScreen extends StatefulWidget {
  const TimesheetListScreen({super.key});

  @override
  State<TimesheetListScreen> createState() => _TimesheetListScreenState();
}

class _TimesheetListScreenState extends State<TimesheetListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TimesheetProvider>().loadTimesheets();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Feuilles de temps'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              context.read<TimesheetProvider>().loadTimesheets(refresh: true);
            },
          ),
        ],
      ),
      body: Consumer<TimesheetProvider>(
        builder: (context, timesheetProvider, child) {
          if (timesheetProvider.isLoading && timesheetProvider.timesheets.isEmpty) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }

          if (timesheetProvider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: Theme.of(context).colorScheme.error, // Refactored
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Erreur de chargement',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    timesheetProvider.error!,
                    style: Theme.of(context).textTheme.bodyMedium,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      timesheetProvider.loadTimesheets(refresh: true);
                    },
                    child: const Text('Réessayer'),
                  ),
                ],
              ),
            );
          }

          if (timesheetProvider.timesheets.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.access_time_outlined,
                    size: 64,
                    color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucune feuille de temps',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Vos feuilles de temps apparaîtront ici',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async {
              await timesheetProvider.loadTimesheets(refresh: true);
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              itemCount: timesheetProvider.timesheets.length,
              itemBuilder: (context, index) {
                final timesheet = timesheetProvider.timesheets[index];
                return _buildTimesheetCard(context, timesheet);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildTimesheetCard(BuildContext context, timesheet) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // En-tête avec date et statut
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  _formatDate(timesheet.datePointage),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                _buildStatusChip(context, timesheet.status), // Pass context
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Heures de travail
            Row(
              children: [
                Expanded(
                  child: _buildTimeInfo(
                    context,
                    icon: Icons.login,
                    label: 'Entrée',
                    time: timesheet.heureDebut != null 
                        ? _formatTime(timesheet.heureDebut)
                        : 'Non pointé',
                    color: Theme.of(context).colorScheme.secondary, // Refactored
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildTimeInfo(
                    context,
                    icon: Icons.logout,
                    label: 'Sortie',
                    time: timesheet.heureFin != null 
                        ? _formatTime(timesheet.heureFin)
                        : 'Non pointé',
                    color: Theme.of(context).colorScheme.error, // Refactored
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Heures travaillées
            if (timesheet.heuresTravailleesMinutes != null)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor.withOpacity(0.1), // Refactored
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.access_time,
                      color: Theme.of(context).primaryColor, // Refactored
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Heures travaillées: ',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    Text(
                      timesheet.formattedHeuresTravaillees,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).primaryColor, // Refactored
                      ),
                    ),
                  ],
                ),
              ),
            
            // Heures supplémentaires
            if (timesheet.heuresSupplementairesMinutes != null && timesheet.heuresSupplementairesMinutes! > 0)
              const SizedBox(height: 8),
            if (timesheet.heuresSupplementairesMinutes != null && timesheet.heuresSupplementairesMinutes! > 0)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.tertiary.withOpacity(0.1), // Refactored
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.schedule,
                      color: Theme.of(context).colorScheme.tertiary, // Refactored
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Heures supplémentaires: ',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    Text(
                      timesheet.formattedHeuresSupplementaires,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.tertiary, // Refactored
                      ),
                    ),
                  ],
                ),
              ),
            
            // Localisation
            if (timesheet.latitude != null && timesheet.longitude != null)
              const SizedBox(height: 12),
            if (timesheet.latitude != null && timesheet.longitude != null)
              Row(
                children: [
                  Icon(
                    Icons.location_on,
                    color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                    size: 16,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Pointage géolocalisé',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor, // Refactored
                      ),
                    ),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusChip(BuildContext context, String status) { // Added context
    Color color;
    String text;
    
    switch (status) {
      case 'draft':
        color = Theme.of(context).colorScheme.tertiary; // Refactored
        text = 'Brouillon';
        break;
      case 'submitted':
        color = Theme.of(context).colorScheme.primary; // Refactored
        text = 'Soumis';
        break;
      case 'approved':
        color = Theme.of(context).colorScheme.secondary; // Refactored
        text = 'Approuvé';
        break;
      case 'rejected':
        color = Theme.of(context).colorScheme.error; // Refactored
        text = 'Rejeté';
        break;
      default:
        color = Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor; // Refactored
        text = 'Inconnu';
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _buildTimeInfo(
    BuildContext context, { // Added context
    required IconData icon,
    required String label,
    required String time,
    required Color color,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              icon,
              color: color,
              size: 16,
            ),
            const SizedBox(width: 4),
            Text(
              label,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor, // Refactored
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          time,
          style: Theme.of(context).textTheme.titleSmall?.copyWith(
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final yesterday = today.subtract(const Duration(days: 1));
    final dateOnly = DateTime(date.year, date.month, date.day);
    
    if (dateOnly == today) {
      return 'Aujourd\'hui';
    } else if (dateOnly == yesterday) {
      return 'Hier';
    } else {
      return '${date.day}/${date.month}/${date.year}';
    }
  }

  String _formatTime(DateTime time) {
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
}