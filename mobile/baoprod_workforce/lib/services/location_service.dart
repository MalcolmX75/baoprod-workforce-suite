import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';
import '../utils/constants.dart';

class LocationService {
  static LocationService? _instance;
  static LocationService get instance => _instance ??= LocationService._();
  
  LocationService._();
  
  Position? _currentPosition;
  Position? get currentPosition => _currentPosition;
  
  /// Vérifie si les services de localisation sont activés
  Future<bool> isLocationServiceEnabled() async {
    return await Geolocator.isLocationServiceEnabled();
  }
  
  /// Vérifie les permissions de localisation
  Future<LocationPermission> checkLocationPermission() async {
    return await Geolocator.checkPermission();
  }
  
  /// Demande les permissions de localisation
  Future<LocationPermission> requestLocationPermission() async {
    return await Geolocator.requestPermission();
  }
  
  /// Vérifie et demande les permissions si nécessaire
  Future<bool> ensureLocationPermission() async {
    // Vérifier si les services de localisation sont activés
    bool serviceEnabled = await isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw LocationException('Les services de localisation sont désactivés');
    }
    
    // Vérifier les permissions
    LocationPermission permission = await checkLocationPermission();
    
    if (permission == LocationPermission.denied) {
      permission = await requestLocationPermission();
      if (permission == LocationPermission.denied) {
        throw LocationException('Permission de localisation refusée');
      }
    }
    
    if (permission == LocationPermission.deniedForever) {
      throw LocationException(
        'Permission de localisation refusée définitivement. '
        'Veuillez l\'activer dans les paramètres de l\'application.'
      );
    }
    
    return true;
  }
  
  /// Obtient la position actuelle
  Future<Position> getCurrentPosition() async {
    try {
      // S'assurer que les permissions sont accordées
      await ensureLocationPermission();
      
      // Obtenir la position actuelle
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: AppConstants.locationTimeout,
      );
      
      _currentPosition = position;
      return position;
    } catch (e) {
      throw LocationException('Impossible d\'obtenir la position: $e');
    }
  }
  
  /// Obtient la dernière position connue
  Future<Position?> getLastKnownPosition() async {
    try {
      Position? position = await Geolocator.getLastKnownPosition();
      if (position != null) {
        _currentPosition = position;
      }
      return position;
    } catch (e) {
      print('Erreur lors de la récupération de la dernière position: $e');
      return null;
    }
  }
  
  /// Calcule la distance entre deux positions
  double calculateDistance(
    double lat1, 
    double lon1, 
    double lat2, 
    double lon2
  ) {
    return Geolocator.distanceBetween(lat1, lon1, lat2, lon2);
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
    try {
      // Note: Pour une implémentation complète, vous devriez utiliser
      // un service de géocodage inverse comme Google Maps Geocoding API
      // ou OpenStreetMap Nominatim
      
      // Pour l'instant, retourner une adresse générique
      return 'Lat: ${latitude.toStringAsFixed(6)}, Lng: ${longitude.toStringAsFixed(6)}';
    } catch (e) {
      print('Erreur lors de la récupération de l\'adresse: $e');
      return null;
    }
  }
  
  /// Valide la précision d'une position
  bool isValidPosition(Position position) {
    // Vérifier la précision (en mètres)
    if (position.accuracy > AppConstants.locationAccuracy) {
      return false;
    }
    
    // Vérifier que les coordonnées sont valides
    if (position.latitude == 0.0 && position.longitude == 0.0) {
      return false;
    }
    
    return true;
  }
  
  /// Obtient une position pour le pointage avec validation
  Future<Position> getPositionForClockIn() async {
    try {
      Position position = await getCurrentPosition();
      
      if (!isValidPosition(position)) {
        throw LocationException(
          'Position non valide. Précision: ${position.accuracy}m '
          '(minimum requis: ${AppConstants.locationAccuracy}m)'
        );
      }
      
      return position;
    } catch (e) {
      throw LocationException('Impossible d\'obtenir une position valide pour le pointage: $e');
    }
  }
  
  /// Ouvre les paramètres de localisation
  Future<void> openLocationSettings() async {
    await Geolocator.openLocationSettings();
  }
  
  /// Ouvre les paramètres de l'application
  Future<void> openAppSettings() async {
    await openAppSettings();
  }
  
  /// Formate une position pour l'affichage
  String formatPosition(Position position) {
    return 'Lat: ${position.latitude.toStringAsFixed(6)}, '
           'Lng: ${position.longitude.toStringAsFixed(6)}, '
           'Précision: ${position.accuracy.toStringAsFixed(1)}m';
  }
  
  /// Formate une position pour l'API
  Map<String, dynamic> positionToApiFormat(Position position) {
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

/// Exception personnalisée pour les erreurs de localisation
class LocationException implements Exception {
  final String message;
  LocationException(this.message);
  
  @override
  String toString() => 'LocationException: $message';
}

/// Classe pour représenter une position avec des informations supplémentaires
class LocationInfo {
  final Position position;
  final String? address;
  final bool isValid;
  final String? errorMessage;
  
  LocationInfo({
    required this.position,
    this.address,
    required this.isValid,
    this.errorMessage,
  });
  
  factory LocationInfo.fromPosition(Position position, {String? address}) {
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