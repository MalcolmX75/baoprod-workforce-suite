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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paramètres'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/dashboard'),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppConstants.defaultPadding),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Paramètres généraux
            _buildSection(
              context,
              title: 'Général',
              children: [
                _buildSwitchTile(
                  context,
                  icon: Icons.notifications,
                  title: 'Notifications',
                  subtitle: 'Recevoir des notifications push',
                  value: _notificationsEnabled,
                  onChanged: (value) {
                    setState(() {
                      _notificationsEnabled = value;
                    });
                  },
                ),
                _buildSwitchTile(
                  context,
                  icon: Icons.dark_mode,
                  title: 'Mode sombre',
                  subtitle: 'Activer le thème sombre',
                  value: _darkModeEnabled,
                  onChanged: (value) {
                    setState(() {
                      _darkModeEnabled = value;
                    });
                  },
                ),
              ],
            ),
            
            const SizedBox(height: 24),
            
            // Paramètres de localisation
            _buildSection(
              context,
              title: 'Localisation',
              children: [
                _buildSwitchTile(
                  context,
                  icon: Icons.location_on,
                  title: 'Géolocalisation',
                  subtitle: 'Autoriser l\'accès à la position',
                  value: _locationEnabled,
                  onChanged: (value) {
                    setState(() {
                      _locationEnabled = value;
                    });
                  },
                ),
                _buildActionTile(
                  context,
                  icon: Icons.location_searching,
                  title: 'Précision de localisation',
                  subtitle: 'Configurer la précision GPS',
                  onTap: () {
                    _showLocationAccuracyDialog(context);
                  },
                ),
              ],
            ),
            
            const SizedBox(height: 24),
            
            // Paramètres de sécurité
            _buildSection(
              context,
              title: 'Sécurité',
              children: [
                _buildActionTile(
                  context,
                  icon: Icons.lock,
                  title: 'Changer le mot de passe',
                  subtitle: 'Modifier votre mot de passe',
                  onTap: () {
                    _showChangePasswordDialog(context);
                  },
                ),
                _buildActionTile(
                  context,
                  icon: Icons.fingerprint,
                  title: 'Authentification biométrique',
                  subtitle: 'Utiliser l\'empreinte digitale',
                  onTap: () {
                    _showBiometricDialog(context);
                  },
                ),
              ],
            ),
            
            const SizedBox(height: 24),
            
            // Paramètres de l'application
            _buildSection(
              context,
              title: 'Application',
              children: [
                _buildActionTile(
                  context,
                  icon: Icons.info,
                  title: 'À propos',
                  subtitle: 'Version et informations',
                  onTap: () {
                    _showAboutDialog(context);
                  },
                ),
                _buildActionTile(
                  context,
                  icon: Icons.help,
                  title: 'Aide',
                  subtitle: 'FAQ et support',
                  onTap: () {
                    _showHelpDialog(context);
                  },
                ),
                _buildActionTile(
                  context,
                  icon: Icons.privacy_tip,
                  title: 'Confidentialité',
                  subtitle: 'Politique de confidentialité',
                  onTap: () {
                    _showPrivacyDialog(context);
                  },
                ),
              ],
            ),
            
            const SizedBox(height: 24),
            
            // Actions dangereuses
            _buildSection(
              context,
              title: 'Actions',
              children: [
                _buildActionTile(
                  context,
                  icon: Icons.delete_forever,
                  title: 'Supprimer le compte',
                  subtitle: 'Supprimer définitivement votre compte',
                  onTap: () {
                    _showDeleteAccountDialog(context);
                  },
                  textColor: AppTheme.errorColor,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSection(
    BuildContext context, {
    required String title,
    required List<Widget> children,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
            color: AppTheme.primaryColor,
          ),
        ),
        const SizedBox(height: 12),
        Card(
          child: Column(
            children: children,
          ),
        ),
      ],
    );
  }

  Widget _buildSwitchTile(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: AppTheme.primaryColor,
      ),
      title: Text(title),
      subtitle: Text(subtitle),
      trailing: Switch(
        value: value,
        onChanged: onChanged,
        activeColor: AppTheme.primaryColor,
      ),
    );
  }

  Widget _buildActionTile(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color? textColor,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: textColor ?? AppTheme.primaryColor,
      ),
      title: Text(
        title,
        style: TextStyle(
          color: textColor,
        ),
      ),
      subtitle: Text(subtitle),
      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
      onTap: onTap,
    );
  }

  void _showLocationAccuracyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Précision de localisation'),
        content: const Text(
          'Configurez la précision de géolocalisation pour le pointage. '
          'Une précision plus élevée nécessite plus de batterie.',
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

  void _showChangePasswordDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Changer le mot de passe'),
        content: const Text(
          'Cette fonctionnalité sera disponible dans une prochaine version.',
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

  void _showBiometricDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Authentification biométrique'),
        content: const Text(
          'Cette fonctionnalité sera disponible dans une prochaine version.',
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

  void _showAboutDialog(BuildContext context) {
    showAboutDialog(
      context: context,
      applicationName: 'BaoProd Workforce',
      applicationVersion: '1.0.0',
      applicationIcon: Container(
        width: 64,
        height: 64,
        decoration: BoxDecoration(
          color: AppTheme.primaryColor,
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Icon(
          Icons.work_outline,
          color: Colors.white,
          size: 32,
        ),
      ),
      children: [
        const Text(
          'Application mobile de gestion d\'intérim et de staffing '
          'pour les entreprises de la zone CEMAC.',
        ),
      ],
    );
  }

  void _showHelpDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Aide et support'),
        content: const Text(
          'Pour toute question ou problème, contactez notre support technique '
          'à l\'adresse support@baoprod.com',
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

  void _showPrivacyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Politique de confidentialité'),
        content: const Text(
          'Vos données personnelles sont protégées et utilisées uniquement '
          'pour les fonctionnalités de l\'application. Nous ne partageons '
          'pas vos informations avec des tiers.',
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

  void _showDeleteAccountDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer le compte'),
        content: const Text(
          'Cette action est irréversible. Toutes vos données seront '
          'supprimées définitivement.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Implémenter la suppression du compte
            },
            child: const Text(
              'Supprimer',
              style: TextStyle(color: AppTheme.errorColor),
            ),
          ),
        ],
      ),
    );
  }
}