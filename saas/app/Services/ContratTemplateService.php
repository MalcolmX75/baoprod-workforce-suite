<?php

namespace BaoProd\Workforce\Services;

use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Tenant;

class ContratTemplateService
{
    /**
     * Générer le contenu HTML du contrat
     */
    public function generateContratHtml(Contrat $contrat): string
    {
        $template = $this->getTemplateByCountry($contrat->pays_code);
        
        $data = $this->prepareContratData($contrat);
        
        return $this->renderTemplate($template, $data);
    }

    /**
     * Générer le contenu PDF du contrat
     */
    public function generateContratPdf(Contrat $contrat): string
    {
        $html = $this->generateContratHtml($contrat);
        
        // Ici on pourrait utiliser DomPDF ou une autre librairie
        // Pour l'instant, on retourne le HTML
        return $html;
    }

    /**
     * Obtenir le template selon le pays
     */
    private function getTemplateByCountry(string $countryCode): string
    {
        $templates = [
            'GA' => $this->getGabonTemplate(),
            'CM' => $this->getCamerounTemplate(),
            'TD' => $this->getTchadTemplate(),
            'CF' => $this->getRCATemplate(),
            'GQ' => $this->getGuineeEquatorialeTemplate(),
            'CG' => $this->getCongoTemplate(),
        ];

        return $templates[$countryCode] ?? $templates['GA'];
    }

    /**
     * Préparer les données du contrat pour le template
     */
    private function prepareContratData(Contrat $contrat): array
    {
        $tenant = $contrat->tenant;
        $user = $contrat->user;
        $job = $contrat->job;
        $createdBy = $contrat->createdBy;

        return [
            'contrat' => $contrat,
            'tenant' => $tenant,
            'employe' => $user,
            'job' => $job,
            'created_by' => $createdBy,
            'config_pays' => $contrat->configuration_pays,
            'date_generation' => now()->format('d/m/Y'),
            'signatures' => $contrat->signatures,
        ];
    }

    /**
     * Rendre le template avec les données
     */
    private function renderTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        // Remplacer les données spécifiques
        $template = $this->replaceContratData($template, $data['contrat']);
        $template = $this->replaceEmployeData($template, $data['employe']);
        $template = $this->replaceTenantData($template, $data['tenant']);

