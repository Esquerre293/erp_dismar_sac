<?php
require_once('tcpdf/tcpdf.php');

// Configuración de la base de datos
$conn = new mysqli('127.0.0.1', 'root', '', 'dismar_sac', 3309);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Obtener ID de la venta
$venta_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar la venta
$sql = "SELECT * FROM ventas_hielo WHERE id = $venta_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) die("Venta no encontrada");
$venta = $result->fetch_assoc();

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetTitle("Comprobante " . $venta['numero_comprobante']);
$pdf->AddPage();

// Contenido HTML del comprobante
$html = '
<style>
    .header { text-align: center; margin-bottom: 20px; }
    .info { margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 10px; }
    .total { font-size: 18px; font-weight: bold; text-align: right; }
</style>

<div class="header">
    <h1>' . strtoupper($venta['tipo_comprobante']) . ' ELECTRÓNICA</h1>
    <h3>HIELO DISMAR SAC</h3>
    <p>RUC: 12345678901 | Dirección: Av. Principal 123 - Lima</p>
</div>

<div class="info">
    <p><strong>N° Comprobante:</strong> ' . $venta['numero_comprobante'] . '</p>
    <p><strong>Fecha:</strong> ' . $venta['fecha'] . '</p>
    <p><strong>Cliente:</strong> ' . $venta['cliente'] . '</p>
    <p><strong>Método de Pago:</strong> ' . $venta['metodo_pago'] . '</p>
</div>

<table>
    <tr>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Total</th>
    </tr>
    <tr>
        <td>Venta de Hielo</td>
        <td>' . $venta['peso_toneladas'] . ' Ton</td>
        <td>S/ 100.00</td>
        <td>S/ ' . number_format($venta['peso_toneladas'] * 100, 2) . '</td>
    </tr>
</table>

<div class="total">
    <h3>TOTAL: S/ ' . number_format($venta['peso_toneladas'] * 100, 2) . '</h3>
</div>';

// Generar PDF
$pdf->writeHTML($html);

// Mostrar versión HTML o descargar PDF
if (!isset($_GET['pdf'])) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>' . htmlspecialchars($venta['tipo_comprobante']) . ' ' . htmlspecialchars($venta['numero_comprobante']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .container { max-width: 800px; margin: 0 auto; }
            .descargar-pdf { 
                display: inline-block; 
                padding: 10px 20px; 
                background: #007bff; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            ' . $html . '
            <a href="?id=' . $venta_id . '&pdf=1" class="descargar-pdf">Descargar PDF</a>
        </div>
    </body>
    </html>';
} else {
    $pdf->Output('comprobante_' . $venta['numero_comprobante'] . '.pdf', 'D');
}

$conn->close();
?>