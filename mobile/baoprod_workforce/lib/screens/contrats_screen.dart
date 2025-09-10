import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/document_provider.dart';
import '../models/document_request.dart';
import '../widgets/custom_button.dart';
import '../widgets/document_request_dialog.dart';
import '../widgets/electronic_signature_dialog.dart';

class ContratsScreen extends StatefulWidget {
  const ContratsScreen({super.key});

  @override
  State<ContratsScreen> createState() => _ContratsScreenState();
}

class _ContratsScreenState extends State<ContratsScreen> with TickerProviderStateMixin {
  late TabController _tabController;

  // Données de démonstration pour les contrats
  final List<Map<String, dynamic>> _demoContrats = [
    {
      'id': 1,
      'title': 'Développeur Web Laravel - BaoProd',
      'type': 'CDI',
      'status': 'actif',
      'startDate': '2025-01-15',
      'endDate': null,
      'salary': '150000 FCFA',
      'location': 'Libreville, Gabon',
      'department': 'Informatique',
      'manager': 'Marie Ngoma',
      'description': 'Contrat de travail pour le poste de développeur web spécialisé Laravel au sein de l\'équipe technique.',
      'benefits': ['Assurance santé', 'Formation continue', 'Prime de transport', '13ème mois'],
    },
    {
      'id': 2,
      'title': 'Mission Comptable - Cabinet Expertise',
      'type': 'Mission',
      'status': 'terminé',
      'startDate': '2024-10-01',
      'endDate': '2024-12-31',
      'salary': '120000 FCFA',
      'location': 'Port-Gentil, Gabon',
      'department': 'Finance',
      'manager': 'Jean Obame',
      'description': 'Mission de 3 mois pour l\'audit des comptes et la préparation des états financiers.',
      'benefits': ['Prime de mission', 'Hébergement fourni'],
    },
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DocumentProvider>().loadDocumentRequests();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Contrats & Documents'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: _showNewDocumentRequestDialog,
            tooltip: 'Nouvelle demande',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            const Tab(
              icon: Icon(Icons.description),
              text: 'Mes Contrats',
            ),
            Consumer<DocumentProvider>(
              builder: (context, provider, child) {
                return Tab(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.request_page),
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('Demandes'),
                          if (provider.pendingCount > 0) ...[
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.orange,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '${provider.pendingCount}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
            Consumer<DocumentProvider>(
              builder: (context, provider, child) {
                return Tab(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.check_circle),
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('Prêts'),
                          if (provider.readyCount > 0) ...[
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.green,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '${provider.readyCount}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildContractsTab(),
          _buildDocumentRequestsTab(),
          _buildReadyDocumentsTab(),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showNewDocumentRequestDialog,
        icon: const Icon(Icons.add),
        label: const Text('Nouvelle demande'),
        backgroundColor: Theme.of(context).primaryColor,
      ),
    );
  }

  Widget _buildContractsTab() {
    return RefreshIndicator(
      onRefresh: () async {
        // Simulation de rafraîchissement
        await Future.delayed(const Duration(milliseconds: 500));
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _demoContrats.length,
        itemBuilder: (context, index) {
          final contract = _demoContrats[index];
          return _buildContractCard(contract);
        },
      ),
    );
  }

  Widget _buildDocumentRequestsTab() {
    return Consumer<DocumentProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.requests.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (provider.hasError) {
          return _buildErrorWidget(provider.error!, provider);
        }

        final allRequests = provider.requests;
        if (allRequests.isEmpty) {
          return _buildEmptyState(
            icon: Icons.request_page,
            title: 'Aucune demande',
            subtitle: 'Vous n\'avez pas encore fait de demande de document',
            actionText: 'Faire une demande',
            onAction: _showNewDocumentRequestDialog,
          );
        }

        return RefreshIndicator(
          onRefresh: () => provider.loadDocumentRequests(refresh: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: allRequests.length,
            itemBuilder: (context, index) {
              return _buildDocumentRequestCard(allRequests[index]);
            },
          ),
        );
      },
    );
  }

  Widget _buildReadyDocumentsTab() {
    return Consumer<DocumentProvider>(
      builder: (context, provider, child) {
        final readyRequests = provider.readyRequests;
        
        if (readyRequests.isEmpty) {
          return _buildEmptyState(
            icon: Icons.check_circle_outline,
            title: 'Aucun document prêt',
            subtitle: 'Les documents prêts à télécharger ou signer apparaîtront ici',
          );
        }

        return RefreshIndicator(
          onRefresh: () => provider.loadDocumentRequests(refresh: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: readyRequests.length,
            itemBuilder: (context, index) {
              return _buildDocumentRequestCard(readyRequests[index]);
            },
          ),
        );
      },
    );
  }

  Widget _buildContractCard(Map<String, dynamic> contract) {
    final isActive = contract['status'] == 'actif';
    
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    contract['title'],
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isActive ? Colors.green.withOpacity(0.1) : Colors.grey.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    contract['status'].toString().toUpperCase(),
                    style: TextStyle(
                      color: isActive ? Colors.green : Colors.grey,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              contract['description'],
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildInfoChip(Icons.work, contract['type']),
                const SizedBox(width: 8),
                _buildInfoChip(Icons.attach_money, contract['salary']),
                const SizedBox(width: 8),
                _buildInfoChip(Icons.location_on, contract['location']),
              ],
            ),
            if (isActive) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _requestContractCopy(contract),
                      icon: const Icon(Icons.content_copy, size: 16),
                      label: const Text('Demander copie'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _showResignationDialog(contract),
                      icon: const Icon(Icons.exit_to_app, size: 16),
                      label: const Text('Démissionner'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentRequestCard(DocumentRequest request) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: request.statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    _getRequestIcon(request.type),
                    color: request.statusColor,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        request.title,
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        request.typeText,
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: request.statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    request.statusText,
                    style: TextStyle(
                      color: request.statusColor,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            if (request.description.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                request.description,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Text(
                  'Demandé ${request.formattedRequestDate}',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontSize: 12,
                  ),
                ),
                const Spacer(),
                if (request.isUrgent)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Text(
                      'URGENT',
                      style: TextStyle(
                        color: Colors.red,
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
              ],
            ),
            if (request.status == RequestStatus.ready) ...[
              const SizedBox(height: 12),
              Row(
                children: [
                  if (request.canDownload)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _downloadDocument(request),
                        icon: const Icon(Icons.download, size: 16),
                        label: const Text('Télécharger'),
                      ),
                    ),
                  if (request.needsSignature) ...[
                    if (request.canDownload) const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _signDocument(request),
                        icon: const Icon(Icons.edit, size: 16),
                        label: Text(
                          request.signatureType == SignatureType.electronic
                              ? 'Signer'
                              : 'Scanner',
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.grey[600]),
          const SizedBox(width: 4),
          Text(
            text,
            style: TextStyle(
              color: Colors.grey[600],
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState({
    required IconData icon,
    required String title,
    required String subtitle,
    String? actionText,
    VoidCallback? onAction,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            Text(
              title,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
            if (actionText != null && onAction != null) ...[
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: onAction,
                icon: const Icon(Icons.add),
                label: Text(actionText),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildErrorWidget(String error, DocumentProvider provider) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'Erreur de chargement',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text(
              error,
              style: Theme.of(context).textTheme.bodyMedium,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () => provider.loadDocumentRequests(refresh: true),
              icon: const Icon(Icons.refresh),
              label: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  IconData _getRequestIcon(DocumentType type) {
    switch (type) {
      case DocumentType.contract:
        return Icons.description;
      case DocumentType.payslip:
        return Icons.receipt;
      case DocumentType.certificate:
        return Icons.verified;
      case DocumentType.recommendation:
        return Icons.star;
      case DocumentType.other:
        return Icons.insert_drive_file;
    }
  }

  void _showNewDocumentRequestDialog() {
    showDialog(
      context: context,
      builder: (context) => const DocumentRequestDialog(),
    ).then((result) {
      if (result == true) {
        // Rafraîchir les données
        context.read<DocumentProvider>().loadDocumentRequests(refresh: true);
      }
    });
  }

  void _requestContractCopy(Map<String, dynamic> contract) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Demander une copie de contrat'),
        content: Text('Voulez-vous faire une demande de copie pour le contrat "${contract['title']}" ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              _createContractCopyRequest(contract);
            },
            child: const Text('Demander'),
          ),
        ],
      ),
    );
  }

  Future<void> _createContractCopyRequest(Map<String, dynamic> contract) async {
    final provider = context.read<DocumentProvider>();
    final success = await provider.createDocumentRequest(
      type: DocumentType.contract,
      title: 'Copie - ${contract['title']}',
      description: 'Demande de copie du contrat de travail avec signature électronique requise.',
      signatureType: SignatureType.electronic,
      isUrgent: false,
    );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Demande de copie envoyée avec succès'),
          backgroundColor: Colors.green,
        ),
      );
      // Basculer vers l'onglet des demandes
      _tabController.animateTo(1);
    }
  }

  void _showResignationDialog(Map<String, dynamic> contract) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Démission'),
        content: const Text(
          'Êtes-vous sûr de vouloir démissionner de ce poste ?\n\n'
          'Cette action créera une demande de rupture de contrat qui devra être validée par votre employeur.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              _createResignationRequest(contract);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Confirmer la démission'),
          ),
        ],
      ),
    );
  }

  Future<void> _createResignationRequest(Map<String, dynamic> contract) async {
    final provider = context.read<DocumentProvider>();
    final success = await provider.createDocumentRequest(
      type: DocumentType.other,
      title: 'Demande de démission - ${contract['title']}',
      description: 'Demande de rupture de contrat de travail (démission volontaire).',
      signatureType: SignatureType.electronic,
      isUrgent: true,
    );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Demande de démission envoyée'),
          backgroundColor: Colors.orange,
        ),
      );
      // Basculer vers l'onglet des demandes
      _tabController.animateTo(1);
    }
  }

  Future<void> _downloadDocument(DocumentRequest request) async {
    final provider = context.read<DocumentProvider>();
    final success = await provider.downloadDocument(request.id);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Document téléchargé avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  void _signDocument(DocumentRequest request) {
    if (request.signatureType == SignatureType.electronic) {
      showDialog(
        context: context,
        builder: (context) => ElectronicSignatureDialog(documentRequest: request),
      ).then((result) {
        if (result == true) {
          // Rafraîchir les données
          context.read<DocumentProvider>().loadDocumentRequests(refresh: true);
        }
      });
    } else {
      // Pour la signature physique, simuler l'upload d'un scan
      _simulatePhysicalSignature(request);
    }
  }

  Future<void> _simulatePhysicalSignature(DocumentRequest request) async {
    final provider = context.read<DocumentProvider>();
    final success = await provider.uploadSignedDocument(
      request.id,
      'path/to/scanned/document.pdf',
    );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Document scanné téléversé avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }
}