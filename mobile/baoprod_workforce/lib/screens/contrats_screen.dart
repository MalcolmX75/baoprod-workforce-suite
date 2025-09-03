import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../utils/constants.dart';

class ContratsScreen extends StatefulWidget {
  const ContratsScreen({super.key});

  @override
  State<ContratsScreen> createState() => _ContratsScreenState();
}

class _ContratsScreenState extends State<ContratsScreen> {
  List<dynamic> _contrats = [];
  bool _isLoading = true;
  String _selectedFilter = 'Tous';
  final List<String> _filterOptions = ['Tous', 'Actifs', 'Expirés', 'En attente'];

  @override
  void initState() {
    super.initState();
    _loadContrats();
  }

  Future<void> _loadContrats() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // TODO: Load contrats from API
      // Simulate API call
      await Future.delayed(const Duration(seconds: 1));
      
      // Mock data for now
      _contrats = [
        {
          'id': 1,
          'type': 'CDI',
          'poste': 'Développeur Web',
          'entreprise': 'JLC Gabon',
          'salaire_brut': 150000,
          'date_debut': '2025-01-01',
          'date_fin': null,
          'statut': 'ACTIF',
          'pays': 'Gabon',
        },
        {
          'id': 2,
          'type': 'CDD',
          'poste': 'Comptable',
          'entreprise': 'JLC Gabon',
          'salaire_brut': 120000,
          'date_debut': '2025-02-01',
          'date_fin': '2025-12-31',
          'statut': 'ACTIF',
          'pays': 'Gabon',
        },
        {
          'id': 3,
          'type': 'Mission',
          'poste': 'Assistant Administratif',
          'entreprise': 'JLC Gabon',
          'salaire_brut': 80000,
          'date_debut': '2024-12-01',
          'date_fin': '2025-01-31',
          'statut': 'TERMINE',
          'pays': 'Gabon',
        },
      ];
    } catch (e) {
      _showErrorDialog('Erreur lors du chargement des contrats: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  List<dynamic> _getFilteredContrats() {
    switch (_selectedFilter) {
      case 'Actifs':
        return _contrats.where((c) => c['statut'] == 'ACTIF').toList();
      case 'Expirés':
        return _contrats.where((c) => c['statut'] == 'TERMINE').toList();
      case 'En attente':
        return _contrats.where((c) => c['statut'] == 'EN_ATTENTE_SIGNATURE').toList();
      default:
        return _contrats;
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
        title: const Text('Mes Contrats'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _buildContratsList(),
    );
  }

  Widget _buildContratsList() {
    final filteredContrats = _getFilteredContrats();

    if (filteredContrats.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.description,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              _selectedFilter == 'Tous' 
                  ? 'Aucun contrat trouvé'
                  : 'Aucun contrat $_selectedFilter.toLowerCase() trouvé',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Vos contrats apparaîtront ici',
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
      onRefresh: _loadContrats,
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
          
          // Contrats List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: filteredContrats.length,
              itemBuilder: (context, index) {
                final contrat = filteredContrats[index];
                return _buildContratCard(contrat);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContratCard(Map<String, dynamic> contrat) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      child: InkWell(
        onTap: () {
          _showContratDetails(contrat);
        },
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header with type and status
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: _getContratTypeColor(contrat['type']),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      contrat['type'],
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  _buildStatusChip(contrat['statut']),
                ],
              ),
              
              const SizedBox(height: 12),
              
              // Job title
              Text(
                contrat['poste'],
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              
              const SizedBox(height: 4),
              
              // Company
              Text(
                contrat['entreprise'],
                style: const TextStyle(
                  fontSize: 16,
                  color: Colors.grey,
                ),
              ),
              
              const SizedBox(height: 12),
              
              // Details row
              Row(
                children: [
                  Expanded(
                    child: _buildDetailItem(
                      'Salaire',
                      '${contrat['salaire_brut']} FCFA',
                      Icons.attach_money,
                      Colors.green,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildDetailItem(
                      'Pays',
                      contrat['pays'],
                      Icons.flag,
                      Colors.blue,
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 12),
              
              // Date range
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    'Du ${contrat['date_debut']}',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  ),
                  if (contrat['date_fin'] != null) ...[
                    const Text(' au '),
                    Text(
                      contrat['date_fin'],
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                  ] else ...[
                    const Text(' (Indéterminé)'),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailItem(String label, String value, IconData icon, Color color) {
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
          value,
          style: const TextStyle(
            fontSize: 14,
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
      case 'ACTIF':
        backgroundColor = Colors.green;
        textColor = Colors.white;
        statusText = 'Actif';
        break;
      case 'TERMINE':
        backgroundColor = Colors.red;
        textColor = Colors.white;
        statusText = 'Terminé';
        break;
      case 'EN_ATTENTE_SIGNATURE':
        backgroundColor = Colors.orange;
        textColor = Colors.white;
        statusText = 'En attente';
        break;
      case 'SUSPENDU':
        backgroundColor = Colors.grey;
        textColor = Colors.white;
        statusText = 'Suspendu';
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

  Color _getContratTypeColor(String type) {
    switch (type) {
      case 'CDI':
        return Colors.green;
      case 'CDD':
        return Colors.blue;
      case 'Mission':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filtrer les contrats'),
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

  void _showContratDetails(Map<String, dynamic> contrat) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Contrat - ${contrat['poste']}'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow('Type', contrat['type']),
              _buildDetailRow('Poste', contrat['poste']),
              _buildDetailRow('Entreprise', contrat['entreprise']),
              _buildDetailRow('Salaire brut', '${contrat['salaire_brut']} FCFA'),
              _buildDetailRow('Pays', contrat['pays']),
              _buildDetailRow('Date de début', contrat['date_debut']),
              if (contrat['date_fin'] != null)
                _buildDetailRow('Date de fin', contrat['date_fin']),
              _buildDetailRow('Statut', _getStatusText(contrat['statut'])),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Fermer'),
          ),
          if (contrat['statut'] == 'EN_ATTENTE_SIGNATURE')
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                _showSignatureDialog(contrat);
              },
              child: const Text('Signer'),
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
            width: 100,
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
      case 'ACTIF':
        return 'Actif';
      case 'TERMINE':
        return 'Terminé';
      case 'EN_ATTENTE_SIGNATURE':
        return 'En attente de signature';
      case 'SUSPENDU':
        return 'Suspendu';
      default:
        return status;
    }
  }

  void _showSignatureDialog(Map<String, dynamic> contrat) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Signature du contrat'),
        content: const Text(
          'Voulez-vous signer ce contrat ? Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _signContrat(contrat['id']);
            },
            child: const Text('Signer'),
          ),
        ],
      ),
    );
  }

  void _signContrat(int contratId) {
    // TODO: Implement contract signing
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Contrat signé avec succès !'),
        backgroundColor: Colors.green,
      ),
    );
  }
}