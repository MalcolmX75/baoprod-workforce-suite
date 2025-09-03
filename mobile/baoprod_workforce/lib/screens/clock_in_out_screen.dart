import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';

import '../providers/timesheet_provider.dart';
import '../widgets/custom_button.dart';
import '../utils/constants.dart';

class ClockInOutScreen extends StatefulWidget {
  const ClockInOutScreen({super.key});

  @override
  State<ClockInOutScreen> createState() => _ClockInOutScreenState();
}

class _ClockInOutScreenState extends State<ClockInOutScreen> {
  bool _isLoading = false;
  bool _isGettingLocation = false;
  Position? _currentPosition;
  String? _locationError;

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      _isGettingLocation = true;
      _locationError = null;
    });

    try {
      // Check location permission
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          setState(() {
            _locationError = 'Permission de localisation refusée';
            _isGettingLocation = false;
          });
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        setState(() {
          _locationError = 'Permission de localisation définitivement refusée';
          _isGettingLocation = false;
        });
        return;
      }

      // Get current position
      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 10),
      );

      setState(() {
        _currentPosition = position;
        _isGettingLocation = false;
      });
    } catch (e) {
      setState(() {
        _locationError = 'Erreur lors de la récupération de la position: $e';
        _isGettingLocation = false;
      });
    }
  }

  Future<void> _clockIn() async {
    if (_currentPosition == null) {
      _showErrorDialog('Impossible de pointer sans localisation');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final timesheetProvider = Provider.of<TimesheetProvider>(context, listen: false);
      final success = await timesheetProvider.clockIn(
        latitude: _currentPosition!.latitude,
        longitude: _currentPosition!.longitude,
      );

      if (success) {
        _showSuccessDialog('Pointage d\'entrée réussi !');
        context.pop();
      } else {
        _showErrorDialog('Erreur lors du pointage d\'entrée');
      }
    } catch (e) {
      _showErrorDialog('Erreur: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _clockOut() async {
    if (_currentPosition == null) {
      _showErrorDialog('Impossible de pointer sans localisation');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final timesheetProvider = Provider.of<TimesheetProvider>(context, listen: false);
      final success = await timesheetProvider.clockOut(
        latitude: _currentPosition!.latitude,
        longitude: _currentPosition!.longitude,
      );

      if (success) {
        _showSuccessDialog('Pointage de sortie réussi !');
        context.pop();
      } else {
        _showErrorDialog('Erreur lors du pointage de sortie');
      }
    } catch (e) {
      _showErrorDialog('Erreur: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
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
        title: const Text('Pointage'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: Consumer<TimesheetProvider>(
        builder: (context, timesheetProvider, child) {
          final currentTimesheet = timesheetProvider.currentTimesheet;
          final isClockedIn = currentTimesheet != null && currentTimesheet.status == 'EN_COURS';
          
          return Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Current Status Card
                Card(
                  elevation: 4,
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: [
                        Icon(
                          isClockedIn ? Icons.login : Icons.logout,
                          size: 64,
                          color: isClockedIn ? Colors.red : Colors.green,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          isClockedIn ? 'Vous êtes en service' : 'Vous n\'êtes pas en service',
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (isClockedIn && currentTimesheet != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            'Depuis: ${currentTimesheet.heureDebut}',
                            style: const TextStyle(
                              fontSize: 16,
                              color: Colors.grey,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                
                const SizedBox(height: 20),
                
                // Location Status
                _buildLocationStatus(),
                
                const SizedBox(height: 20),
                
                // Clock In/Out Button
                CustomButton(
                  text: isClockedIn ? 'Pointer Sortie' : 'Pointer Entrée',
                  onPressed: _isLoading || _isGettingLocation || _currentPosition == null
                      ? null
                      : (isClockedIn ? _clockOut : _clockIn),
                  backgroundColor: isClockedIn ? Colors.red : Colors.green,
                  icon: isClockedIn ? Icons.logout : Icons.login,
                  isLoading: _isLoading,
                ),
                
                const SizedBox(height: 20),
                
                // Additional Info
                if (isClockedIn && currentTimesheet != null)
                  _buildCurrentSessionInfo(currentTimesheet),
                
                const SizedBox(height: 20),
                
                // Location Details
                if (_currentPosition != null)
                  _buildLocationDetails(),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildLocationStatus() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Statut de Localisation',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            if (_isGettingLocation)
              const Row(
                children: [
                  SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                  SizedBox(width: 8),
                  Text('Récupération de la position...'),
                ],
              )
            else if (_locationError != null)
              Row(
                children: [
                  const Icon(Icons.error, color: Colors.red),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _locationError!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ),
                  TextButton(
                    onPressed: _getCurrentLocation,
                    child: const Text('Réessayer'),
                  ),
                ],
              )
            else if (_currentPosition != null)
              const Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.green),
                  SizedBox(width: 8),
                  Text('Position récupérée avec succès'),
                ],
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentSessionInfo(dynamic currentTimesheet) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Session Actuelle',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Heure d\'entrée:'),
                Text(
                  currentTimesheet.heureDebut,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Durée:'),
                Text(
                  _calculateDuration(currentTimesheet.heureDebut),
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLocationDetails() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Détails de Localisation',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Latitude:'),
                Text(_currentPosition!.latitude.toStringAsFixed(6)),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Longitude:'),
                Text(_currentPosition!.longitude.toStringAsFixed(6)),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Précision:'),
                Text('${_currentPosition!.accuracy.toStringAsFixed(1)}m'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _calculateDuration(String startTime) {
    try {
      final now = DateTime.now();
      final start = DateTime.parse('${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')} $startTime');
      final duration = now.difference(start);
      
      final hours = duration.inHours;
      final minutes = duration.inMinutes % 60;
      
      return '${hours}h ${minutes}m';
    } catch (e) {
      return 'N/A';
    }
  }
}