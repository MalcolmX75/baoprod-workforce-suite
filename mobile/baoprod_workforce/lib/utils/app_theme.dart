import 'package:flutter/material.dart';

class AppTheme {
  // Modern purple color scheme
  static const Color primaryColor = Color(0xFF7E57C2); // Modern purple
  static const Color secondaryColor = Color(0xFF9C27B0); // Deep purple
  static const Color accentColor = Color(0xFFE1BEE7); // Light purple
  static const Color errorColor = Color(0xFFD32F2F);
  static const Color successColor = Color(0xFF4CAF50);
  static const Color warningColor = Color(0xFFFF9800);
  static const Color infoColor = Color(0xFF2196F3);

  // Styles for backward compatibility
  static const Color textSecondaryColor = Color(0xFF757575);
  static const TextStyle heading2 = TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF212121));
  static const TextStyle bodyText = TextStyle(fontSize: 16, fontWeight: FontWeight.normal, color: Color(0xFF212121));
  static const TextStyle subtitle = TextStyle(fontSize: 14, fontWeight: FontWeight.normal, color: Color(0xFF757575));

  static final ThemeData lightTheme = ThemeData.light().copyWith(
    primaryColor: primaryColor,
    colorScheme: const ColorScheme.light().copyWith(
      primary: primaryColor,
      secondary: secondaryColor,
      error: errorColor,
    ),
    scaffoldBackgroundColor: const Color(0xFFFAFAFA),
    appBarTheme: const AppBarTheme(
      backgroundColor: primaryColor,
      foregroundColor: Colors.white,
      elevation: 0,
    ),
    textTheme: _lightTextTheme,
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: Color(0xFFE0E0E0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: primaryColor, width: 2),
      ),
    ),
  );

  static final ThemeData darkTheme = ThemeData.dark().copyWith(
    primaryColor: primaryColor,
    colorScheme: const ColorScheme.dark().copyWith(
      primary: primaryColor,
      secondary: secondaryColor,
      error: errorColor,
    ),
    appBarTheme: const AppBarTheme(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
    textTheme: _darkTextTheme,
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: primaryColor, width: 2),
      ),
    ),
  );

  static final TextTheme _lightTextTheme = TextTheme(
    displayLarge: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Color(0xFF212121)),
    displayMedium: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF212121)),
    headlineMedium: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: Color(0xFF212121)),
    bodyLarge: const TextStyle(fontSize: 16, fontWeight: FontWeight.normal, color: Color(0xFF212121)),
    bodyMedium: const TextStyle(fontSize: 14, fontWeight: FontWeight.normal, color: Color(0xFF757575)),
    bodySmall: const TextStyle(fontSize: 12, fontWeight: FontWeight.normal, color: Color(0xFF757575)),
  );

  static final TextTheme _darkTextTheme = TextTheme(
    displayLarge: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white),
    displayMedium: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.white),
    headlineMedium: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: Colors.white),
    bodyLarge: const TextStyle(fontSize: 16, fontWeight: FontWeight.normal, color: Colors.white),
    bodyMedium: const TextStyle(fontSize: 14, fontWeight: FontWeight.normal, color: Colors.white70),
    bodySmall: const TextStyle(fontSize: 12, fontWeight: FontWeight.normal, color: Colors.white70),
  );
}
