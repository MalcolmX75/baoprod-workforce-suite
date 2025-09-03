import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../providers/timesheet_provider.dart';
import '../widgets/custom_button.dart';
import '../utils/constants.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    final timesheetProvider = Provider.of<TimesheetProvider>(context, listen: false);
    await timesheetProvider.loadTimesheets();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tableau de Bord'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications),
            onPressed: () {
              // TODO: Navigate to notifications
            },
          ),
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              context.push('/settings');
            },
          ),
        ],
      ),
      body: Consumer2<AuthProvider, TimesheetProvider>(
        builder: (context, authProvider, timesheetProvider, child) {
          final user = authProvider.user;
          final currentTimesheet = timesheetProvider.currentTimesheet;
          
          return RefreshIndicator(
            onRefresh: _loadDashboardData,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Welcome Card
                  _buildWelcomeCard(user?.firstName ?? 'Utilisateur'),
                  
                  const SizedBox(height: 20),
                  
                  // Quick Actions
                  _buildQuickActionsSection(currentTimesheet),
                  
                  const SizedBox(height: 20),
                  
                  // Statistics
                  _buildStatisticsSection(timesheetProvider),
                  
                  const SizedBox(height: 20),
                  
                  // Recent Timesheets
                  _buildRecentTimesheetsSection(timesheetProvider),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildWelcomeCard(String firstName) {
    return Card(
      elevation: 4,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          gradient: LinearGradient(
            colors: [AppTheme.primaryColor, AppTheme.primaryColor.withOpacity(0.8)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Bonjour, $firstName !',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Bienvenue sur votre tableau de bord BaoProd Workforce',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 16,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActionsSection(dynamic currentTimesheet) {
    final isClockedIn = currentTimesheet != null && currentTimesheet.status == 'EN_COURS';
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Actions Rapides',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: CustomButton(
                text: isClockedIn ? 'Pointer Sortie' : 'Pointer Entrée',
                onPressed: () {
                  context.push('/clock-in-out');
                },
                backgroundColor: isClockedIn ? Colors.red : Colors.green,
                icon: isClockedIn ? Icons.logout : Icons.login,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: CustomButton(
                text: 'Mes Timesheets',
                onPressed: () {
                  context.push('/timesheets');
                },
                backgroundColor: AppTheme.secondaryColor,
                icon: Icons.access_time,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: CustomButton(
                text: 'Mes Contrats',
                onPressed: () {
                  context.push('/contrats');
                },
                backgroundColor: AppTheme.accentColor,
                icon: Icons.description,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: CustomButton(
                text: 'Mon Profil',
                onPressed: () {
                  context.push('/profile');
                },
                backgroundColor: Colors.purple,
                icon: Icons.person,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatisticsSection(TimesheetProvider timesheetProvider) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Statistiques',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Heures cette semaine',
                '${timesheetProvider.weeklyHours}',
                Icons.access_time,
                Colors.blue,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Jours travaillés',
                '${timesheetProvider.workedDays}',
                Icons.calendar_today,
                Colors.green,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Heures supplémentaires',
                '${timesheetProvider.overtimeHours}',
                Icons.trending_up,
                Colors.orange,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Salaire estimé',
                '${timesheetProvider.estimatedSalary} FCFA',
                Icons.attach_money,
                Colors.purple,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: const TextStyle(
                fontSize: 12,
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentTimesheetsSection(TimesheetProvider timesheetProvider) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Timesheets Récents',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            TextButton(
              onPressed: () {
                context.push('/timesheets');
              },
              child: const Text('Voir tout'),
            ),
          ],
        ),
        const SizedBox(height: 12),
        if (timesheetProvider.timesheets.isEmpty)
          const Card(
            child: Padding(
              padding: EdgeInsets.all(20),
              child: Center(
                child: Text(
                  'Aucun timesheet trouvé',
                  style: TextStyle(color: Colors.grey),
                ),
              ),
            ),
          )
        else
          ...timesheetProvider.timesheets.take(3).map((timesheet) => 
            Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: _getStatusColor(timesheet.status),
                  child: Icon(
                    _getStatusIcon(timesheet.status),
                    color: Colors.white,
                  ),
                ),
                title: Text(
                  '${timesheet.datePointage}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                subtitle: Text(
                  '${timesheet.heureDebut} - ${timesheet.heureFin}',
                ),
                trailing: Text(
                  '${timesheet.heuresTravaillees}h',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryColor,
                  ),
                ),
                onTap: () {
                  // TODO: Navigate to timesheet detail
                },
              ),
            ),
          ),
      ],
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'VALIDE':
        return Colors.green;
      case 'EN_ATTENTE_VALIDATION':
        return Colors.orange;
      case 'REJETE':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'VALIDE':
        return Icons.check;
      case 'EN_ATTENTE_VALIDATION':
        return Icons.pending;
      case 'REJETE':
        return Icons.close;
      default:
        return Icons.help;
    }
  }
}