import 'package:flutter/material.dart';
import '../utils/constants.dart';

class ContratsScreen extends StatelessWidget {
  const ContratsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Contrats'),
      ),
      body: const Center(
        child: Text('Écran des contrats - À implémenter'),
      ),
    );
  }
}