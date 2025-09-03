import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../utils/constants.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notificationsEnabled = true;
  bool _locationEnabled = true;
  bool _darkModeEnabled = false;
  String _selectedLanguage = 'fr';
  String _selectedCountry = 'GA';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  void _loadSettings() {
    // TODO: Load settings from storage
    setState(() {
      _notificationsEnabled = true;
      _locationEnabled = true;
      _darkModeEnabled = false;
      _selectedLanguage = 'fr';
      _selectedCountry = 'GA';
    });
  }

  void _saveSettings() {
    // TODO: Save settings to storage
    _showSuccessDialog('Paramètres sauvegardés avec succès !');
  }

  void _showSuccessDialog(String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Succès'),
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
        title: const Text('Paramètres'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          TextButton(
            onPressed: _saveSettings,
            child: const Text(
              'Sauvegarder',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
      body: ListView(
        children: [
          // Notifications Section
          _buildSection(
            'Notifications',
            Icons.notifications,
            [
              SwitchListTile(
                title: const Text('Notifications push'),
                subtitle: const Text('Recevoir des notifications sur votre appareil'),
                value: _notificationsEnabled,
                onChanged: (value) {
                  setState(() {
                    _notificationsEnabled = value;
                  });
                },
                secondary: const Icon(Icons.notifications_active),
              ),
            ],
          ),
          
          // Location Section
          _buildSection(
            'Localisation',
            Icons.location_on,
            [
              SwitchListTile(
                title: const Text('Géolocalisation'),
                subtitle: const Text('Autoriser l\'accès à votre position pour le pointage'),
                value: _locationEnabled,
                onChanged: (value) {
                  setState(() {
                    _locationEnabled = value;
                  });
                },
                secondary: const Icon(Icons.my_location),
              ),
            ],
          ),
          
          // Appearance Section
          _buildSection(
            'Apparence',
            Icons.palette,
            [
              SwitchListTile(
                title: const Text('Mode sombre'),
                subtitle: const Text('Activer le thème sombre'),
                value: _darkModeEnabled,
                onChanged: (value) {
                  setState(() {
                    _darkModeEnabled = value;
                  });
                },
                secondary: const Icon(Icons.dark_mode),
              ),
            ],
          ),
          
          // Language Section
          _buildSection(
            'Langue et Région',
            Icons.language,
            [
              ListTile(
                title: const Text('Langue'),
                subtitle: Text(_getLanguageName(_selectedLanguage)),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showLanguageDialog();
                },
                leading: const Icon(Icons.translate),
              ),
              ListTile(
                title: const Text('Pays'),
                subtitle: Text(_getCountryName(_selectedCountry)),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showCountryDialog();
                },
                leading: const Icon(Icons.flag),
              ),
            ],
          ),
          
          // Account Section
          _buildSection(
            'Compte',
            Icons.account_circle,
            [
              ListTile(
                title: const Text('Mon Profil'),
                subtitle: const Text('Gérer vos informations personnelles'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  context.push('/profile');
                },
                leading: const Icon(Icons.person),
              ),
              ListTile(
                title: const Text('Sécurité'),
                subtitle: const Text('Mot de passe et authentification'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showSecurityDialog();
                },
                leading: const Icon(Icons.security),
              ),
            ],
          ),
          
          // Data Section
          _buildSection(
            'Données',
            Icons.storage,
            [
              ListTile(
                title: const Text('Synchronisation'),
                subtitle: const Text('Synchroniser les données avec le serveur'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _syncData();
                },
                leading: const Icon(Icons.sync),
              ),
              ListTile(
                title: const Text('Cache'),
                subtitle: const Text('Vider le cache de l\'application'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _clearCache();
                },
                leading: const Icon(Icons.clear_all),
              ),
            ],
          ),
          
          // Support Section
          _buildSection(
            'Support',
            Icons.help,
            [
              ListTile(
                title: const Text('Aide'),
                subtitle: const Text('Centre d\'aide et FAQ'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showHelpDialog();
                },
                leading: const Icon(Icons.help_outline),
              ),
              ListTile(
                title: const Text('Contact'),
                subtitle: const Text('Contacter le support technique'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showContactDialog();
                },
                leading: const Icon(Icons.contact_support),
              ),
              ListTile(
                title: const Text('À propos'),
                subtitle: const Text('Informations sur l\'application'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showAboutDialog();
                },
                leading: const Icon(Icons.info_outline),
              ),
            ],
          ),
          
          // Danger Zone
          _buildSection(
            'Zone de Danger',
            Icons.warning,
            [
              ListTile(
                title: const Text('Supprimer le compte'),
                subtitle: const Text('Supprimer définitivement votre compte'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () {
                  _showDeleteAccountDialog();
                },
                leading: const Icon(Icons.delete_forever, color: Colors.red),
                textColor: Colors.red,
                iconColor: Colors.red,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSection(String title, IconData icon, List<Widget> children) {
    return Card(
      margin: const EdgeInsets.all(8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Icon(icon, color: AppTheme.primaryColor),
                const SizedBox(width: 12),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  void _showLanguageDialog() {
    final languages = [
      {'code': 'fr', 'name': 'Français'},
      {'code': 'en', 'name': 'English'},
    ];

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner la langue'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: languages.map((lang) => RadioListTile<String>(
            title: Text(lang['name']!),
            value: lang['code']!,
            groupValue: _selectedLanguage,
            onChanged: (value) {
              setState(() {
                _selectedLanguage = value!;
              });
              Navigator.of(context).pop();
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showCountryDialog() {
    final countries = [
      {'code': 'GA', 'name': 'Gabon'},
      {'code': 'CM', 'name': 'Cameroun'},
      {'code': 'TD', 'name': 'Tchad'},
      {'code': 'CF', 'name': 'RCA'},
      {'code': 'GQ', 'name': 'Guinée Équatoriale'},
      {'code': 'CG', 'name': 'Congo'},
    ];

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner le pays'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: countries.map((country) => RadioListTile<String>(
            title: Text(country['name']!),
            value: country['code']!,
            groupValue: _selectedCountry,
            onChanged: (value) {
              setState(() {
                _selectedCountry = value!;
              });
              Navigator.of(context).pop();
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showSecurityDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sécurité'),
        content: const Text('Options de sécurité du compte'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              context.push('/profile');
            },
            child: const Text('Changer le mot de passe'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }

  void _syncData() {
    showDialog(
      context: context,
      builder: (context) => const AlertDialog(
        title: Text('Synchronisation'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Synchronisation en cours...'),
          ],
        ),
      ),
    );

    // Simulate sync
    Future.delayed(const Duration(seconds: 2), () {
      Navigator.of(context).pop();
      _showSuccessDialog('Données synchronisées avec succès !');
    });
  }

  void _clearCache() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Vider le cache'),
        content: const Text('Êtes-vous sûr de vouloir vider le cache de l\'application ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _showSuccessDialog('Cache vidé avec succès !');
            },
            child: const Text('Vider'),
          ),
        ],
      ),
    );
  }

  void _showHelpDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Aide et Support'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Centre d\'aide disponible 24/7'),
            SizedBox(height: 8),
            Text('• FAQ'),
            Text('• Tutoriels vidéo'),
            Text('• Documentation'),
            Text('• Support technique'),
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

  void _showContactDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Contact Support'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Support technique BaoProd'),
            SizedBox(height: 16),
            Text('📧 Email: support@baoprod.com'),
            Text('📞 Téléphone: +241 XX XX XX XX'),
            Text('🌐 Site web: www.baoprod.com'),
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

  void _showAboutDialog() {
    showAboutDialog(
      context: context,
      applicationName: 'BaoProd Workforce',
      applicationVersion: '1.0.0',
      applicationIcon: const Icon(
        Icons.work,
        size: 48,
        color: AppTheme.primaryColor,
      ),
      children: [
        const Text('Application de gestion de workforce pour la zone CEMAC.'),
        const SizedBox(height: 16),
        const Text('Développé par BaoProd'),
        const Text('© 2025 Tous droits réservés'),
      ],
    );
  }

  void _showDeleteAccountDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer le compte'),
        content: const Text(
          'Êtes-vous sûr de vouloir supprimer définitivement votre compte ? '
          'Cette action est irréversible et toutes vos données seront perdues.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Implement account deletion
              _showSuccessDialog('Compte supprimé avec succès');
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  String _getLanguageName(String code) {
    switch (code) {
      case 'fr':
        return 'Français';
      case 'en':
        return 'English';
      default:
        return 'Français';
    }
  }

  String _getCountryName(String code) {
    switch (code) {
      case 'GA':
        return 'Gabon';
      case 'CM':
        return 'Cameroun';
      case 'TD':
        return 'Tchad';
      case 'CF':
        return 'RCA';
      case 'GQ':
        return 'Guinée Équatoriale';
      case 'CG':
        return 'Congo';
      default:
        return 'Gabon';
    }
  }
}