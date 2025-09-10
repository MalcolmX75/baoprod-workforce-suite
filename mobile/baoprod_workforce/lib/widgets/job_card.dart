import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/job.dart';
import '../utils/constants.dart';
import '../utils/app_theme.dart';
import '../providers/cv_provider.dart';
import 'cv_selection_dialog.dart';

class JobCard extends StatelessWidget {
  final Job job;
  final VoidCallback? onTap;
  final VoidCallback? onFavorite;

  const JobCard({
    super.key,
    required this.job,
    this.onTap,
    this.onFavorite,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // En-tête avec titre et statut
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          job.title,
                          style: Theme.of(context).textTheme.headlineMedium?.copyWith( // Refactored
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          job.company,
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith( // Refactored
                            color: Theme.of(context).primaryColor, // Refactored
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    children: [
                      _buildStatusChip(context),
                      if (job.hasApplied) ...[
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.green.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.green.withOpacity(0.3)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.check_circle,
                                size: 12,
                                color: Colors.green,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                'Candidaté',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                  color: Colors.green,
                                  fontWeight: FontWeight.w500,
                                  fontSize: 10,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              
              // Description
              Text(
                job.description,
                style: Theme.of(context).textTheme.bodyLarge?.copyWith( // Refactored
                  color: Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor, // Refactored
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),
              
              // Informations clés
              Row(
                children: [
                  _buildInfoChip(
                    context, // Pass context
                    icon: Icons.location_on,
                    text: job.location,
                    color: Theme.of(context).primaryColor, // Refactored
                  ),
                  const SizedBox(width: 8),
                  _buildInfoChip(
                    context, // Pass context
                    icon: Icons.access_time,
                    text: job.contractType,
                    color: Theme.of(context).colorScheme.secondary, // Refactored
                  ),
                ],
              ),
              const SizedBox(height: 8),
              
              Row(
                children: [
                  _buildInfoChip(
                    context, // Pass context
                    icon: Icons.attach_money,
                    text: job.hasSalaryRange ? job.formattedSalaryRange : 'Selon profil',
                    color: Colors.green,
                  ),
                  const SizedBox(width: 8),
                  _buildInfoChip(
                    context, // Pass context
                    icon: Icons.calendar_today,
                    text: job.postedDate,
                    color: Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor, // Refactored
                  ),
                ],
              ),
              const SizedBox(height: 12),
              
              // Actions
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: job.hasApplied ? null : () => _showCVSelectionDialog(context),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: job.hasApplied 
                          ? Theme.of(context).disabledColor
                          : Theme.of(context).primaryColor,
                        side: BorderSide(
                          color: job.hasApplied 
                            ? Theme.of(context).disabledColor
                            : Theme.of(context).primaryColor,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (job.hasApplied) ...[
                            Icon(
                              Icons.check_circle,
                              size: 16,
                              color: Theme.of(context).disabledColor,
                            ),
                            const SizedBox(width: 4),
                          ],
                          Text(job.hasApplied ? 'Candidaté' : 'Postuler'),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    onPressed: onFavorite,
                    icon: Icon(
                      job.isFavorite ? Icons.favorite : Icons.favorite_border,
                      color: job.isFavorite ? Colors.red : Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor, // Refactored
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(BuildContext context) { // Added context
    Color statusColor;
    String statusText;
    
    switch (job.status.toLowerCase()) {
      case 'ouvert':
        statusColor = Colors.green;
        statusText = 'Ouvert';
        break;
      case 'en cours':
        statusColor = Colors.orange;
        statusText = 'En cours';
        break;
      case 'fermé':
        statusColor = Colors.red;
        statusText = 'Fermé';
        break;
      default:
        statusColor = Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor; // Refactored
        statusText = job.status;
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Text(
        statusText,
        style: Theme.of(context).textTheme.bodySmall?.copyWith( // Refactored
          color: statusColor,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  Widget _buildInfoChip(
    BuildContext context, { // Added context
    required IconData icon,
    required String text,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 14,
            color: color,
          ),
          const SizedBox(width: 4),
          Text(
            text,
            style: Theme.of(context).textTheme.bodySmall?.copyWith( // Refactored
              color: color,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _showCVSelectionDialog(BuildContext context) async {
    // Charger les CVs si ce n'est pas encore fait
    final cvProvider = context.read<CVProvider>();
    if (cvProvider.cvList.isEmpty) {
      await cvProvider.loadCVs();
    }

    if (context.mounted) {
      final result = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (BuildContext context) {
          return CVSelectionDialog(job: job);
        },
      );

      // Si la candidature a été envoyée avec succès, actualiser les données
      if (result == true && onFavorite != null) {
        // Le callback onFavorite peut être utilisé pour rafraîchir les données
        // ou on peut directement notifier les listeners
      }
    }
  }
}

class JobCardCompact extends StatelessWidget {
  final Job job;
  final VoidCallback? onTap;

  const JobCardCompact({
    super.key,
    required this.job,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              // Icône de l'emploi
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor.withOpacity(0.1), // Refactored
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  Icons.work_outline,
                  color: Theme.of(context).primaryColor, // Refactored
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              
              // Informations
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      job.title,
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith( // Refactored
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      job.company,
                      style: Theme.of(context).textTheme.bodyLarge?.copyWith( // Refactored
                        color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.location_on,
                          size: 12,
                          color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                        ),
                        const SizedBox(width: 4),
                        Text(
                          job.location,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith( // Refactored
                            color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                          ),
                        ),
                        const SizedBox(width: 12),
                        Icon(
                          Icons.attach_money,
                          size: 12,
                          color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                        ),
                        const SizedBox(width: 4),
                        Text(
                          job.hasSalaryRange ? job.formattedSalaryRange : 'Selon profil',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith( // Refactored
                            color: Theme.of(context).textTheme.bodyMedium?.color, // Refactored
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Statut
              _buildStatusIndicator(context), // Pass context
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusIndicator(BuildContext context) { // Added context
    Color statusColor;
    
    switch (job.status.toLowerCase()) {
      case 'ouvert':
        statusColor = Colors.green;
        break;
      case 'en cours':
        statusColor = Colors.orange;
        break;
      case 'fermé':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Theme.of(context).textTheme.bodyMedium?.color ?? AppTheme.textSecondaryColor; // Refactored
    }
    
    return Container(
      width: 8,
      height: 8,
      decoration: BoxDecoration(
        color: statusColor,
        shape: BoxShape.circle,
      ),
    );
  }
}
