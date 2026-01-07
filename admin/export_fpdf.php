<?php
require('./fpdf.php'); 
session_start();

$conn = new mysqli("localhost", "root", "", "gestion_examens_db");

// --- LOGIQUE DE FILTRE ---
$where_clause = "";
$titre_document = "CALENDRIER GÉNÉRAL DES EXAMENS";

if (isset($_GET['formation_id']) && !empty($_GET['formation_id'])) {
    $fid = intval($_GET['formation_id']);
    $where_clause = " WHERE f.id = $fid ";
    
    // On récupère le nom de la formation pour le titre
    $res_name = $conn->query("SELECT nom FROM formations WHERE id = $fid");
    if($f_info = $res_name->fetch_assoc()) {
        $titre_document = "EMPLOI DU TEMPS : " . strtoupper($f_info['nom']);
    }
}

class PDF extends FPDF {
    protected $title_pdf;
    function setDocTitle($t) { $this->title_pdf = $t; }

    function Header() {
        if(file_exists('../logo.png')){ $this->Image('../logo.png', 10, 6, 25); }
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 15, utf8_decode($this->title_pdf), 0, 1, 'C');
        $this->Ln(5);
        
        // En-tête du tableau
        $this->SetFillColor(0, 51, 102);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(35, 10, 'Date', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Heure', 1, 0, 'C', true);
        $this->Cell(70, 10, 'Module', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Salle', 1, 1, 'C', true);
        $this->SetTextColor(0);
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->setDocTitle($titre_document);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// --- REQUÊTE FILTRÉE ---
$query = "SELECT e.*, m.nom as mod_nom, s.nom as salle_nom, f.nom as form_nom
          FROM examens e 
          JOIN modules m ON e.module_id = m.id 
          JOIN formations f ON m.formation_id = f.id
          JOIN lieu_examen s ON e.salle_id = s.id 
          $where_clause
          ORDER BY e.date_examen, e.heure_debut";

$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
    $pdf->Cell(35, 10, date('d/m/Y', strtotime($row['date_examen'])), 1, 0, 'C');
    $pdf->Cell(25, 10, substr($row['heure_debut'], 0, 5), 1, 0, 'C');
    $pdf->Cell(70, 10, utf8_decode($row['mod_nom']), 1, 0, 'L');
    $pdf->Cell(60, 10, utf8_decode($row['salle_nom']), 1, 1, 'L');
}

$pdf->Output('I', 'Emploi_du_temps.pdf');
?>