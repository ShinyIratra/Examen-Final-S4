<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';
require_once __DIR__ . '/../assets/fpdf186/fpdf.php';

class Pret extends Objet {

    public static function getPretById($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, c.*, u.nom as nom_client, u.identifiant, t.taux 
            FROM ef_pret p
            JOIN ef_client c ON p.id_client = c.id_client
            JOIN ef_utilisateur u ON c.id_utilisateur = u.id_utilisateur
            JOIN ef_type_pret t ON p.id_type_pret = t.id_type_pret
            WHERE p.id_pret = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function generatePdf($pret) {
        // Create PDF instance with UTF-8 support
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Add document information
        $pdf->SetTitle('Contrat de Prêt - ' . $pret['nom_client']);
        $pdf->SetAuthor('FinanceHub');
        $pdf->SetCreator('Système de Gestion Bancaire');
        
        // Custom header with logo
        $pdf->SetFillColor(51, 122, 183); // Blue header
        $pdf->Rect(0, 0, $pdf->GetPageWidth(), 30, 'F');
        $pdf->SetTextColor(255, 255, 255); // White text
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(0, 25, iconv('UTF-8', 'ISO-8859-1', 'CONTRAT DE PRÊT'), 0, 1, 'C');
        
        // Reset colors
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);
        
        // Reference number and date - Use current date, not future date
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'Référence: PRET-' . $pret['id_pret']), 0, 1, 'R');
        $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', 'Date d\'émission: ' . date('d/m/Y')), 0, 1, 'R');
        $pdf->Ln(10);
        
        // Client information section with styled box
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'INFORMATIONS SUR LE CLIENT'), 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 10, iconv('UTF-8', 'ISO-8859-1', 'Nom complet:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $pret['nom_client']), 1, 1, 'L');
        $pdf->Cell(60, 10, iconv('UTF-8', 'ISO-8859-1', 'Identifiant client:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $pret['identifiant']), 1, 1, 'L');
        $pdf->Ln(10);
        
        // Loan details section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'DÉTAILS DU PRÊT'), 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 11);
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Montant emprunté:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($pret['montant'], 2, ',', ' ') . ' $'), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Taux d\'intérêt appliqué:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $pret['taux'] . ' %'), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Durée du prêt:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $pret['delai'] . ' mois'), 1, 1, 'L');
        
        // Properly format dates from Y-m-d format
        $date_debut = date('d/m/Y', strtotime($pret['date_pret']));
        $date_fin = date('d/m/Y', strtotime($pret['date_retour']));
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Date de début du prêt:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $date_debut), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Date de retour prévue:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $date_fin), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Assurance:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $pret['assurance'] . ' %'), 1, 1, 'L');
        
        $pdf->Ln(10);
        
        // Get grace period from database (replace 'delai_debut' with your actual field name)
        $grace_period = isset($pret['delai_debut']) ? intval($pret['delai_debut']) : 0;
        
        // Calculate amortization schedule with fixed calculation
        $capital = floatval($pret['montant']);
        $taux_mensuel = floatval($pret['taux']) / 100 / 12;
        $duree = intval($pret['delai']);
        $assurance_taux = floatval($pret['assurance']) / 100 / 12;
        $assurance_mensuelle = $capital * $assurance_taux;
        
        // Calculate monthly payment (after grace period)
        if ($taux_mensuel > 0) {
            // Standard calculation for positive interest rate
            // Note: We adjust the duration by subtracting the grace period
            $mensualite = $capital * $taux_mensuel * pow(1 + $taux_mensuel, $duree - $grace_period) / 
                         (pow(1 + $taux_mensuel, $duree - $grace_period) - 1);
        } else {
            // Simple division for zero interest rate
            $mensualite = $capital / ($duree - $grace_period);
        }
        
        // Calculate monthly payment during grace period (interest + insurance only)
        $mensualite_grace = $capital * $taux_mensuel + $assurance_mensuelle;
        
        // Add insurance to regular payment
        $mensualite_avec_assurance = $mensualite + $assurance_mensuelle;
        
        // Calculate totals
        $total_interest_grace = $capital * $taux_mensuel * $grace_period;
        $total_capital_regular = $capital;
        $total_interest_regular = ($mensualite * ($duree - $grace_period)) - $capital;
        $total_interets = $total_interest_grace + $total_interest_regular; // Changed from $total_interest to $total_interets
        $total_assurance = $assurance_mensuelle * $duree;
        $total_a_rembourser = $capital + $total_interets + $total_assurance; // Also update here
        $total_avec_assurance = $total_a_rembourser; // Define the missing variable
        
        // Display grace period information if applicable
        if ($grace_period > 0) {
            $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Délai de début du remboursement:'), 1, 0, 'L');
            $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $grace_period . ' mois'), 1, 1, 'L');
            
            $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Paiement pendant le délai (intérêts + assurance):'), 1, 0, 'L');
            $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($mensualite_grace, 2, ',', ' ') . ' $'), 1, 1, 'L');
        }
        
        // Summary of payments
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'SYNTHÈSE DU PRÊT'), 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 11);
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Mensualité (sans assurance):'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($mensualite, 2, ',', ' ') . ' $'), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Mensualité (avec assurance):'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($mensualite_avec_assurance, 2, ',', ' ') . ' $'), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Total des intérêts:'), 1, 0, 'L');
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($total_interets, 2, ',', ' ') . ' $'), 1, 1, 'L');
        
        $pdf->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1', 'Montant total à rembourser:'), 1, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', number_format($total_avec_assurance, 2, ',', ' ') . ' $'), 1, 1, 'L');
        
        $pdf->Ln(10);
        
        // Amortization schedule
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'ÉCHÉANCIER D\'AMORTISSEMENT'), 1, 1, 'L', true);
        
        // Table header with styling
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1', 'Mensualité'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Montant'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Intérêts'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Capital'), 1, 0, 'C', true);
        $pdf->Cell(40, 8, iconv('UTF-8', 'ISO-8859-1', 'Capital restant dû'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Assurance'), 1, 1, 'C', true);
        
        // Table content with alternating row colors
        $pdf->SetFont('Arial', '', 9);
        $solde = $capital;
        
        for ($i = 1; $i <= $duree; $i++) {
            // Alternate row colors
            $pdf->SetFillColor(245, 245, 245);
            $fill = ($i % 2 == 0) ? true : false;
            
            // Check if we're in grace period
            if ($i <= $grace_period) {
                // During grace period: pay only interest and insurance, no principal
                $interet = $solde * $taux_mensuel;
                $amortissement = 0; // No principal payment during grace period
                $payment = $interet + $assurance_mensuelle;
                
                // Use a different background color for grace period
                $pdf->SetFillColor(255, 240, 245);
                $fill = true;
            } else {
                // After grace period: regular amortization
                $interet = $solde * $taux_mensuel;
                $amortissement = $mensualite - $interet;
                $payment = $mensualite_avec_assurance;
                
                // Reset to normal alternating colors
                $pdf->SetFillColor(245, 245, 245);
                $fill = ($i % 2 == 0) ? true : false;
            }
            
            // Update remaining balance
            $solde -= $amortissement;
            
            // Ensure we don't display negative remaining balance
            if ($solde < 0.01) $solde = 0;
            
            $pdf->Cell(20, 7, $i, 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', number_format($payment, 2, ',', ' ') . ' $'), 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', number_format($interet, 2, ',', ' ') . ' $'), 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', number_format($amortissement, 2, ',', ' ') . ' $'), 1, 0, 'R', $fill);
            $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1', number_format($solde, 2, ',', ' ') . ' $'), 1, 0, 'R', $fill);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', number_format($assurance_mensuelle, 2, ',', ' ') . ' $'), 1, 1, 'R', $fill);
            
            // Add a page break every 25 rows
            if ($i % 25 == 0 && $i < $duree) {
                $pdf->AddPage();
                
                // Repeat the table header on new page
                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1', 'Mensualité'), 1, 0, 'C', true);
                $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Montant'), 1, 0, 'C', true);
                $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Intérêts'), 1, 0, 'C', true);
                $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Capital'), 1, 0, 'C', true);
                $pdf->Cell(40, 8, iconv('UTF-8', 'ISO-8859-1', 'Capital restant dû'), 1, 0, 'C', true);
                $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1', 'Assurance'), 1, 1, 'C', true);
                $pdf->SetFont('Arial', '', 9);
            }
        }
        
        // Add signature section at the end
        $pdf->Ln(15);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'Fait à ___________________, le _________________'), 0, 1, 'L');
        $pdf->Ln(10);
        
        $pdf->Cell(90, 10, iconv('UTF-8', 'ISO-8859-1', 'Signature du client:'), 0, 0, 'L');
        $pdf->Cell(90, 10, iconv('UTF-8', 'ISO-8859-1', 'Signature du représentant:'), 0, 1, 'L');
        $pdf->Cell(90, 20, '', 'B', 0, 'L');
        $pdf->Cell(90, 20, '', 'B', 1, 'L');
        
        // Output PDF as base64 string for API response
        $pdfOutput = $pdf->Output('S');
        return base64_encode($pdfOutput);
    }
}