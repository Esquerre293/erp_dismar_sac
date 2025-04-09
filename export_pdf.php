<?php
session_start();
require __DIR__ . '/tcpdf/tcpdf.php';

// Configuración de la base de datos
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';

$conn = new mysqli($host, $user, $pass, $db, $port);

class MYPDF extends TCPDF {
    
    private $companyInfo = [
        'name' => 'SERVICIOS PESQUEROS DISMAR',
        'ruc' => '20529932881',
        'address' => 'Cal. los Laureles Nro. 311 a.H. Victor Raul (a Espaldas del Estadio)',
        'phone' => '934558212',
        'email' => 'administracion@dismarsac.com'
    ];

    public function Header() {
        // Fondo de cabecera
        $this->SetFillColor(245, 247, 250);
        $this->Rect(0, 0, 210, 50, 'F');
        
        // Bloque izquierdo (Información de empresa)
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(45, 55, 72);
        $this->SetY(15);
        $this->Cell(80, 6, $this->companyInfo['name'], 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(113, 128, 150);
        $this->Cell(80, 4, 'RUC: '.$this->companyInfo['ruc'], 0, 1, 'L');
        $this->Cell(80, 4, $this->companyInfo['address'], 0, 1, 'L');
        $this->Cell(80, 4, 'TELF: '.$this->companyInfo['phone'], 0, 1, 'L');
        $this->Cell(80, 4, 'EMAIL: '.$this->companyInfo['email'], 0, 1, 'L');

        // Bloque derecho (Título del reporte)
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(66, 153, 225);
        $this->SetY(15);
        $this->SetX(-90);
        
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(113, 128, 150);
        $this->SetX(-90);
        
        $this->SetX(-90);
        

        // Línea divisoria
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(66, 153, 225);
        $this->Line(15, 50, 195, 50);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(113, 128, 150);
        $this->Cell(0, 5, 'Sistema de Gestión de Gastos - '.$this->companyInfo['name'], 0, 0, 'L');
        $this->Cell(0, 5, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R');
    }
}

// Crear PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetTitle('Reporte de Gastos - '.date('Y-m-d'));
$pdf->SetMargins(15, 55, 15);
$pdf->AddPage();

// Estilo de tabla mejorado
$tableStyle = '
<style>
    .header-row {
        background-color: #2D3748;
        color: #FFFFFF;
        font-weight: bold;
        font-size: 10pt;
    }
    .data-row {
        font-size: 9pt;
        border-bottom: 1px solid #E2E8F0;
    }
    .even-row {
        background-color: #F7FAFC;
    }
    .total-row {
        background-color: #2D3748;
        color: #FFFFFF;
        font-weight: bold;
    }
    .transferencia { background-color: #4299E1; color: white; }
    .yape { background-color: #805AD5; color: white; }
    .plin { background-color: #48BB78; color: white; }
    .efectivo { background-color: #F6E05E; color: #1A202C; }
</style>';

// Encabezado de tabla
$html = $tableStyle.'
<table border="0" cellpadding="4" cellspacing="0">
    <tr class="header-row">
        <th width="18%">Fecha</th>
        <th width="18%">Categoría</th>
        <th width="30%">Detalle</th>
        <th width="14%" align="right">Monto (S/)</th>
        <th width="20%" align="center">Método Pago</th>
    </tr>';

// Datos de la tabla
$result = $conn->query("SELECT * FROM gastos ORDER BY fecha DESC");
$total = 0;
$rowCount = 0;

while($row = $result->fetch_assoc()) {
    $total += $row['monto'];
    $rowClass = ($rowCount++ % 2 == 0) ? 'data-row' : 'data-row even-row';
    $pagoClass = strtolower($row['metodo_pago']);
    
    $html .= '
    <tr class="'.$rowClass.'">
        <td>'.$row['fecha'].'</td>
        <td>'.$row['categoria'].'</td>
        <td>'.$row['detalle'].'</td>
        <td align="right">'.number_format($row['monto'], 2).'</td>
        <td align="center" class="'.$pagoClass.'">'.strtoupper($row['metodo_pago']).'</td>
    </tr>';
}

// Total
$html .= '
    <tr class="total-row">
        <td colspan="3" align="right"><b>TOTAL GENERAL</b></td>
        <td align="right"><b>'.number_format($total, 2).'</b></td>
        <td></td>
    </tr>
</table>';

// Añadir tabla al PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Generar PDF
$pdf->Output('reporte_gastos_'.date('Ymd_His').'.pdf', 'D');
exit;