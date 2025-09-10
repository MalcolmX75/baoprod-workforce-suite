import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/job_provider.dart';
import '../providers/cv_provider.dart';
import '../models/job.dart';
import '../widgets/custom_button.dart';
import '../widgets/custom_text_field.dart';
import '../utils/constants.dart';
import '../utils/app_theme.dart';
import '../widgets/job_card.dart';
import '../widgets/cv_selection_dialog.dart';

class JobSearchScreen extends StatefulWidget {
  const JobSearchScreen({super.key});

  @override
  State<JobSearchScreen> createState() => _JobSearchScreenState();
}

class _JobSearchScreenState extends State<JobSearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  final TextEditingController _locationController = TextEditingController();
  
  String _selectedCategory = 'Toutes';
  String _selectedContractType = 'Tous';
  String _selectedStatus = 'Tous';
  
  final List<String> _categories = [
    'Toutes',
    'Informatique',
    'Commerce',
    'Administration',
    'Logistique',
    'Santé',
    'Éducation',
    'Autres'
  ];
  
  final List<String> _contractTypes = [
    'Tous',
    'CDI',
    'CDD',
    'Mission',
    'Stage',
    'Freelance'
  ];
  
  final List<String> _statuses = [
    'Tous',
    'Ouvert',
    'En cours',
    'Fermé'
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<JobProvider>().loadJobs();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  void _performSearch() {
    final jobProvider = context.read<JobProvider>();
    
    // Recherche par texte
    if (_searchController.text.isNotEmpty) {
      jobProvider.searchJobs(_searchController.text);
    }
    
    // Filtre par localisation
    if (_locationController.text.isNotEmpty) {
      jobProvider.filterJobsByLocation(_locationController.text);
    }
    
    // Filtre par catégorie
    if (_selectedCategory != 'Toutes') {
      jobProvider.filterJobsByCategory(_selectedCategory);
    }
    
    // Filtre par type de contrat
    if (_selectedContractType != 'Tous') {
      jobProvider.filterJobsByContractType(_selectedContractType);
    }
  }

  void _clearFilters() {
    setState(() {
      _searchController.clear();
      _locationController.clear();
      _selectedCategory = 'Toutes';
      _selectedContractType = 'Tous';
      _selectedStatus = 'Tous';
    });
    context.read<JobProvider>().loadJobs();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Recherche d\'emplois'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterBottomSheet,
          ),
        ],
      ),
      body: Column(
        children: [
          // Barre de recherche
          Container(
            padding: const EdgeInsets.all(16),
            color: AppTheme.primaryColor,
            child: Column(
              children: [
                CustomTextField(
                  controller: _searchController,
                  hint: 'Rechercher un emploi...',
                  prefixIcon: Icons.search,
                  onSubmitted: (_) => _performSearch(),
                ),
                const SizedBox(height: 12),
                CustomTextField(
                  controller: _locationController,
                  hint: 'Localisation...',
                  prefixIcon: Icons.location_on,
                  onSubmitted: (_) => _performSearch(),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: CustomButton(
                        text: 'Rechercher',
                        onPressed: _performSearch,
                        type: ButtonType.primary,
                        size: ButtonSize.medium,
                      ),
                    ),
                    const SizedBox(width: 12),
                    CustomButton(
                      text: 'Effacer',
                      onPressed: _clearFilters,
                      type: ButtonType.outlined,
                      size: ButtonSize.medium,
                    ),
                  ],
                ),
              ],
            ),
          ),
          
          // Résultats
          Expanded(
            child: Consumer<JobProvider>(
              builder: (context, jobProvider, child) {
                if (jobProvider.isLoading) {
                  return const Center(
                    child: CircularProgressIndicator(),
                  );
                }
                
                if (jobProvider.hasError) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.error_outline,
                          size: 64,
                          color: AppTheme.errorColor,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Erreur lors du chargement',
                          style: AppTheme.heading2,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          jobProvider.errorMessage,
                          style: AppTheme.bodyText,
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        CustomButton(
                          text: 'Réessayer',
                          onPressed: () => jobProvider.loadJobs(),
                          type: ButtonType.primary,
                        ),
                      ],
                    ),
                  );
                }
                
                final jobs = jobProvider.jobs;
                
                if (jobs.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.work_outline,
                          size: 64,
                          color: AppTheme.textSecondaryColor,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Aucun emploi trouvé',
                          style: AppTheme.heading2,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Essayez de modifier vos critères de recherche',
                          style: AppTheme.bodyText,
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  );
                }
                
                return RefreshIndicator(
                  onRefresh: () => jobProvider.loadJobs(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    physics: const BouncingScrollPhysics(),
                    itemCount: jobs.length,
                    itemBuilder: (context, index) {
                      final job = jobs[index];
                      return TweenAnimationBuilder<double>(
                        tween: Tween(begin: 0.0, end: 1.0),
                        duration: Duration(milliseconds: 300 + (index * 50)),
                        builder: (context, value, child) {
                          return Transform.translate(
                            offset: Offset(0, 30 * (1 - value)),
                            child: Opacity(
                              opacity: value,
                              child: Padding(
                                padding: const EdgeInsets.only(bottom: 12),
                                child: JobCard(
                                  job: job,
                                  onTap: () => _showJobDetails(job),
                                    onFavorite: () => _toggleFavorite(job),
                                ),
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Filtres',
                style: AppTheme.heading2,
              ),
              const SizedBox(height: 24),
              
              // Catégorie
              Text('Catégorie', style: AppTheme.subtitle),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                value: _selectedCategory,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                items: _categories.map((category) {
                  return DropdownMenuItem(
                    value: category,
                    child: Text(category),
                  );
                }).toList(),
                onChanged: (value) {
                  setModalState(() {
                    _selectedCategory = value!;
                  });
                },
              ),
              const SizedBox(height: 16),
              
              // Type de contrat
              Text('Type de contrat', style: AppTheme.subtitle),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                value: _selectedContractType,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                items: _contractTypes.map((type) {
                  return DropdownMenuItem(
                    value: type,
                    child: Text(type),
                  );
                }).toList(),
                onChanged: (value) {
                  setModalState(() {
                    _selectedContractType = value!;
                  });
                },
              ),
              const SizedBox(height: 16),
              
              // Statut
              Text('Statut', style: AppTheme.subtitle),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                value: _selectedStatus,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                items: _statuses.map((status) {
                  return DropdownMenuItem(
                    value: status,
                    child: Text(status),
                  );
                }).toList(),
                onChanged: (value) {
                  setModalState(() {
                    _selectedStatus = value!;
                  });
                },
              ),
              const SizedBox(height: 24),
              
              // Boutons
              Row(
                children: [
                  Expanded(
                    child: CustomButton(
                      text: 'Appliquer',
                      onPressed: () {
                        Navigator.pop(context);
                        _performSearch();
                      },
                      type: ButtonType.primary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: CustomButton(
                      text: 'Réinitialiser',
                      onPressed: () {
                        setModalState(() {
                          _selectedCategory = 'Toutes';
                          _selectedContractType = 'Tous';
                          _selectedStatus = 'Tous';
                        });
                      },
                      type: ButtonType.outlined,
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

  void _showJobDetails(Job job) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    job.title,
                    style: AppTheme.heading2,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              job.company,
              style: AppTheme.subtitle.copyWith(
                color: AppTheme.primaryColor,
              ),
            ),
            const SizedBox(height: 16),
            
            // Informations clés
            Row(
              children: [
                Icon(Icons.location_on, size: 16, color: AppTheme.textSecondaryColor),
                const SizedBox(width: 4),
                Text(job.location, style: AppTheme.bodyText),
                const SizedBox(width: 16),
                Icon(Icons.access_time, size: 16, color: AppTheme.textSecondaryColor),
                const SizedBox(width: 4),
                Text(job.contractType, style: AppTheme.bodyText),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.attach_money, size: 16, color: AppTheme.textSecondaryColor),
                const SizedBox(width: 4),
                Text('${job.salary} FCFA', style: AppTheme.bodyText),
                const SizedBox(width: 16),
                Icon(Icons.calendar_today, size: 16, color: AppTheme.textSecondaryColor),
                const SizedBox(width: 4),
                Text(job.postedDate, style: AppTheme.bodyText),
              ],
            ),
            const SizedBox(height: 16),
            
            // Description
            Text('Description', style: AppTheme.subtitle),
            const SizedBox(height: 8),
            Text(
              job.description,
              style: AppTheme.bodyText,
            ),
            const SizedBox(height: 16),
            
            // Exigences
            Text('Exigences', style: AppTheme.subtitle),
            const SizedBox(height: 8),
            Text(
              job.requirements,
              style: AppTheme.bodyText,
            ),
            const SizedBox(height: 24),
            
            // Bouton de candidature
            CustomButton(
              text: 'Postuler',
              onPressed: job.hasApplied ? null : () {
                Navigator.pop(context);
                _showCVSelectionDialogForJob(job);
              },
              type: ButtonType.primary,
              size: ButtonSize.large,
            ),
          ],
        ),
      ),
    );
  }


  void _toggleFavorite(Job job) {
    context.read<JobProvider>().toggleFavorite(job.id);
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          job.isFavorite 
            ? '${job.title} retiré des favoris'
            : '${job.title} ajouté aux favoris'
        ),
        backgroundColor: job.isFavorite ? Colors.orange : Colors.green,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _showCVSelectionDialogForJob(Job job) async {
    // Charger les CVs si ce n'est pas encore fait
    final cvProvider = context.read<CVProvider>();
    if (cvProvider.cvList.isEmpty) {
      await cvProvider.loadCVs();
    }

    if (mounted) {
      final result = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (BuildContext context) {
          return CVSelectionDialog(job: job);
        },
      );

      // Si la candidature a été envoyée avec succès, actualiser les données
      if (result == true) {
        setState(() {
          // Force rebuild to update UI
        });
      }
    }
  }
}