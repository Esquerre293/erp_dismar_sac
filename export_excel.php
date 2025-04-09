<?php
session_start();

// Configuración de base de datos
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Reporte_Gastos_Dismar_'.date('Y-m-d').'.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
echo '<style>
    .excel-table {
        border-collapse: collapse;
        width: 100%;
        font-family: "Calibri", sans-serif;
    }
    .header-cell {
        background: #2c5f8a;
        color: white;
        font-weight: bold;
        padding: 12px;
        border: 1px solid #1a3a5a;
        text-align: center;
        font-size: 12pt;
    }
    .data-cell {
        padding: 10px;
        border: 1px solid #e0e0e0;
        font-size: 11pt;
    }
    .currency {
        text-align: right;
        mso-number-format:"#,##0\.00_ ;\\-#,##0\.00\\ ";
    }
    .total-row {
        background: #e3eff9;
        font-weight: bold;
    }
    .date-format {
        mso-number-format:"Short Date";
        text-align: center;
    }
    .subheader {
        background: #f8f9fa;
        font-size: 10pt;
        padding: 8px;
        border: 1px solid #e0e0e0;
    }
</style></head><body>';

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Cabecera del reporte
    echo '<table class="excel-table">';
    
    // Título principal
    echo '<tr><td colspan="6" style="background: #2c5f8a; color: white; padding: 15px; font-size: 14pt; text-align: center;">
            REPORTE DE GASTOS - DISMAR SAC
          </td></tr>';
    
    // Subcabecera informativa
    echo '<tr><td colspan="6" class="subheader">
            Generado por: '.($_SESSION['usuario'] ?? 'Sistema').' | 
            Fecha: '.date('d/m/Y H:i').' | 
            Periodo: '.date('F Y').'
          </td></tr>';
    
    // Encabezados de columnas
    echo '<tr>
            <th class="header-cell">FECHA</th>
            <th class="header-cell">CATEGORÍA</th>
            <th class="header-cell">DESCRIPCIÓN</th>
            <th class="header-cell">MONTO (S/.)</th>
            <th class="header-cell">MÉTODO PAGO</th>
            <th class="header-cell">COMPROBANTE</th>
          </tr>';

    // Datos del reporte
    $result = $conn->query("SELECT * FROM gastos ORDER BY fecha DESC");
    $total = 0;
    $rowCount = 0;
    
    while($row = $result->fetch_assoc()) {
        $rowStyle = ($rowCount++ % 2) ? 'background: #f8f9fa;' : '';
        $total += $row['monto'];
        
        echo '<tr style="'.$rowStyle.'">
                <td class="data-cell date-format">'.date('d/m/Y', strtotime($row['fecha'])).'</td>
                <td class="data-cell">'.mb_strtoupper($row['categoria']).'</td>
                <td class="data-cell">'.$row['detalle'].'</td>
                <td class="data-cell currency">'.number_format($row['monto'], 2, '.', ',').'</td>
                <td class="data-cell">'.$row['metodo_pago'].'</td>
                <td class="data-cell">'.($row['comprobante'] ?? 'N/A').'</td>
              </tr>';
    }

    // Fila de totales
    echo '<tr class="total-row">
            <td colspan="3" class="data-cell" style="text-align: right;">TOTAL GENERAL</td>
            <td class="data-cell currency">'.number_format($total, 2, '.', ',').'</td>
            <td colspan="2" class="data-cell"></td>
          </tr>';

    // Pie del reporte
    echo '<tr><td colspan="6" style="padding: 10px; font-size: 9pt; color: #666;">
            * Montos en Soles (PEN)<br>
            * Documento generado automáticamente - '.date('d/m/Y H:i').'
          </td></tr>';

    echo '</table>';

} catch (Exception $e) {
    echo '<div style="color: #dc3545; padding: 15px; border: 1px solid #dc3545; margin: 20px;">
            Error al generar reporte: '.$e->getMessage().'
          </div>';
} finally {
    if (isset($conn)) $conn->close();
}

echo '</body></html>';
exit;
?>