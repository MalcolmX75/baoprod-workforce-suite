import 'package:flutter/material.dart';
import '../utils/constants.dart';

enum ButtonType {
  primary,
  secondary,
  outlined,
  text,
  danger,
  success,
}

enum ButtonSize {
  small,
  medium,
  large,
}

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final ButtonType type;
  final ButtonSize size;
  final bool isLoading;
  final bool isFullWidth;
  final IconData? icon;
  final Color? backgroundColor;
  final Color? textColor;
  final double? width;
  final double? height;
  final EdgeInsetsGeometry? padding;
  final BorderRadius? borderRadius;

  const CustomButton({
    super.key,
    required this.text,
    this.onPressed,
    this.type = ButtonType.primary,
    this.size = ButtonSize.medium,
    this.isLoading = false,
    this.isFullWidth = true,
    this.icon,
    this.backgroundColor,
    this.textColor,
    this.width,
    this.height,
    this.padding,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return SizedBox(
      width: isFullWidth ? double.infinity : width,
      height: height ?? _getButtonHeight(),
      child: _buildButton(theme),
    );
  }

  Widget _buildButton(ThemeData theme) {
    switch (type) {
      case ButtonType.primary:
        return _buildElevatedButton(theme);
      case ButtonType.secondary:
        return _buildElevatedButton(theme, isSecondary: true);
      case ButtonType.outlined:
        return _buildOutlinedButton(theme);
      case ButtonType.text:
        return _buildTextButton(theme);
      case ButtonType.danger:
        return _buildElevatedButton(theme, isDanger: true);
      case ButtonType.success:
        return _buildElevatedButton(theme, isSuccess: true);
    }
  }

  Widget _buildElevatedButton(ThemeData theme, {bool isSecondary = false, bool isDanger = false, bool isSuccess = false}) {
    Color bgColor = backgroundColor ?? _getBackgroundColor(isSecondary, isDanger, isSuccess);
    Color fgColor = textColor ?? _getTextColor(isSecondary, isDanger, isSuccess);

    return ElevatedButton(
      onPressed: isLoading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: bgColor,
        foregroundColor: fgColor,
        elevation: 2,
        padding: padding ?? _getPadding(),
        shape: RoundedRectangleBorder(
          borderRadius: borderRadius ?? BorderRadius.circular(AppConstants.borderRadius),
        ),
        textStyle: _getTextStyle(theme),
      ),
      child: _buildButtonContent(fgColor),
    );
  }

  Widget _buildOutlinedButton(ThemeData theme) {
    Color borderColor = backgroundColor ?? AppTheme.primaryColor;
    Color fgColor = textColor ?? borderColor;

    return OutlinedButton(
      onPressed: isLoading ? null : onPressed,
      style: OutlinedButton.styleFrom(
        foregroundColor: fgColor,
        side: BorderSide(color: borderColor),
        padding: padding ?? _getPadding(),
        shape: RoundedRectangleBorder(
          borderRadius: borderRadius ?? BorderRadius.circular(AppConstants.borderRadius),
        ),
        textStyle: _getTextStyle(theme),
      ),
      child: _buildButtonContent(fgColor),
    );
  }

  Widget _buildTextButton(ThemeData theme) {
    Color fgColor = textColor ?? AppTheme.primaryColor;

    return TextButton(
      onPressed: isLoading ? null : onPressed,
      style: TextButton.styleFrom(
        foregroundColor: fgColor,
        padding: padding ?? _getPadding(),
        shape: RoundedRectangleBorder(
          borderRadius: borderRadius ?? BorderRadius.circular(AppConstants.borderRadius),
        ),
        textStyle: _getTextStyle(theme),
      ),
      child: _buildButtonContent(fgColor),
    );
  }

  Widget _buildButtonContent(Color textColor) {
    if (isLoading) {
      return SizedBox(
        width: 20,
        height: 20,
        child: CircularProgressIndicator(
          strokeWidth: 2,
          valueColor: AlwaysStoppedAnimation<Color>(textColor),
        ),
      );
    }

    if (icon != null) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: _getIconSize()),
          const SizedBox(width: 8),
          Text(text),
        ],
      );
    }

    return Text(text);
  }

  double _getButtonHeight() {
    switch (size) {
      case ButtonSize.small:
        return 36;
      case ButtonSize.medium:
        return AppConstants.buttonHeight;
      case ButtonSize.large:
        return 56;
    }
  }

  EdgeInsetsGeometry _getPadding() {
    switch (size) {
      case ButtonSize.small:
        return const EdgeInsets.symmetric(horizontal: 16, vertical: 8);
      case ButtonSize.medium:
        return const EdgeInsets.symmetric(horizontal: 24, vertical: 12);
      case ButtonSize.large:
        return const EdgeInsets.symmetric(horizontal: 32, vertical: 16);
    }
  }

  TextStyle _getTextStyle(ThemeData theme) {
    switch (size) {
      case ButtonSize.small:
        return theme.textTheme.labelMedium?.copyWith(
          fontWeight: FontWeight.w600,
        ) ?? const TextStyle(fontSize: 12, fontWeight: FontWeight.w600);
      case ButtonSize.medium:
        return theme.textTheme.labelLarge?.copyWith(
          fontWeight: FontWeight.w600,
        ) ?? const TextStyle(fontSize: 14, fontWeight: FontWeight.w600);
      case ButtonSize.large:
        return theme.textTheme.titleMedium?.copyWith(
          fontWeight: FontWeight.w600,
        ) ?? const TextStyle(fontSize: 16, fontWeight: FontWeight.w600);
    }
  }

  double _getIconSize() {
    switch (size) {
      case ButtonSize.small:
        return 16;
      case ButtonSize.medium:
        return 20;
      case ButtonSize.large:
        return 24;
    }
  }

  Color _getBackgroundColor(bool isSecondary, bool isDanger, bool isSuccess) {
    if (isDanger) return AppTheme.errorColor;
    if (isSuccess) return AppTheme.successColor;
    if (isSecondary) return AppTheme.secondaryColor;
    return AppTheme.primaryColor;
  }

  Color _getTextColor(bool isSecondary, bool isDanger, bool isSuccess) {
    if (isDanger || isSuccess || !isSecondary) return Colors.white;
    return Colors.white;
  }
}

