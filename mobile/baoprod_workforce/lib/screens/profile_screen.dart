import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/auth_provider.dart';
import '../utils/constants.dart';
import '../widgets/custom_button.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: () {
              // TODO: Naviguer vers l'édition du profil
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          final user = authProvider.user;
          
          if (user == null) {
            return const Center(
              child: Text('Utilisateur non connecté'),
            );
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(AppConstants.defaultPadding),
            child: Column(
              children: [
                // En-tête du profil
                _buildProfileHeader(context, user),
                
                const SizedBox(height: 24),
                
                // Informations personnelles
                _buildPersonalInfo(context, user),
                
                const SizedBox(height: 24),
                
                // Actions du profil
                _buildProfileActions(context),
                
                const SizedBox(height: 24),
                
                // Bouton de déconnexion
                _buildLogoutButton(context, authProvider),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildProfileHeader(BuildContext context, user) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            // Avatar
            CircleAvatar(
              radius: 50,
              backgroundColor: AppTheme.primaryColor,
              child: user.avatar != null
                  ? ClipOval(
                      child: Image.network(
                        user.avatar!,
                        width: 100,
                        height: 100,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Icon(
                            Icons.person,
                            size: 50,
                            color: Colors.white,
                          );
                        },
                      ),
                    )
                  : Icon(
                      Icons.person,
                      size: 50,
                      color: Colors.white,
                    ),
            ),
            
            const SizedBox(height: 16),
            
            // Nom
            Text(
              user.displayName,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            
            const SizedBox(height: 4),
            
            // Email
            Text(
              user.email,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
            ),
            
            const SizedBox(height: 8),
            
            // Type d'utilisateur
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text(
                _getUserTypeText(user.type),
                style: TextStyle(
                  color: AppTheme.primaryColor,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPersonalInfo(BuildContext context, user) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Informations personnelles',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            
            const SizedBox(height: 16),
            
            _buildInfoRow(
              context,
              icon: Icons.person,
              label: 'Prénom',
              value: user.firstName,
            ),
            
            const SizedBox(height: 12),
            
            _buildInfoRow(
              context,
              icon: Icons.person,
              label: 'Nom',
              value: user.lastName,
            ),
            
            if (user.phone != null) ...[
              const SizedBox(height: 12),
              _buildInfoRow(
                context,
                icon: Icons.phone,
                label: 'Téléphone',
                value: user.phone!,
              ),
            ],
            
            const SizedBox(height: 12),
            
            _buildInfoRow(
              context,
              icon: Icons.email,
              label: 'Email',
              value: user.email,
            ),
            
            const SizedBox(height: 12),
            
            _buildInfoRow(
              context,
              icon: Icons.calendar_today,
              label: 'Membre depuis',
              value: _formatDate(user.createdAt),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(
          icon,
          color: AppTheme.textSecondaryColor,
          size: 20,
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppTheme.textSecondaryColor,
                ),
              ),
              Text(
                value,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildProfileActions(BuildContext context) {
    return Card(
      child: Column(
        children: [
          _buildActionTile(
            context,
            icon: Icons.edit,
            title: 'Modifier le profil',
            subtitle: 'Mettre à jour vos informations',
            onTap: () {
              // TODO: Naviguer vers l'édition du profil
            },
          ),
          
          const Divider(height: 1),
          
          _buildActionTile(
            context,
            icon: Icons.security,
            title: 'Sécurité',
            subtitle: 'Mot de passe et authentification',
            onTap: () {
              // TODO: Naviguer vers les paramètres de sécurité
            },
          ),
          
          const Divider(height: 1),
          
          _buildActionTile(
            context,
            icon: Icons.notifications,
            title: 'Notifications',
            subtitle: 'Gérer vos préférences',
            onTap: () {
              // TODO: Naviguer vers les paramètres de notifications
            },
          ),
          
          const Divider(height: 1),
          
          _buildActionTile(
            context,
            icon: Icons.help_outline,
            title: 'Aide et support',
            subtitle: 'FAQ et contact',
            onTap: () {
              // TODO: Naviguer vers l'aide
            },
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: AppTheme.primaryColor,
      ),
      title: Text(title),
      subtitle: Text(subtitle),
      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
      onTap: onTap,
    );
  }

  Widget _buildLogoutButton(BuildContext context, AuthProvider authProvider) {
    return CustomButton(
      text: 'Se déconnecter',
      onPressed: () async {
        final confirmed = await _showLogoutDialog(context);
        if (confirmed && context.mounted) {
          await authProvider.logout();
          if (context.mounted) {
            context.go('/auth/login');
          }
        }
      },
      type: ButtonType.danger,
      icon: Icons.logout,
    );
  }

  Future<bool> _showLogoutDialog(BuildContext context) async {
    return await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Êtes-vous sûr de vouloir vous déconnecter ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    ) ?? false;
  }

  String _getUserTypeText(String type) {
    switch (type) {
      case 'admin':
        return 'Administrateur';
      case 'employer':
        return 'Employeur';
      case 'employee':
        return 'Employé';
      default:
        return 'Utilisateur';
    }
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}