        return $template;
    }

    /**
     * Remplacer les données du contrat
     */
    private function replaceContratData(string $template, Contrat $contrat): string
    {
        $replacements = [
            '{{numero_contrat}}' => $contrat->numero_contrat,
            '{{type_contrat}}' => $contrat->type_contrat,
            '{{date_debut}}' => $contrat->date_debut->format('d/m/Y'),
            '{{date_fin}}' => $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'Non définie',
            '{{salaire_brut}}' => number_format($contrat->salaire_brut, 0, ',', ' ') . ' ' . $contrat->devise,
            '{{salaire_net}}' => number_format($contrat->salaire_net, 0, ',', ' ') . ' ' . $contrat->devise,
            '{{heures_semaine}}' => $contrat->heures_semaine,
            '{{heures_mois}}' => $contrat->heures_mois,
            '{{taux_horaire}}' => number_format($contrat->taux_horaire, 0, ',', ' ') . ' ' . $contrat->devise,
            '{{charges_sociales_pourcentage}}' => $contrat->charges_sociales_pourcentage . '%',
            '{{periode_essai_jours}}' => $contrat->periode_essai_jours,
            '{{periode_essai_fin}}' => $contrat->periode_essai_fin ? $contrat->periode_essai_fin->format('d/m/Y') : 'Non définie',
            '{{description}}' => $contrat->description ?? '',
            '{{conditions_particulieres}}' => $contrat->conditions_particulieres ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Remplacer les données de l'employé
     */
    private function replaceEmployeData(string $template, User $user): string
    {
        $replacements = [
            '{{employe_nom}}' => $user->full_name,
            '{{employe_prenom}}' => $user->first_name,
            '{{employe_nom_famille}}' => $user->last_name,
            '{{employe_email}}' => $user->email,
            '{{employe_telephone}}' => $user->phone ?? 'Non renseigné',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Remplacer les données du tenant
     */
    private function replaceTenantData(string $template, Tenant $tenant): string
    {
        $replacements = [
            '{{entreprise_nom}}' => $tenant->name,
            '{{entreprise_domaine}}' => $tenant->domain,
            '{{pays_code}}' => $tenant->country_code,
            '{{devise}}' => $tenant->currency,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Template pour le Gabon
     */
    private function getGabonTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République Gabonaise</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail gabonais et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }

    /**
     * Template pour le Cameroun
     */
    private function getCamerounTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République du Cameroun</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail camerounais et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }

    /**
     * Template pour le Tchad
     */
    private function getTchadTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République du Tchad</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail tchadien et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }

    /**
     * Template pour la RCA
     */
    private function getRCATemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République Centrafricaine</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail centrafricain et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }

    /**
     * Template pour la Guinée Équatoriale
     */
    private function getGuineeEquatorialeTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République de Guinée Équatoriale</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail équato-guinéen et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }

    /**
     * Template pour le Congo
     */
    private function getCongoTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Travail - {{numero_contrat}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; border: 1px solid #ccc; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>République du Congo</h2>
        <p><strong>Numéro de contrat :</strong> {{numero_contrat}}</p>
        <p><strong>Date de génération :</strong> {{date_generation}}</p>
    </div>

    <div class="section">
        <h3>1. PARTIES AU CONTRAT</h3>
        <p><strong>Employeur :</strong> {{entreprise_nom}}</p>
        <p><strong>Employé :</strong> {{employe_nom}}</p>
        <p><strong>Email employé :</strong> {{employe_email}}</p>
        <p><strong>Téléphone employé :</strong> {{employe_telephone}}</p>
    </div>

    <div class="section">
        <h3>2. NATURE DU CONTRAT</h3>
        <p><strong>Type de contrat :</strong> {{type_contrat}}</p>
        <p><strong>Date de début :</strong> {{date_debut}}</p>
        <p><strong>Date de fin :</strong> {{date_fin}}</p>
    </div>

    <div class="section">
        <h3>3. CONDITIONS DE TRAVAIL</h3>
        <p><strong>Description du poste :</strong> {{description}}</p>
        <p><strong>Heures par semaine :</strong> {{heures_semaine}} heures</p>
        <p><strong>Heures par mois :</strong> {{heures_mois}} heures</p>
    </div>

    <div class="section">
        <h3>4. RÉMUNÉRATION</h3>
        <p><strong>Salaire brut mensuel :</strong> {{salaire_brut}}</p>
        <p><strong>Salaire net mensuel :</strong> {{salaire_net}}</p>
        <p><strong>Taux horaire :</strong> {{taux_horaire}}</p>
        <p><strong>Charges sociales :</strong> {{charges_sociales_pourcentage}}</p>
    </div>

    <div class="section">
        <h3>5. PÉRIODE D\'ESSAI</h3>
        <p><strong>Durée :</strong> {{periode_essai_jours}} jours</p>
        <p><strong>Fin de période d\'essai :</strong> {{periode_essai_fin}}</p>
    </div>

    <div class="section">
        <h3>6. CONDITIONS PARTICULIÈRES</h3>
        <p>{{conditions_particulieres}}</p>
    </div>

    <div class="section">
        <h3>7. CONFORMITÉ LÉGALE</h3>
        <p>Ce contrat est établi conformément au Code du Travail congolais et aux conventions collectives applicables.</p>
        <p>Le salaire minimum garanti (SMIG) est respecté selon la législation en vigueur.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <h4>Signature de l\'Employé</h4>
            <p>Nom : {{employe_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
        <div class="signature-box">
            <h4>Signature de l\'Employeur</h4>
            <p>Entreprise : {{entreprise_nom}}</p>
            <p>Date : _________________</p>
            <p>Signature : _________________</p>
        </div>
    </div>

    <div class="section">
        <p><em>Contrat généré le {{date_generation}} par BaoProd Workforce Suite</em></p>
    </div>
</body>
</html>';
    }
}