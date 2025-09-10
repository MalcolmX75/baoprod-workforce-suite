import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/document_provider.dart';
import '../providers/cv_provider.dart';

class DocumentsScreen extends StatefulWidget {
  const DocumentsScreen({super.key});

  @override
  State<DocumentsScreen> createState() => _DocumentsScreenState();
}

class _DocumentsScreenState extends State<DocumentsScreen>
    with TickerProviderStateMixin {
  late TabController _tabController;
  late AnimationController _fadeController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fadeController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(_fadeController);
    _fadeController.forward();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DocumentProvider>().loadDocumentRequests();
      context.read<CVProvider>().loadCVs();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _fadeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        elevation: 1,
        shadowColor: Colors.black26,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        title: const Row(
          children: [
            Icon(Icons.folder_open, size: 24),
            SizedBox(width: 8),
            Text(
              'Mes Documents',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: Theme.of(context).primaryColor,
          unselectedLabelColor: Colors.grey[600],
          indicatorColor: Theme.of(context).primaryColor,
          tabs: [
            Tab(
              icon: const Icon(Icons.description),
              text: 'Documents',
            ),
            Tab(
              icon: const Icon(Icons.person),
              text: 'CVs',
            ),
            Tab(
              icon: const Icon(Icons.cloud_upload),
              text: 'Téléversements',
            ),
          ],
        ),
        actions: [
          PopupMenuButton(
            icon: const Icon(Icons.more_vert),
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'refresh',
                child: ListTile(
                  leading: Icon(Icons.refresh),
                  title: Text('Actualiser'),
                  dense: true,
                  contentPadding: EdgeInsets.zero,
                ),
              ),
              const PopupMenuItem(
                value: 'help',
                child: ListTile(
                  leading: Icon(Icons.help_outline),
                  title: Text('Aide'),
                  dense: true,
                  contentPadding: EdgeInsets.zero,
                ),
              ),
            ],
            onSelected: (value) {
              switch (value) {
                case 'refresh':
                  _refreshData();
                  break;
                case 'help':
                  _showHelpDialog();
                  break;
              }
            },
          ),
        ],
      ),
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: TabBarView(
          controller: _tabController,
          children: [
            _buildDocumentsTab(),
            _buildCVsTab(),
            _buildUploadsTab(),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddDocumentDialog,
        backgroundColor: Theme.of(context).primaryColor,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Ajouter'),
      ),
    );
  }

  Widget _buildDocumentsTab() {
    return Consumer<DocumentProvider>(
      builder: (context, documentProvider, _) {
        if (documentProvider.isLoading && documentProvider.requests.isEmpty) {
          return const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Chargement des documents...'),
              ],
            ),
          );
        }

        if (documentProvider.hasError) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.error_outline,
                  size: 64,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: 16),
                Text(
                  documentProvider.error!,
                  style: TextStyle(color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () => documentProvider.loadDocumentRequests(refresh: true),
                  child: const Text('Réessayer'),
                ),
              ],
            ),
          );
        }

        final documents = documentProvider.requests;

        if (documents.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.description_outlined,
                  size: 64,
                  color: Colors.grey[300],
                ),
                const SizedBox(height: 16),
                Text(
                  'Aucun document',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Vos documents demandés apparaîtront ici',
                  style: TextStyle(color: Colors.grey[600]),
                ),
              ],
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () => documentProvider.loadDocumentRequests(refresh: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: documents.length,
            itemBuilder: (context, index) {
              final document = documents[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: _getStatusColor(document.status).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      _getDocumentIcon(document.type),
                      color: _getStatusColor(document.status),
                      size: 24,
                    ),
                  ),
                  title: Text(
                    document.title,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(document.typeText),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(
                            Icons.circle,
                            size: 8,
                            color: _getStatusColor(document.status),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            document.statusText,
                            style: TextStyle(
                              color: _getStatusColor(document.status),
                              fontWeight: FontWeight.w500,
                              fontSize: 12,
                            ),
                          ),
                          const Spacer(),
                          Text(
                            document.formattedRequestDate,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  isThreeLine: true,
                  trailing: document.canDownload
                      ? IconButton(
                          icon: const Icon(Icons.download),
                          onPressed: () => _downloadDocument(document.id),
                        )
                      : null,
                  onTap: () => _showDocumentDetails(document),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildCVsTab() {
    return Consumer<CVProvider>(
      builder: (context, cvProvider, _) {
        if (cvProvider.isLoading && cvProvider.cvs.isEmpty) {
          return const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Chargement des CVs...'),
              ],
            ),
          );
        }

        if (cvProvider.hasError) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.error_outline,
                  size: 64,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: 16),
                Text(
                  cvProvider.error!,
                  style: TextStyle(color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () => cvProvider.loadCVs(refresh: true),
                  child: const Text('Réessayer'),
                ),
              ],
            ),
          );
        }

        final cvs = cvProvider.cvs;

        if (cvs.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.person_outline,
                  size: 64,
                  color: Colors.grey[300],
                ),
                const SizedBox(height: 16),
                Text(
                  'Aucun CV',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Ajoutez votre premier CV pour postuler aux offres',
                  style: TextStyle(color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () => _showAddCVDialog(),
                  icon: const Icon(Icons.add),
                  label: const Text('Ajouter un CV'),
                ),
              ],
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () => cvProvider.loadCVs(refresh: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: cvs.length,
            itemBuilder: (context, index) {
              final cv = cvs[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: cv.isDefault
                          ? Theme.of(context).primaryColor.withOpacity(0.1)
                          : Colors.grey.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      Icons.person,
                      color: cv.isDefault
                          ? Theme.of(context).primaryColor
                          : Colors.grey[600],
                      size: 24,
                    ),
                  ),
                  title: Row(
                    children: [
                      Expanded(
                        child: Text(
                          cv.name,
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                      ),
                      if (cv.isDefault)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.green,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Text(
                            'Défaut',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                    ],
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('${cv.formattedFileSize} • ${cv.displayExtension.toUpperCase()}'),
                      const SizedBox(height: 4),
                      Text(
                        'Modifié le ${cv.updatedAt.day}/${cv.updatedAt.month}/${cv.updatedAt.year}',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                  isThreeLine: true,
                  trailing: PopupMenuButton(
                    itemBuilder: (context) => [
                      if (!cv.isDefault)
                        const PopupMenuItem(
                          value: 'set_default',
                          child: ListTile(
                            leading: Icon(Icons.star),
                            title: Text('Définir par défaut'),
                            dense: true,
                            contentPadding: EdgeInsets.zero,
                          ),
                        ),
                      const PopupMenuItem(
                        value: 'edit',
                        child: ListTile(
                          leading: Icon(Icons.edit),
                          title: Text('Modifier'),
                          dense: true,
                          contentPadding: EdgeInsets.zero,
                        ),
                      ),
                      const PopupMenuItem(
                        value: 'download',
                        child: ListTile(
                          leading: Icon(Icons.download),
                          title: Text('Télécharger'),
                          dense: true,
                          contentPadding: EdgeInsets.zero,
                        ),
                      ),
                      const PopupMenuItem(
                        value: 'delete',
                        child: ListTile(
                          leading: Icon(Icons.delete, color: Colors.red),
                          title: Text('Supprimer'),
                          dense: true,
                          contentPadding: EdgeInsets.zero,
                        ),
                      ),
                    ],
                    onSelected: (value) => _handleCVAction(value.toString(), cv.id),
                  ),
                  onTap: () => _showCVPreview(cv),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildUploadsTab() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.cloud_upload,
                        color: Theme.of(context).primaryColor,
                        size: 32,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Zone de téléversement',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              'Ajoutez vos documents et CVs',
                              style: TextStyle(color: Colors.grey[600]),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _uploadDocument,
                          icon: const Icon(Icons.description),
                          label: const Text('Document'),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.all(16),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _uploadCV,
                          icon: const Icon(Icons.person),
                          label: const Text('CV'),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.all(16),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Types de fichiers acceptés',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 12),
                  _buildFileTypeInfo('PDF', 'Documents PDF (max 10 MB)', Icons.picture_as_pdf),
                  _buildFileTypeInfo('Word', 'Documents Word (.doc, .docx)', Icons.description),
                  _buildFileTypeInfo('Images', 'JPG, PNG (max 5 MB)', Icons.image),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFileTypeInfo(String type, String description, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, color: Colors.grey[600], size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  type,
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
                Text(
                  description,
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  IconData _getDocumentIcon(type) {
    switch (type.toString()) {
      case 'DocumentType.contract':
        return Icons.description;
      case 'DocumentType.payslip':
        return Icons.receipt_long;
      case 'DocumentType.certificate':
        return Icons.verified;
      case 'DocumentType.recommendation':
        return Icons.recommend;
      default:
        return Icons.description;
    }
  }

  Color _getStatusColor(status) {
    switch (status.toString()) {
      case 'RequestStatus.pending':
        return Colors.orange;
      case 'RequestStatus.processing':
        return Colors.blue;
      case 'RequestStatus.ready':
        return Colors.green;
      case 'RequestStatus.completed':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  void _refreshData() {
    context.read<DocumentProvider>().loadDocumentRequests(refresh: true);
    context.read<CVProvider>().loadCVs(refresh: true);
  }

  void _showHelpDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Aide - Documents'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Gestion de vos documents:',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            SizedBox(height: 8),
            Text('• Consultez vos documents demandés dans l\'onglet Documents'),
            Text('• Gérez vos CVs dans l\'onglet CVs'),
            Text('• Téléversez de nouveaux fichiers dans l\'onglet Téléversements'),
            Text('• Définissez un CV par défaut pour vos candidatures'),
            Text('• Téléchargez vos documents quand ils sont prêts'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Compris'),
          ),
        ],
      ),
    );
  }

  void _showAddDocumentDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ajouter un document'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: Icon(Icons.description),
              title: Text('Document personnel'),
              subtitle: Text('Téléverser un document existant'),
              contentPadding: EdgeInsets.zero,
            ),
            ListTile(
              leading: Icon(Icons.person),
              title: Text('Nouveau CV'),
              subtitle: Text('Créer ou téléverser un CV'),
              contentPadding: EdgeInsets.zero,
            ),
            ListTile(
              leading: Icon(Icons.request_page),
              title: Text('Demande de document'),
              subtitle: Text('Demander un document à votre employeur'),
              contentPadding: EdgeInsets.zero,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
        ],
      ),
    );
  }

  void _showAddCVDialog() {
    // TODO: Implémenter l'ajout de CV
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Fonctionnalité d\'ajout de CV à venir'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _downloadDocument(int documentId) async {
    final success = await context.read<DocumentProvider>().downloadDocument(documentId);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.download_done, color: Colors.white, size: 20),
              SizedBox(width: 8),
              Text('Document téléchargé avec succès'),
            ],
          ),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  void _showDocumentDetails(document) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(document.title),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildDetailRow('Type', document.typeText),
            _buildDetailRow('Statut', document.statusText),
            _buildDetailRow('Demandé le', document.formattedRequestDate),
            if (document.description.isNotEmpty)
              _buildDetailRow('Description', document.description),
            if (document.completedDate != null)
              _buildDetailRow('Complété le', document.formattedCompletedDate ?? ''),
          ],
        ),
        actions: [
          if (document.canDownload)
            ElevatedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                _downloadDocument(document.id);
              },
              icon: const Icon(Icons.download),
              label: const Text('Télécharger'),
            ),
          TextButton(
            onPressed: () => Navigator.pop(context),
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
            width: 80,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }

  void _showCVPreview(cv) {
    // TODO: Implémenter la prévisualisation du CV
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Prévisualisation du CV à venir'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _handleCVAction(String action, int cvId) {
    switch (action) {
      case 'set_default':
        context.read<CVProvider>().setDefaultCV(cvId);
        break;
      case 'edit':
        // TODO: Implémenter l'édition de CV
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Édition de CV à venir')),
        );
        break;
      case 'download':
        // TODO: Implémenter le téléchargement de CV
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Téléchargement de CV à venir')),
        );
        break;
      case 'delete':
        _showDeleteCVDialog(cvId);
        break;
    }
  }

  void _showDeleteCVDialog(int cvId) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer le CV'),
        content: const Text(
          'Êtes-vous sûr de vouloir supprimer ce CV ? Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              context.read<CVProvider>().deleteCV(cvId);
              Navigator.pop(context);
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text(
              'Supprimer',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  void _uploadDocument() {
    // TODO: Implémenter le téléversement de document
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Téléversement de document à venir'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _uploadCV() {
    // TODO: Implémenter le téléversement de CV
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Téléversement de CV à venir'),
        duration: Duration(seconds: 2),
      ),
    );
  }
}