import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
// import 'package:hive_flutter/hive_flutter.dart'; // Removed due to path_provider dependency
// import 'package:firebase_core/firebase_core.dart';

import 'providers/auth_provider.dart';
import 'providers/timesheet_provider.dart';
import 'providers/job_provider.dart';
import 'providers/theme_provider.dart';
import 'providers/cv_provider.dart';
import 'providers/onboarding_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/document_provider.dart';
import 'providers/chat_provider.dart';
import 'providers/language_provider.dart';
import 'services/api_service.dart';
import 'services/storage_service.dart';
import 'services/location_service.dart';
import 'services/notification_service.dart';
import 'utils/app_router.dart';
import 'utils/app_theme.dart';
import 'utils/constants.dart';
import 'utils/app_localizations.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase (temporarily disabled)
  // await Firebase.initializeApp();
  
  // Initialize storage service
  await StorageService.init();
  
  // Initialize API service
  await ApiService.init();
  
  // Initialize location service
  await LocationService.instance.ensureLocationPermission();
  
  // Initialize notification service
  await NotificationService.instance.init();
  
  // Initialize language provider
  final languageProvider = LanguageProvider();
  await languageProvider.init();
  
  runApp(BaoProdWorkforceApp(languageProvider: languageProvider));
}

class BaoProdWorkforceApp extends StatelessWidget {
  const BaoProdWorkforceApp({super.key, required this.languageProvider});
  
  final LanguageProvider languageProvider;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => TimesheetProvider()),
        ChangeNotifierProvider(create: (_) => JobProvider()),
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => CVProvider()),
        ChangeNotifierProvider(create: (_) => OnboardingProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
        ChangeNotifierProvider(create: (_) => DocumentProvider()),
        ChangeNotifierProvider(create: (_) => ChatProvider()),
        ChangeNotifierProvider.value(value: languageProvider),
      ],
      child: Consumer3<AuthProvider, ThemeProvider, LanguageProvider>(
        builder: (context, authProvider, themeProvider, languageProvider, _) {
          return MaterialApp.router(
            title: AppConstants.appName,
            debugShowCheckedModeBanner: false,
            theme: AppTheme.lightTheme,
            darkTheme: AppTheme.darkTheme,
            themeMode: themeProvider.themeMode,
            locale: languageProvider.currentLocale,
            supportedLocales: AppLocalizations.supportedLocales,
            localizationsDelegates: const [
              AppLocalizationsDelegate(),
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            routerConfig: AppRouter.router,
          );
        },
      ),
    );
  }
}