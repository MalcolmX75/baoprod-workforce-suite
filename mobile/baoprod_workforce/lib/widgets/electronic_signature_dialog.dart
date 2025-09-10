import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/document_request.dart';
import '../providers/document_provider.dart';

class ElectronicSignatureDialog extends StatefulWidget {
  final DocumentRequest documentRequest;

  const ElectronicSignatureDialog({
    super.key,
    required this.documentRequest,
  });

  @override
  State<ElectronicSignatureDialog> createState() => _ElectronicSignatureDialogState();
}

class _ElectronicSignatureDialogState extends State<ElectronicSignatureDialog> {
  final _signatureController = TextEditingController();
  bool _isSigningAgreement = false;
  bool _isSubmitting = false;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Container(
        padding: const EdgeInsets.all(24),
        constraints: const BoxConstraints(maxHeight: 500),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // En-tête
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.draw,
                    color: Colors.green,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Signature électronique',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        widget.documentRequest.title,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () => Navigator.of(context).pop(),
                  icon: const Icon(Icons.close),
                ),
              ],
            ),
            const SizedBox(height: 24),

            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Information du document
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.blue.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.blue.withOpacity(0.2)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.info, color: Colors.blue, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                'Informations du document',
                                style: TextStyle(
                                  color: Colors.blue.shade700,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          _buildInfoRow('Type', widget.documentRequest.typeText),
                          _buildInfoRow('Statut', widget.documentRequest.statusText),
                          _buildInfoRow('Demandé le', widget.documentRequest.formattedRequestDate),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Instructions de signature
                    Text(
                      'Instructions de signature',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.amber.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.amber.withOpacity(0.3)),
                      ),
                      child: Column(
                        children: [
                          _buildInstructionStep(
                            '1.',
                            'Saisissez votre nom complet tel qu\'il apparaît sur vos documents officiels',
                          ),
                          const SizedBox(height: 8),
                          _buildInstructionStep(
                            '2.',
                            'Vérifiez que toutes les informations du document sont correctes',
                          ),
                          const SizedBox(height: 8),
                          _buildInstructionStep(
                            '3.',
                            'Cochez la case de consentement pour valider votre signature',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Champ de signature
                    Text(
                      'Votre signature électronique',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _signatureController,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        hintText: 'Tapez votre nom complet',
                        prefixIcon: const Icon(Icons.edit),
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'La signature est requise';
                        }
                        if (value.trim().length < 2) {
                          return 'Signature trop courte';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Case de consentement
                    CheckboxListTile(
                      title: const Text('Consentement à la signature électronique'),
                      subtitle: const Text(
                        'Je confirme que cette signature électronique a la même valeur juridique qu\'une signature manuscrite et que j\'accepte les termes de ce document.',
                        style: TextStyle(fontSize: 12),
                      ),
                      value: _isSigningAgreement,
                      onChanged: (value) {
                        setState(() {
                          _isSigningAgreement = value ?? false;
                        });
                      },
                      contentPadding: EdgeInsets.zero,
                      controlAffinity: ListTileControlAffinity.leading,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Actions
            Row(
              children: [
                TextButton(
                  onPressed: _isSubmitting ? null : () => Navigator.of(context).pop(),
                  child: const Text('Annuler'),
                ),
                const Spacer(),
                ElevatedButton.icon(
                  onPressed: (_canSign() && !_isSubmitting) ? _submitSignature : null,
                  icon: _isSubmitting
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.check, size: 18),
                  label: const Text('Signer le document'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }

  Widget _buildInstructionStep(String step, String instruction) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 20,
          height: 20,
          decoration: BoxDecoration(
            color: Colors.amber,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Center(
            child: Text(
              step,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            instruction,
            style: const TextStyle(fontSize: 13),
          ),
        ),
      ],
    );
  }

  bool _canSign() {
    return _signatureController.text.trim().isNotEmpty && 
           _isSigningAgreement;
  }

  Future<void> _submitSignature() async {
    if (!_canSign()) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      final documentProvider = context.read<DocumentProvider>();
      
      final success = await documentProvider.signDocumentElectronically(
        widget.documentRequest.id,
        _signatureController.text.trim(),
      );

      if (success && mounted) {
        Navigator.of(context).pop(true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white, size: 20),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text('Document signé avec succès !'),
                ),
              ],
            ),
            backgroundColor: Colors.green,
          ),
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Erreur lors de la signature du document'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _signatureController.dispose();
    super.dispose();
  }
}