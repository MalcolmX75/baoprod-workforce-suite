// import 'package:permission_handler/permission_handler.dart'; // Temporarily removed
import '../utils/constants.dart';

class LocationService {
  static LocationService? _instance;
  static LocationService get instance => _instance ??= LocationService._();
  
  LocationService._();
  
  MockPosition? _currentPosition;
  MockPosition? get currentPosition => _currentPosition;
  
  /// Vérifie si les services de localisation sont activés (stub)
  Future<bool> isLocationServiceEnabled() async {
    return true; // Simuler que les services sont activés
  }
  
  /// Vérifie les permissions de localisation (stub)
  Future<MockLocationPermission> checkLocationPermission() async {
    return MockLocationPermission.whileInUse;
  }
  
  /// Demande les permissions de localisation (stub)
  Future<MockLocationPermission> requestLocationPermission() async {
    return MockLocationPermission.whileInUse;
  }
  
  /// Vérifie et demande les permissions si nécessaire
  Future<bool> ensureLocationPermission() async {
    // En mode démo, toujours réussir
    return true;
  }
  
  /// Obtient la position actuelle (position simulée de Libreville)
  Future<MockPosition> getCurrentPosition() async {
    await Future.delayed(Duration(seconds: 1)); // Simuler la latence GPS
    
    // Position simulée de Libreville, Gabon
    _currentPosition = MockPosition(
      latitude: 0.4162,
      longitude: 9.4673,
      accuracy: 10.0,
      altitude: 35.0,
      speed: 0.0,
      heading: 0.0,
      timestamp: DateTime.now(),
    );
    
    return _currentPosition!;
  }
  
  /// Obtient la dernière position connue
  Future<MockPosition?> getLastKnownPosition() async {
    return _currentPosition;
  }
  
  /// Calcule la distance entre deux positions (formule de Haversine simplifiée)
  double calculateDistance(
    double lat1, 
    double lon1, 
    double lat2, 
    double lon2
  ) {
    // Formule simplifiée pour la démo
    double deltaLat = (lat2 - lat1) * 111000; // Approximation en mètres
    double deltaLon = (lon2 - lon1) * 111000;
    return (deltaLat * deltaLat + deltaLon * deltaLon) / 1000; // Distance approximative
  }
  
  /// Vérifie si une position est dans un rayon donné
  bool isWithinRadius(
    double targetLat, 
    double targetLon, 
    double radiusInMeters
  ) {
    if (_currentPosition == null) return false;
    
    double distance = calculateDistance(
      _currentPosition!.latitude,
      _currentPosition!.longitude,
      targetLat,
      targetLon,
    );
    
    return distance <= radiusInMeters;
  }
  
  /// Obtient l'adresse à partir des coordonnées
  Future<String?> getAddressFromCoordinates(
    double latitude, 
    double longitude
  ) async {
    // Simuler une adresse pour Libreville
    if (latitude == 0.4162 && longitude == 9.4673) {
      return 'Libreville, Gabon';
    }
    return 'Lat: ${latitude.toStringAsFixed(6)}, Lng: ${longitude.toStringAsFixed(6)}';
  }
  
  /// Valide la précision d'une position
  bool isValidPosition(MockPosition position) {
    if (position.accuracy > AppConstants.locationAccuracy) {
      return false;
    }
    
    if (position.latitude == 0.0 && position.longitude == 0.0) {
      return false;
    }
    
    return true;
  }
  
  /// Obtient une position pour le pointage avec validation
  Future<MockPosition> getPositionForClockIn() async {
    MockPosition position = await getCurrentPosition();
    
    if (!isValidPosition(position)) {
      throw LocationException(
        'Position non valide. Précision: ${position.accuracy}m '
        '(minimum requis: ${AppConstants.locationAccuracy}m)'
      );
    }
    
    return position;
  }
  
  /// Ouvre les paramètres de localisation (stub)
  Future<void> openLocationSettings() async {
    print('📍 Ouverture des paramètres de localisation (simulé)');
  }
  
  /// Ouvre les paramètres de l'application (stub)
  Future<void> openAppSettings() async {
    print('⚙️ Ouverture des paramètres de l\'application (simulé)');
  }
  
  /// Formate une position pour l'affichage
  String formatPosition(MockPosition position) {
    return 'Lat: ${position.latitude.toStringAsFixed(6)}, '
           'Lng: ${position.longitude.toStringAsFixed(6)}, '
           'Précision: ${position.accuracy.toStringAsFixed(1)}m';
  }
  
  /// Formate une position pour l'API
  Map<String, dynamic> positionToApiFormat(MockPosition position) {
    return {
      'latitude': position.latitude,
      'longitude': position.longitude,
      'accuracy': position.accuracy,
      'altitude': position.altitude,
      'speed': position.speed,
      'heading': position.heading,
      'timestamp': position.timestamp.toIso8601String(),
    };
  }
}

/// Position simulée pour remplacer la position Geolocator
class MockPosition {
  final double latitude;
  final double longitude;
  final double accuracy;
  final double altitude;
  final double speed;
  final double heading;
  final DateTime timestamp;
  
  MockPosition({
    required this.latitude,
    required this.longitude,
    required this.accuracy,
    required this.altitude,
    required this.speed,
    required this.heading,
    required this.timestamp,
  });
}

/// Permissions simulées pour remplacer LocationPermission
enum MockLocationPermission {
  denied,
  deniedForever,
  whileInUse,
  always,
}

/// Exception personnalisée pour les erreurs de localisation
class LocationException implements Exception {
  final String message;
  LocationException(this.message);
  
  @override
  String toString() => 'LocationException: $message';
}

/// Classe pour représenter une position avec des informations supplémentaires
class LocationInfo {
  final MockPosition position;
  final String? address;
  final bool isValid;
  final String? errorMessage;
  
  LocationInfo({
    required this.position,
    this.address,
    required this.isValid,
    this.errorMessage,
  });
  
  factory LocationInfo.fromPosition(MockPosition position, {String? address}) {
    bool isValid = LocationService.instance.isValidPosition(position);
    return LocationInfo(
      position: position,
      address: address,
      isValid: isValid,
      errorMessage: isValid ? null : 'Position non valide',
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'position': LocationService.instance.positionToApiFormat(position),
      'address': address,
      'isValid': isValid,
      'errorMessage': errorMessage,
    };
  }
}