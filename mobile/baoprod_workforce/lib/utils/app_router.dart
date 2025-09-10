import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/onboarding_provider.dart';
import '../screens/splash_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/dashboard_screen.dart';
import '../screens/timesheet_list_screen.dart';
import '../screens/clock_in_out_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/settings_screen.dart';
import '../screens/job_search_screen.dart';
import '../screens/contrats_screen.dart';
import '../screens/onboarding_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/chat_screen.dart';
import '../screens/documents_screen.dart';

class AppRouter {
  static final GoRouter router = GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      final authProvider = context.read<AuthProvider>();
      final onboardingProvider = context.read<OnboardingProvider>();
      
      final isAuthenticated = authProvider.isAuthenticated;
      final isOnAuthPage = state.uri.path.startsWith('/auth');
      final isOnOnboarding = state.uri.path == '/onboarding';
      final isOnSplash = state.uri.path == '/splash';
      final shouldShowOnboarding = onboardingProvider.shouldShowOnboarding;
      
      // If not authenticated and not on auth page, redirect to login
      if (!isAuthenticated && !isOnAuthPage && !isOnSplash) {
        return '/auth/login';
      }
      
      // If authenticated and should show onboarding, redirect to onboarding
      if (isAuthenticated && shouldShowOnboarding && !isOnOnboarding && !isOnSplash) {
        return '/onboarding';
      }
      
      // If authenticated and on auth page, redirect to onboarding (if needed) or dashboard
      if (isAuthenticated && isOnAuthPage) {
        return shouldShowOnboarding ? '/onboarding' : '/dashboard';
      }
      
      return null;
    },
    routes: [
      // Splash Screen
      GoRoute(
        path: '/splash',
        name: 'splash',
        builder: (context, state) => const SplashScreen(),
      ),
      
      // Auth Routes
      GoRoute(
        path: '/auth/login',
        name: 'login',
        builder: (context, state) => const LoginScreen(),
      ),

      
      // Main App Routes
      GoRoute(
        path: '/dashboard',
        name: 'dashboard',
        builder: (context, state) => DashboardScreen(),
      ),
      GoRoute(
        path: '/timesheets',
        name: 'timesheets',
        builder: (context, state) => const TimesheetListScreen(),
      ),
      GoRoute(
        path: '/clock-in-out',
        name: 'clock-in-out',
        builder: (context, state) => const ClockInOutScreen(),
      ),
      GoRoute(
        path: '/profile',
        name: 'profile',
        builder: (context, state) => const ProfileScreen(),
      ),
      GoRoute(
        path: '/settings',
        name: 'settings',
        builder: (context, state) => const SettingsScreen(),
      ),
      GoRoute(
        path: '/job-search',
        name: 'job-search',
        builder: (context, state) => const JobSearchScreen(),
      ),
      GoRoute(
        path: '/contrats',
        name: 'contrats',
        builder: (context, state) => const ContratsScreen(),
      ),
      GoRoute(
        path: '/onboarding',
        name: 'onboarding',
        builder: (context, state) => const OnboardingScreen(),
      ),
      GoRoute(
        path: '/notifications',
        name: 'notifications',
        builder: (context, state) => const NotificationsScreen(),
      ),
      GoRoute(
        path: '/chat',
        name: 'chat',
        builder: (context, state) => const ChatScreen(),
      ),
      GoRoute(
        path: '/documents',
        name: 'documents',
        builder: (context, state) => const DocumentsScreen(),
      ),
    ],
    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 64,
              color: Colors.red,
            ),
            const SizedBox(height: 16),
            Text(
              'Page non trouvée',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 8),
            Text(
              'La page "${state.uri.path}" n\'existe pas.',
              style: Theme.of(context).textTheme.bodyMedium,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () => context.go('/dashboard'),
              child: const Text('Retour à l\'accueil'),
            ),
          ],
        ),
      ),
    ),
  );
}