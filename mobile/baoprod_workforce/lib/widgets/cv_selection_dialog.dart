import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cv.dart';
import '../models/job.dart';
import '../providers/cv_provider.dart';
import '../providers/job_provider.dart';

class CVSelectionDialog extends StatefulWidget {
  final Job job;

  const CVSelectionDialog({
    super.key,
    required this.job,
  });

  @override
  State<CVSelectionDialog> createState() => _CVSelectionDialogState();
}

class _CVSelectionDialogState extends State<CVSelectionDialog> {
  CV? selectedCV;
  bool isSubmitting = false;

  @override
  void initState() {
    super.initState();
    // Sélectionner le CV par défaut automatiquement
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final cvProvider = context.read<CVProvider>();
      if (cvProvider.defaultCV != null) {
        setState(() {
          selectedCV = cvProvider.defaultCV;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CVProvider>(
      builder: (context, cvProvider, child) {
        return Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          child: Container(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // En-tête
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Theme.of(context).primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(
                        Icons.work_outline,
                        color: Theme.of(context).primaryColor,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Postuler à l\'offre',
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            widget.job.title,
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: Theme.of(context).primaryColor,
                              fontWeight: FontWeight.w500,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                
                // Instructions
                Text(
                  'Sélectionnez le CV à utiliser pour cette candidature :',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(height: 16),
                
                // Liste des CVs
                if (cvProvider.isLoading)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32),
                      child: CircularProgressIndicator(),
                    ),
                  )
                else if (!cvProvider.hasCVs)
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.orange.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.orange.withOpacity(0.3)),
                    ),
                    child: Column(
                      children: [
                        Icon(
                          Icons.description_outlined,
                          color: Colors.orange,
                          size: 32,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Aucun CV trouvé',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: Colors.orange.shade700,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Vous devez d\'abord ajouter un CV pour pouvoir postuler.',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Colors.orange.shade600,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  )
                else
                  Container(
                    constraints: const BoxConstraints(maxHeight: 300),
                    child: SingleChildScrollView(
                      child: Column(
                        children: cvProvider.cvList.map((cv) => _buildCVTile(cv)).toList(),
                      ),
                    ),
                  ),
                
                if (cvProvider.hasError) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.red.withOpacity(0.3)),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.error_outline, color: Colors.red, size: 16),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            cvProvider.error!,
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Colors.red.shade700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                
                const SizedBox(height: 24),
                
                // Actions
                Row(
                  children: [
                    TextButton(
                      onPressed: isSubmitting ? null : () => Navigator.of(context).pop(),
                      child: const Text('Annuler'),
                    ),
                    const SizedBox(width: 8),
                    if (cvProvider.hasCVs) ...[
                      TextButton.icon(
                        onPressed: isSubmitting ? null : _addNewCV,
                        icon: const Icon(Icons.add, size: 16),
                        label: const Text('Ajouter CV'),
                      ),
                      const SizedBox(width: 8),
                    ],
                    Expanded(
                      child: ElevatedButton(
                        onPressed: (selectedCV != null && !isSubmitting) ? _submitApplication : null,
                        style: ElevatedButton.styleFrom(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        child: isSubmitting
                          ? const SizedBox(
                              height: 16,
                              width: 16,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Postuler'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildCVTile(CV cv) {
    final isSelected = selectedCV?.id == cv.id;
    
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        border: Border.all(
          color: isSelected 
              ? Theme.of(context).primaryColor
              : Colors.grey.withOpacity(0.3),
          width: isSelected ? 2 : 1,
        ),
        borderRadius: BorderRadius.circular(8),
        color: isSelected 
            ? Theme.of(context).primaryColor.withOpacity(0.1)
            : null,
      ),
      child: ListTile(
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: _getFileIconColor(cv.fileExtension),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            _getFileIcon(cv.fileExtension),
            color: Colors.white,
            size: 20,
          ),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(
                cv.name,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w500,
                  color: isSelected ? Theme.of(context).primaryColor : null,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (cv.isDefault) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  'DÉFAUT',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ],
        ),
        subtitle: Row(
          children: [
            Expanded(
              child: Text(
                '${cv.displayExtension.toUpperCase()} • ${cv.formattedFileSize}',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
            ),
            Text(
              _formatDate(cv.updatedAt),
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Colors.grey[500],
                fontSize: 11,
              ),
            ),
          ],
        ),
        trailing: Radio<CV>(
          value: cv,
          groupValue: selectedCV,
          onChanged: (CV? value) {
            setState(() {
              selectedCV = value;
            });
          },
          activeColor: Theme.of(context).primaryColor,
        ),
        onTap: () {
          setState(() {
            selectedCV = cv;
          });
        },
      ),
    );
  }

  IconData _getFileIcon(String extension) {
    switch (extension.toLowerCase()) {
      case 'pdf':
        return Icons.picture_as_pdf;
      case 'doc':
      case 'docx':
        return Icons.description;
      default:
        return Icons.insert_drive_file;
    }
  }

  Color _getFileIconColor(String extension) {
    switch (extension.toLowerCase()) {
      case 'pdf':
        return Colors.red;
      case 'doc':
      case 'docx':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date).inDays;
    
    if (difference == 0) {
      return "Aujourd'hui";
    } else if (difference == 1) {
      return "Hier";
    } else if (difference < 7) {
      return "Il y a $difference j";
    } else if (difference < 30) {
      final weeks = (difference / 7).floor();
      return "Il y a $weeks sem";
    } else {
      final months = (difference / 30).floor();
      return "Il y a $months mois";
    }
  }

  void _addNewCV() {
    // TODO: Implémenter l'ajout de CV (sélecteur de fichier)
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Fonctionnalité d\'ajout de CV à implémenter'),
        backgroundColor: Colors.orange,
      ),
    );
  }

  Future<void> _submitApplication() async {
    if (selectedCV == null) return;

    setState(() {
      isSubmitting = true;
    });

    try {
      final jobProvider = context.read<JobProvider>();
      final success = await jobProvider.applyToJob(widget.job.id, {
        'cv_id': selectedCV!.id,
        'cv_name': selectedCV!.name,
        'message': 'Candidature envoyée depuis l\'application mobile',
      });

      if (success && mounted) {
        Navigator.of(context).pop(true); // Retourner true pour indiquer le succès
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white, size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text('Candidature envoyée avec le CV "${selectedCV!.name}"'),
                ),
              ],
            ),
            backgroundColor: Colors.green,
          ),
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Erreur lors de l\'envoi de la candidature'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          isSubmitting = false;
        });
      }
    }
  }
}