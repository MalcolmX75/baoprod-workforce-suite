import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/document_request.dart';
import '../providers/document_provider.dart';

class DocumentRequestDialog extends StatefulWidget {
  const DocumentRequestDialog({super.key});

  @override
  State<DocumentRequestDialog> createState() => _DocumentRequestDialogState();
}

class _DocumentRequestDialogState extends State<DocumentRequestDialog> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  
  DocumentType _selectedType = DocumentType.contract;
  SignatureType _selectedSignatureType = SignatureType.none;
  bool _isUrgent = false;
  bool _isSubmitting = false;

  final Map<DocumentType, String> _typeDescriptions = {
    DocumentType.contract: 'Copie de votre contrat de travail actuel',
    DocumentType.payslip: 'Fiche de paie pour une période donnée',
    DocumentType.certificate: 'Attestation de travail ou certificat d\'emploi',
    DocumentType.recommendation: 'Lettre de recommandation de votre employeur',
    DocumentType.other: 'Autre type de document professionnel',
  };

  final Map<SignatureType, String> _signatureDescriptions = {
    SignatureType.none: 'Aucune signature requise',
    SignatureType.electronic: 'Signature électronique dans l\'application',
    SignatureType.physical: 'Signature manuscrite (scan requis)',
  };

  @override
  void initState() {
    super.initState();
    _updateTitleFromType();
  }

  void _updateTitleFromType() {
    switch (_selectedType) {
      case DocumentType.contract:
        _titleController.text = 'Copie contrat de travail';
        break;
      case DocumentType.payslip:
        _titleController.text = 'Fiche de paie';
        break;
      case DocumentType.certificate:
        _titleController.text = 'Attestation de travail';
        break;
      case DocumentType.recommendation:
        _titleController.text = 'Lettre de recommandation';
        break;
      case DocumentType.other:
        _titleController.text = '';
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Container(
        padding: const EdgeInsets.all(24),
        constraints: const BoxConstraints(maxHeight: 600),
        child: Form(
          key: _formKey,
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
                      color: Theme.of(context).primaryColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      Icons.request_page,
                      color: Theme.of(context).primaryColor,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Nouvelle demande de document',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
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
                      // Type de document
                      Text(
                        'Type de document',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<DocumentType>(
                        value: _selectedType,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        items: DocumentType.values.map((type) {
                          return DropdownMenuItem(
                            value: type,
                            child: Text(_getTypeText(type)),
                          );
                        }).toList(),
                        onChanged: (value) {
                          if (value != null) {
                            setState(() {
                              _selectedType = value;
                              _updateTitleFromType();
                            });
                          }
                        },
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _typeDescriptions[_selectedType] ?? '',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Titre
                      Text(
                        'Titre de la demande',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _titleController,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          hintText: 'Ex: Copie contrat de travail',
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Le titre est requis';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Description
                      Text(
                        'Description (optionnel)',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _descriptionController,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          hintText: 'Précisions sur votre demande...',
                        ),
                        maxLines: 3,
                      ),
                      const SizedBox(height: 16),

                      // Type de signature
                      Text(
                        'Signature requise',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      ...SignatureType.values.map((type) {
                        return RadioListTile<SignatureType>(
                          title: Text(_getSignatureTypeText(type)),
                          subtitle: Text(
                            _signatureDescriptions[type] ?? '',
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 12,
                            ),
                          ),
                          value: type,
                          groupValue: _selectedSignatureType,
                          onChanged: (value) {
                            if (value != null) {
                              setState(() {
                                _selectedSignatureType = value;
                              });
                            }
                          },
                          contentPadding: EdgeInsets.zero,
                        );
                      }),
                      const SizedBox(height: 16),

                      // Urgent
                      CheckboxListTile(
                        title: const Text('Demande urgente'),
                        subtitle: const Text(
                          'Traitement prioritaire (délai raccourci)',
                          style: TextStyle(fontSize: 12),
                        ),
                        value: _isUrgent,
                        onChanged: (value) {
                          setState(() {
                            _isUrgent = value ?? false;
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
                  ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitRequest,
                    style: ElevatedButton.styleFrom(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(
                            height: 16,
                            width: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Envoyer la demande'),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _getTypeText(DocumentType type) {
    switch (type) {
      case DocumentType.contract:
        return 'Contrat de travail';
      case DocumentType.payslip:
        return 'Fiche de paie';
      case DocumentType.certificate:
        return 'Attestation de travail';
      case DocumentType.recommendation:
        return 'Lettre de recommandation';
      case DocumentType.other:
        return 'Autre document';
    }
  }

  String _getSignatureTypeText(SignatureType type) {
    switch (type) {
      case SignatureType.none:
        return 'Aucune signature';
      case SignatureType.electronic:
        return 'Signature électronique';
      case SignatureType.physical:
        return 'Signature physique';
    }
  }

  Future<void> _submitRequest() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final documentProvider = context.read<DocumentProvider>();
      
      final success = await documentProvider.createDocumentRequest(
        type: _selectedType,
        title: _titleController.text.trim(),
        description: _descriptionController.text.trim(),
        signatureType: _selectedSignatureType,
        isUrgent: _isUrgent,
        metadata: {
          'created_from': 'mobile_app',
          'user_device': 'mobile',
        },
      );

      if (success && mounted) {
        Navigator.of(context).pop(true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white, size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text('Demande envoyée : ${_titleController.text}'),
                ),
              ],
            ),
            backgroundColor: Colors.green,
          ),
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Erreur lors de l\'envoi de la demande'),
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
    _titleController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }
}