/// Bouton avec icône uniquement
class IconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final Color? color;
  final Color? backgroundColor;
  final double size;
  final String? tooltip;

  const IconButton({
    super.key,
    required this.icon,
    this.onPressed,
    this.color,
    this.backgroundColor,
    this.size = 24,
    this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    Widget button = Material(
      color: backgroundColor ?? Colors.transparent,
      borderRadius: BorderRadius.circular(AppConstants.borderRadius),
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(AppConstants.borderRadius),
        child: Container(
          width: size + 16,
          height: size + 16,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppConstants.borderRadius),
          ),
          child: Icon(
            icon,
            size: size,
            color: color ?? AppTheme.primaryColor,
          ),
        ),
      ),
    );

    if (tooltip != null) {
      return Tooltip(
        message: tooltip!,
        child: button,
      );
    }

    return button;
  }
}

/// Bouton flottant personnalisé
class CustomFloatingActionButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final String? tooltip;
  final Color? backgroundColor;
  final Color? foregroundColor;
  final double size;

  const CustomFloatingActionButton({
    super.key,
    required this.icon,
    this.onPressed,
    this.tooltip,
    this.backgroundColor,
    this.foregroundColor,
    this.size = 56,
  });

  @override
  Widget build(BuildContext context) {
    return FloatingActionButton(
      onPressed: onPressed,
      tooltip: tooltip,
      backgroundColor: backgroundColor ?? AppTheme.primaryColor,
      foregroundColor: foregroundColor ?? Colors.white,
      child: Icon(icon, size: size * 0.6),
    );
  }
}

/// Bouton de chargement
class LoadingButton extends StatelessWidget {
  final String text;
  final bool isLoading;
  final VoidCallback? onPressed;
  final ButtonType type;
  final ButtonSize size;

  const LoadingButton({
    super.key,
    required this.text,
    required this.isLoading,
    this.onPressed,
    this.type = ButtonType.primary,
    this.size = ButtonSize.medium,
  });

  @override
  Widget build(BuildContext context) {
    return CustomButton(
      text: text,
      onPressed: onPressed,
      type: type,
      size: size,
      isLoading: isLoading,
    );
  }
}