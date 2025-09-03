import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';

import '../providers/timesheet_provider.dart';
import '../utils/constants.dart';

class TimesheetListScreen extends StatefulWidget {
  const TimesheetListScreen({super.key});

  @override
  State<TimesheetListScreen> createState() => _TimesheetListScreenState();
}

class _TimesheetListScreenState extends State<TimesheetListScreen> {
  String _selectedFilter = 'Tous';
  final List<String> _filterOptions = ['Tous', 'En cours', 'Validés', 'Rejetés'];

  @override
  void initState() {
    super.initState();
    _loadTimesheets();
  }

  Future<void> _loadTimesheets() async {
    final timesheetProvider = Provider.of<TimesheetProvider>(context, listen: false);
    await timesheetProvider.loadTimesheets();
  }

  List<dynamic> _getFilteredTimesheets(List<dynamic> timesheets) {
    switch (_selectedFilter) {
      case 'En cours':
        return timesheets.where((t) => t.status == 'EN_ATTENTE_VALIDATION').toList();
      case 'Validés':
        return timesheets.where((t) => t.status == 'VALIDE').toList();
      case 'Rejetés':
        return timesheets.where((t) => t.status == 'REJETE').toList();
      default:
        return timesheets;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes Timesheets'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: Consumer<TimesheetProvider>(
        builder: (context, timesheetProvider, child) {
          final timesheets = _getFilteredTimesheets(timesheetProvider.timesheets);
          
          if (timesheetProvider.isLoading) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }

          if (timesheets.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.access_time,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _selectedFilter == 'Tous' 
                        ? 'Aucun timesheet trouvé'
                        : 'Aucun timesheet $selectedFilter.toLowerCase() trouvé',
                    style: TextStyle(
                      fontSize: 18,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Vos pointages apparaîtront ici',
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
            onRefresh: _loadTimesheets,
            child: Column(
              children: [
                // Filter Chip
                if (_selectedFilter != 'Tous')
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
                              _selectedFilter = 'Tous';
                            });
                          },
                          child: const Text('Effacer'),
                        ),
                      ],
                    ),
                  ),
                
                // Timesheets List
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: timesheets.length,
                    itemBuilder: (context, index) {
                      final timesheet = timesheets[index];
                      return _buildTimesheetCard(timesheet);
                    },
                  ),
                ),
              ],
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          context.push('/clock-in-out');
        },
        backgroundColor: AppTheme.primaryColor,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildTimesheetCard(dynamic timesheet) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      child: InkWell(
        onTap: () {
          _showTimesheetDetails(timesheet);
        },
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header with date and status
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    timesheet.datePointage,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  _buildStatusChip(timesheet.status),
                ],
              ),
              
              const SizedBox(height: 12),
              
              // Time information
              Row(
                children: [
                  Expanded(
                    child: _buildTimeInfo(
                      'Entrée',
                      timesheet.heureDebut,
                      Icons.login,
                      Colors.green,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildTimeInfo(
                      'Sortie',
                      timesheet.heureFin ?? 'En cours',
                      Icons.logout,
                      Colors.red,
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 12),
              
              // Hours worked
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Heures travaillées:',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  Text(
                    '${timesheet.heuresTravaillees}h',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                ],
              ),
              
              // Overtime hours
              if (timesheet.heuresSupplementaires > 0) ...[
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Heures supplémentaires:',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    Text(
                      '${timesheet.heuresSupplementaires}h',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Colors.orange,
                      ),
                    ),
                  ],
                ),
              ],
              
              // Location info
              if (timesheet.latitude != null && timesheet.longitude != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.location_on, size: 16, color: Colors.grey),
                    const SizedBox(width: 4),
                    Text(
                      'Pointage géolocalisé',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTimeInfo(String label, String time, IconData icon, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 4),
            Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                color: Colors.grey,
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          time,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  Widget _buildStatusChip(String status) {
    Color backgroundColor;
    Color textColor;
    String statusText;

    switch (status) {
      case 'VALIDE':
        backgroundColor = Colors.green;
        textColor = Colors.white;
        statusText = 'Validé';
        break;
      case 'EN_ATTENTE_VALIDATION':
        backgroundColor = Colors.orange;
        textColor = Colors.white;
        statusText = 'En attente';
        break;
      case 'REJETE':
        backgroundColor = Colors.red;
        textColor = Colors.white;
        statusText = 'Rejeté';
        break;
      case 'EN_COURS':
        backgroundColor = Colors.blue;
        textColor = Colors.white;
        statusText = 'En cours';
        break;
      default:
        backgroundColor = Colors.grey;
        textColor = Colors.white;
        statusText = status;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        statusText,
        style: TextStyle(
          color: textColor,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filtrer les timesheets'),
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

  void _showTimesheetDetails(dynamic timesheet) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Timesheet - ${timesheet.datePointage}'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow('Statut', _getStatusText(timesheet.status)),
              _buildDetailRow('Heure d\'entrée', timesheet.heureDebut),
              _buildDetailRow('Heure de sortie', timesheet.heureFin ?? 'En cours'),
              _buildDetailRow('Heures travaillées', '${timesheet.heuresTravaillees}h'),
              if (timesheet.heuresSupplementaires > 0)
                _buildDetailRow('Heures supplémentaires', '${timesheet.heuresSupplementaires}h'),
              if (timesheet.latitude != null && timesheet.longitude != null) ...[
                _buildDetailRow('Latitude', timesheet.latitude.toString()),
                _buildDetailRow('Longitude', timesheet.longitude.toString()),
              ],
              if (timesheet.commentaires != null && timesheet.commentaires.isNotEmpty)
                _buildDetailRow('Commentaires', timesheet.commentaires),
            ],
          ),
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

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'VALIDE':
        return 'Validé';
      case 'EN_ATTENTE_VALIDATION':
        return 'En attente de validation';
      case 'REJETE':
        return 'Rejeté';
      case 'EN_COURS':
        return 'En cours';
      default:
        return status;
    }
  }
}