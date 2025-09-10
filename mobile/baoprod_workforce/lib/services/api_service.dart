import 'package:dio/dio.dart';
// import 'package:shared_preferences/shared_preferences.dart'; // Temporarily removed
import '../utils/constants.dart';
import 'storage_service.dart';

class ApiService {
  static late Dio _dio;
  static String? _authToken;
  static String? _tenantId;

  static Future<void> init() async {
    _dio = Dio(BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: AppConstants.apiTimeout,
      receiveTimeout: AppConstants.apiTimeout,
      sendTimeout: AppConstants.apiTimeout,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));
    
    // Add interceptors
    _dio.interceptors.add(_AuthInterceptor());
    _dio.interceptors.add(_LoggingInterceptor());
    
    // Load saved token and tenant
    await _loadToken();
    await _loadTenant();
  }
  
  static Future<void> _loadToken() async {
    _authToken = StorageService.get<String>(AppConstants.tokenKey);
    if (_authToken != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_authToken';
    }
  }

  static Future<void> _loadTenant() async {
    _tenantId = StorageService.get<String>('tenant_id');
    if (_tenantId != null) {
      _dio.options.headers['X-Tenant-ID'] = _tenantId;
    }
  }
  
  static Future<void> setAuthToken(String token) async {
    _authToken = token;
    _dio.options.headers['Authorization'] = 'Bearer $token';
    
    await StorageService.save(AppConstants.tokenKey, token);
  }

  static Future<void> setTenant(String tenantId) async {
    _tenantId = tenantId;
    _dio.options.headers['X-Tenant-ID'] = tenantId;

    await StorageService.save('tenant_id', tenantId);
  }
  
  static Future<void> clearAuthToken() async {
    _authToken = null;
    _dio.options.headers.remove('Authorization');
    
    await StorageService.remove(AppConstants.tokenKey);
  }

  static Future<void> clearTenant() async {
    _tenantId = null;
    _dio.options.headers.remove('X-Tenant-ID');

    await StorageService.remove('tenant_id');
  }
  
  static String? get authToken => _authToken;
  static String? get tenantId => _tenantId;
  
  // Auth endpoints
  static Future<Response> login(String email, String password) async {
    return await _dio.post('/${AppConstants.apiVersion}/auth/login', data: {
      'email': email,
      'password': password,
    });
  }
  
  static Future<Response> register(Map<String, dynamic> userData) async {
    return await _dio.post('/${AppConstants.apiVersion}/auth/register', data: userData);
  }
  
  static Future<Response> logout() async {
    return await _dio.post('/${AppConstants.apiVersion}/auth/logout');
  }
  
  static Future<Response> getProfile() async {
    return await _dio.get('/${AppConstants.apiVersion}/auth/me');
  }
  
  static Future<Response> refreshToken() async {
    return await _dio.post('/${AppConstants.apiVersion}/auth/refresh');
  }
  
  // Jobs endpoints
  static Future<Response> getJobs({int page = 1, int limit = 10}) async {
    return await _dio.get('/${AppConstants.apiVersion}/jobs', queryParameters: {
      'page': page,
      'limit': limit,
    });
  }
  
  static Future<Response> getJob(int jobId) async {
    return await _dio.get('/${AppConstants.apiVersion}/jobs/$jobId');
  }
  
  // Applications endpoints
  static Future<Response> getApplications({int page = 1, int limit = 10}) async {
    return await _dio.get('/${AppConstants.apiVersion}/applications', queryParameters: {
      'page': page,
      'limit': limit,
    });
  }
  
  static Future<Response> createApplication(Map<String, dynamic> applicationData) async {
    return await _dio.post('/${AppConstants.apiVersion}/applications', data: applicationData);
  }
  
  // Timesheets endpoints
  static Future<Response> getTimesheets({int page = 1, int limit = 10}) async {
    return await _dio.get('/${AppConstants.apiVersion}/timesheets', queryParameters: {
      'page': page,
      'limit': limit,
    });
  }
  
  static Future<Response> createTimesheet(Map<String, dynamic> timesheetData) async {
    return await _dio.post('/${AppConstants.apiVersion}/timesheets', data: timesheetData);
  }
  
  static Future<Response> clockIn(Map<String, dynamic> clockInData) async {
    return await _dio.post('/${AppConstants.apiVersion}/timesheets/clock-in', data: clockInData);
  }
  
  static Future<Response> clockOut(int timesheetId, Map<String, dynamic> clockOutData) async {
    return await _dio.post('/${AppConstants.apiVersion}/timesheets/$timesheetId/clock-out', data: clockOutData);
  }
  
  // Contrats endpoints
  static Future<Response> getContrats({int page = 1, int limit = 10}) async {
    return await _dio.get('/${AppConstants.apiVersion}/contrats', queryParameters: {
      'page': page,
      'limit': limit,
    });
  }
  
  static Future<Response> getContrat(int contratId) async {
    return await _dio.get('/${AppConstants.apiVersion}/contrats/$contratId');
  }
  
  // Paie endpoints
  static Future<Response> getPaie({int page = 1, int limit = 10}) async {
    return await _dio.get('/${AppConstants.apiVersion}/paie', queryParameters: {
      'page': page,
      'limit': limit,
    });
  }
  
  static Future<Response> getPaieDetails(int paieId) async {
    return await _dio.get('/${AppConstants.apiVersion}/paie/$paieId');
  }
  
  // Health check
  static Future<Response> healthCheck() async {
    return await _dio.get('/health');
  }
}

class _AuthInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    if (ApiService._authToken != null) {
      options.headers['Authorization'] = 'Bearer ${ApiService._authToken}';
    }
    if (ApiService._tenantId != null) {
      options.headers['X-Tenant-ID'] = ApiService._tenantId;
    }
    handler.next(options);
  }
  
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Token expired or invalid
      await ApiService.clearAuthToken();
      await ApiService.clearTenant();
      // You might want to navigate to login screen here
    }
    handler.next(err);
  }
}

class _LoggingInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    print('🚀 REQUEST[${options.method}] => PATH: ${options.path}');
    print('Headers: ${options.headers}');
    if (options.data != null) {
      print('Data: ${options.data}');
    }
    handler.next(options);
  }
  
  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    print('✅ RESPONSE[${response.statusCode}] => PATH: ${response.requestOptions.path}');
    print('Data: ${response.data}');
    handler.next(response);
  }
  
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    print('❌ ERROR[${err.response?.statusCode}] => PATH: ${err.requestOptions.path}');
    print('Message: ${err.message}');
    if (err.response?.data != null) {
      print('Error Data: ${err.response?.data}');
    }
    handler.next(err);
  }
}
