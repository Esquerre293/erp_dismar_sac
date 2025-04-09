<?php
// Configuración de la base de datos
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear directorio de uploads si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $idEliminar = intval($_POST['eliminar_id']);
    
    // Eliminar archivo si existe
    $archivoQuery = $conn->query("SELECT archivo_comprobante FROM transacciones_hielo WHERE id = $idEliminar");
    if ($archivoQuery->num_rows > 0) {
        $archivo = $archivoQuery->fetch_assoc()['archivo_comprobante'];
        if (!empty($archivo) && file_exists("uploads/$archivo")) {
            unlink("uploads/$archivo");
        }
    }

    // Eliminar de la base de datos
    $conn->query("DELETE FROM transacciones_hielo WHERE id = $idEliminar");
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrar_venta'])) {
        // Procesar venta
        $fecha = $conn->real_escape_string($_POST['fecha']);
        $cliente = $conn->real_escape_string($_POST['cliente']);
        $peso_total = (float)$_POST['peso_total'];
        $metodo_pago = $conn->real_escape_string($_POST['metodo_pago']);

        $notas = $conn->real_escape_string($_POST['notas']);
        
        $conn->query("INSERT INTO transacciones_hielo 
                     (fecha, cliente, peso_total, peso_pagado, metodo_pago, notas)
                     VALUES ('$fecha', '$cliente', $peso_total, $peso_total, '$metodo_pago',  '$notas')");
    }

    
   // Procesar Pago de Crédito
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar_credito'])) {
    try {
        $id = (int)$_POST['id'];
        
        // 1. Obtener el crédito actual
        $stmt = $conn->prepare("SELECT peso_credito, peso_pagado 
                              FROM transacciones_hielo 
                              WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $credito = $data['peso_credito'];
        $pagado_actual = $data['peso_pagado'];
        
        // 2. Calcular nuevo pagado
        $nuevo_pagado = $pagado_actual + $credito;
        
        // 3. Actualizar los valores
        $update = $conn->prepare("UPDATE transacciones_hielo 
                                SET peso_pagado = ?,
                                    peso_credito = 0
                                WHERE id = ?");
        $update->bind_param('di', $nuevo_pagado, $id);
        $update->execute();
        
        $_SESSION['mensaje'] = "✅ Crédito transferido a pagado exitosamente";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;

    } catch(Exception $e) {
        $_SESSION['mensaje'] = "❌ Error: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}


    if (isset($_POST['registrar_credito'])) {
        // Procesar crédito
        $fecha = $conn->real_escape_string($_POST['fecha']);
        $cliente = $conn->real_escape_string($_POST['cliente']);
        $peso_total = (float)$_POST['peso_total'];
        $peso_pagado = (float)$_POST['peso_pagado'];
        $metodo_pago = $conn->real_escape_string($_POST['metodo_pago']);
        $fecha_pago = $conn->real_escape_string($_POST['fecha_pago']);
        $notas = $conn->real_escape_string($_POST['notas']);
        
        $conn->query("INSERT INTO transacciones_hielo 
                     (fecha, cliente, peso_total, peso_pagado, peso_credito, metodo_pago, fecha_pago_credito, notas)
                     VALUES ('$fecha', '$cliente', $peso_total, $peso_pagado, ".($peso_total-$peso_pagado).", '$metodo_pago', '$fecha_pago', '$notas')");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Ventas - Hielo</title>
    <style>
        :root {
            --primary: #2D3748;
            --secondary: #4A5568;
            --accent: #4299E1;
            --background: #FFFFFF;
            --surface: #F7FAFC;
            --success: #48BB78;
            --error: #F56565;
            --text-primary: #1A202C;
            --text-secondary: #718096;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--surface);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 2rem;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #E2E8F0;
        }

        h1, h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        /* Botones Principales */
        .botones-principales {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .botones-principales button {
            padding: 0.75rem 1.5rem;
            min-width: 120px;
            text-align: center;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 40px;
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 4px rgba(45, 55, 72, 0.1);
        }

        .botones-principales button:hover {
            background: #1A202C;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(45, 55, 72, 0.15);
        }

        /* Tablas */
        .table-container {
            background: var(--background);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #EDF2F7;
        }

        th {
            background: var(--surface);
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: var(--surface);
        }

        .credito-pendiente {
            background-color: rgba(245, 101, 101, 0.05);
        }

        .credito-pendiente:hover {
            background-color: rgba(245, 101, 101, 0.1);
        }

        /* Modales */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background: var(--background);
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            padding: 2rem;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: modalFadeIn 0.3s ease-out;
            position: relative;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cerrar {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .cerrar:hover {
            color: var(--text-primary);
        }

        /* Formularios */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .form-input, .form-input select, .form-input textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus, .form-input select:focus, .form-input textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .form-input textarea {
            min-height: 100px;
            resize: vertical;
        }

        button[type="submit"] {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success);
            color: white;
            margin-top: 1rem;
        }

        button[type="submit"]:hover {
            background: #38a169;
            transform: translateY(-1px);
        }

        /* Enlaces */
        a {
            color: var(--accent);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1.25rem;
            }

            .botones-principales {
                flex-direction: column;
            }

            .botones-principales button {
                width: 100%;
            }

            .modal-content {
                padding: 1.5rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Botones Principales -->
        <div class="botones-principales">
            <button class="btn-primary" onclick="abrirModal('modalVenta')">Registrar Venta</button>
            <button class="btn-primary" onclick="abrirModal('modalCredito')">Registrar Crédito</button>
            <button class="btn-primary" onclick="abrirModal('modalVerCreditos')">Ver Créditos</button>
             <!-- Botón de Estadísticas -->
    <a href="estadisticas_hielo.php" class="btn-estadisticas">
        <svg class="icon-stats" viewBox="0 0 24 24">
            <path d="M10 20H4V10H10V20ZM14 20H10V4H14V20ZM20 20H14V13H20V20Z"/>
        </svg>
        Ver Estadísticas
    </a>

    

<!-- Botón actualizado -->
<button class="btn-exportar" onclick="mostrarModalExportacion()">
    <svg class="icon-pdf" viewBox="0 0 24 24">
        <path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-2 13c0 .6-.4 1-1 1s-1-.4-1-1v-2H9v2c0 1.7 1.3 3 3 3s3-1.3 3-3v-2h-2v2zm-1-8V3.5L18.5 10H12z"/>
    </svg>
    Exportar PDF
</button>

<style>
/* Botón principal de exportación */
.btn-exportar {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-exportar:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(231, 76, 60, 0.25);
    background: linear-gradient(135deg, #c0392b, #e74c3c);
}

.btn-exportar:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Icono PDF personalizado */
.btn-exportar .icon-pdf {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    fill: white;
    transition: transform 0.2s ease;
}

.btn-exportar:hover .icon-pdf {
    transform: scale(1.1);
}

/* Modal de exportación */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    backdrop-filter: blur(3px);
}

.modal-contenido {
    background: white;
    margin: 10vh auto;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    position: relative;
    animation: modalEntrada 0.3s ease-out;
}

@keyframes modalEntrada {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Botones de periodo */
.btn-periodo {
    background: #2ecc71;
    color: white;
    padding: 14px 30px;
    margin: 15px 10px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 45%;
    min-width: 120px;
    display: inline-block;
}

.btn-periodo:hover {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
}

.btn-periodo:active {
    transform: translateY(0);
}

/* Texto del modal */
.modal-contenido h3 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.4em;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 15px;
}

/* Botón cerrar */
.cerrar {
    color: #95a5a6;
    position: absolute;
    right: 25px;
    top: 15px;
    font-size: 32px;
    font-weight: 300;
    transition: color 0.2s ease;
}

.cerrar:hover {
    color: #e74c3c;
}

@media (max-width: 480px) {
    .btn-periodo {
        width: 100%;
        margin: 10px 0;
    }
    
    .modal-contenido {
        padding: 20px;
        margin: 5vh auto;
    }
}
</style>

<!-- Modal (sin cambios en estructura) -->
<div id="modalExportacion" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModalExportacion()">&times;</span>
        <h3>Seleccionar periodo de exportación</h3>
        <button class="btn-periodo" onclick="generarPDF('semanal')">Semanal</button>
        <button class="btn-periodo" onclick="generarPDF('mensual')">Mensual</button>
    </div>
</div>

    
    


<style>
/* Estilo específico para el botón de estadísticas */
.btn-estadisticas {
    background: #3498db !important; /* Color celeste */
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-estadisticas:hover {
    background: #2980b9 !important;
    transform: translateY(-1px);
}

.icon-stats {
    width: 20px;
    height: 20px;
    fill: white;
}

/* Estilo para el botón volver */
.btn-volver {
    background: #95a5a6 !important;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-volver:hover {
    background: #7f8c8d !important;
}

.icon-return {
    width: 20px;
    height: 20px;
    fill: white;
}

/* Mantener consistencia con otros botones */
.botones-principales {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

</style>          
            <!-- Notification Bell -->
<div class="notificacion-bell" aria-label="Notificaciones">
    <div class="bell-container" role="button" aria-expanded="false">
        <svg class="bell-icon" viewBox="0 0 24 24">
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
        <?php
        $count = 0;
        try {
            $hoy = new DateTime();
            $tresDias = clone $hoy;
            $tresDias->modify('+3 days');
            
            $hoyFormatted = $hoy->format('Y-m-d');
            $tresDiasFormatted = $tresDias->format('Y-m-d');
            
            $stmt = $conn->prepare("SELECT COUNT(*) as total 
                                  FROM transacciones_hielo 
                                  WHERE peso_credito > 0 
                                  AND fecha_pago_credito BETWEEN ? AND ?");
            $stmt->bind_param('ss', $hoyFormatted, $tresDiasFormatted);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error en notificaciones: " . $e->getMessage());
        }
        ?>
        <span class="badge <?= $count > 0 ? 'has-notification' : '' ?>"><?= $count > 0 ? $count : '' ?></span>
        
        <div class="notificaciones-dropdown">
            <?php
            try {
                $stmt = $conn->prepare("SELECT *, 
                                        DATEDIFF(fecha_pago_credito, CURDATE()) as dias_restantes 
                                        FROM transacciones_hielo 
                                        WHERE peso_credito > 0 
                                        AND fecha_pago_credito BETWEEN ? AND ?
                                        ORDER BY fecha_pago_credito ASC");
                $stmt->bind_param('ss', $hoyFormatted, $tresDiasFormatted);
                $stmt->execute();
                $pendientes = $stmt->get_result();
                
                if($pendientes->num_rows > 0): 
                    while($notif = $pendientes->fetch_assoc()): 
                        $urgente = $notif['dias_restantes'] <= 0;
                        $statusClass = $urgente ? 'urgente' : ($notif['dias_restantes'] <= 1 ? 'alerta' : '');
                        $fechaPago = date('d/m/Y', strtotime($notif['fecha_pago_credito']));
                        ?>
                        <div class="notificacion-item <?= $statusClass ?>">
                            <div class="notificacion-icon">
                                <?php if($urgente): ?>
                                    <svg class="icon-warning" viewBox="0 0 24 24">
                                        <path d="M12 2L1 21h22L12 2zm0 4l7.53 13H4.47L12 6zm-1 4v4h2v-4h-2zm0 6v2h2v-2h-2z"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="icon-info" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="notificacion-content">
                                <div class="notificacion-header">
                                    <strong><?= htmlspecialchars($notif['cliente']) ?></strong>
                                    <span class="dias-restantes">
                                        <?= $urgente ? 'Hoy' : $notif['dias_restantes'] . ' días' ?>
                                    </span>
                                </div>
                                <div class="notificacion-details">
                                    <span class="peso"><?= number_format($notif['peso_credito'], 2) ?>T</span>
                                    <span class="fecha"><?= $fechaPago ?></span>
                                </div>
                            </div>
                            <form method="POST" class="accion-rapida">
                                <input type="hidden" name="id" value="<?= $notif['id'] ?>">
                                <button type="submit" name="pagar_credito" class="btn-check" title="Marcar como pagado">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <div class="notificacion-empty">
                        <svg class="icon-success" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <div class="empty-message">
                            <h4>¡Todo en orden!</h4>
                            <p>No hay créditos pendientes</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php } catch (Exception $e) { ?>
                <div class="notificacion-empty error">
                    <svg class="icon-error" viewBox="0 0 24 24">
                        <path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                    </svg>
                    <div class="empty-message">
                        <h4>Error del sistema</h4>
                        <p>No se pudieron cargar las notificaciones</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<style>
:root {
    --color-primary: #2c3e50;
    --color-danger: #e74c3c;
    --color-success: #2ecc71;
    --color-warning: #f1c40f;
    --color-text: #34495e;
    --color-bg: #ffffff;
}

.notificacion-bell {
    position: fixed;
    top: 20px;
    right: 30px;
    z-index: 1000;
}

.bell-container {
    position: relative;
    cursor: pointer;
    background: var(--color-bg);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.bell-container:hover {
    transform: scale(1.05);
}

.bell-icon {
    width: 24px;
    height: 24px;
    fill: var(--color-primary);
}

.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--color-danger);
    color: white;
    font-size: 0.8rem;
    min-width: 22px;
    height: 22px;
    border-radius: 11px;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 3px;
}

.badge.has-notification {
    display: flex;
    animation: ping 1.5s ease-in-out infinite;
}

@keyframes ping {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.notificaciones-dropdown {
    position: absolute;
    right: 0;
    top: 60px;
    width: 350px;
    max-height: 60vh;
    background: var(--color-bg);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    overflow-y: auto;
}

.notificaciones-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notificacion-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
    position: relative;
}

.notificacion-item:hover {
    background: #f9f9f9;
}

.notificacion-item.urgente {
    border-left: 4px solid var(--color-danger);
    background: rgba(231, 76, 60, 0.03);
}

.notificacion-item.alerta {
    border-left: 4px solid var(--color-warning);
}

.notificacion-icon svg {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.icon-warning { fill: var(--color-danger); }
.icon-info { fill: var(--color-primary); }

.notificacion-content {
    flex-grow: 1;
}

.notificacion-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.dias-restantes {
    font-weight: 600;
    color: var(--color-danger);
}

.notificacion-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: var(--color-text);
}

.btn-check {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--color-success);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: all 0.3s ease;
}

.notificacion-item:hover .btn-check {
    opacity: 1;
}

.btn-check svg {
    width: 18px;
    height: 18px;
    fill: white;
}

.notificacion-empty {
    padding: 25px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.notificacion-empty.error {
    color: var(--color-danger);
}

.icon-success {
    width: 48px;
    height: 48px;
    fill: var(--color-success);
}

.icon-error {
    width: 48px;
    height: 48px;
    fill: var(--color-danger);
}

.empty-message h4 {
    margin: 0;
    font-size: 1.1rem;
    color: inherit;
}

.empty-message p {
    margin: 5px 0 0;
    font-size: 0.9rem;
    color: #7f8c8d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bell = document.querySelector('.bell-container');
    const dropdown = document.querySelector('.notificaciones-dropdown');
    
    // Toggle notifications
    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });
    
    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
    
    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') dropdown.classList.remove('show');
    });
});
</script>
            

<!-- Modal de Créditos -->
<div id="modalVerCreditos" class="modal">
    <div class="modal-content-cred">
        <span class="cerrar" onclick="cerrarModal('modalVerCreditos')">&times;</span>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Total (T)</th>
                        <th>Pagado (T)</th>
                        <th>Pendiente (T)</th>
                        <th>Fecha Límite</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $creditos = $conn->query("SELECT * 
                                            FROM transacciones_hielo 
                                            WHERE peso_credito > 0
                                            ORDER BY fecha_pago_credito ASC");
                    
                    if ($creditos->num_rows > 0):
                        while ($credito = $creditos->fetch_assoc()): 
                            $fecha_limite = date('d/m/Y', strtotime($credito['fecha_pago_credito']));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($credito['cliente']) ?></td>
                        <td><?= number_format($credito['peso_total'], 2) ?></td>
                        <td><?= number_format($credito['peso_pagado'], 2) ?></td>
                        <td class="texto-rojo"><?= number_format($credito['peso_credito'], 2) ?></td>
                        <td><?= $fecha_limite ?></td>
                        <td>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $credito['id'] ?>">
        <button type="submit" 
                name="pagar_credito" 
                class="btn-pagar"
                title="Transferir crédito a pagado">
            Transferir a Pagado
        </button>
    </form>
</td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6">No existen créditos pendientes</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<style>
:root {
    --notification-bg: #ffffff;
    --notification-border: #e0e0e0;
    --notification-primary: #2c3e50;
    --notification-error: #e74c3c;
    --notification-warning: #f1c40f;
    --notification-success: #2ecc71;
    --notification-surface: #f8f9fa;
    --notification-text: #34495e;
}

.btn-pago-completo {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-pago-completo:hover {
    background: #45a049;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.texto-rojo {
    color: #e74c3c;
    font-weight: bold;
}

.modal-content-cred {
    background: white;
    max-width: 800px;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
}

.notificacion-bell {
    position: fixed;
    top: 1.5rem;
    right: 2rem;
    z-index: 1000;
}

.bell-container {
    position: relative;
    cursor: pointer;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--notification-bg);
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--notification-border);
}

.bell-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.bell-icon {
    width: 24px;
    height: 24px;
    fill: var(--notification-primary);
    transition: transform 0.3s ease;
}

.badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: var(--notification-error);
    color: white;
    border-radius: 12px;
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
    display: none;
}

.badge.has-notification {
    display: block;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.notificaciones-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 1rem);
    width: 360px;
    background: var(--notification-bg);
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    max-height: 60vh;
    overflow-y: auto;
    opacity: 0;
    transform: translateY(-10px);
    visibility: hidden;
    transition: all 0.3s ease;
}

.notificaciones-dropdown.show {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
}

.notificacion-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--notification-border);
    transition: background 0.2s ease;
}

.notificacion-item:last-child {
    border-bottom: none;
}

.notificacion-item:hover {
    background: var(--notification-surface);
}

.notificacion-item.urgente {
    background: rgba(231, 76, 60, 0.05);
    border-left: 4px solid var(--notification-error);
}

.notificacion-item.alerta {
    border-left: 4px solid var(--notification-warning);
}

.notificacion-icon svg {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.icon-warning {
    fill: var(--notification-error);
}

.icon-info {
    fill: var(--notification-primary);
}

.notificacion-content {
    flex-grow: 1;
}

.notificacion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.cliente {
    color: var(--notification-primary);
    font-size: 0.95rem;
    font-weight: 600;
}

.dias-restantes {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--notification-error);
}

.notificacion-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--notification-text);
}

.peso {
    font-weight: 500;
}

.fecha {
    color: #7f8c8d;
}

.notificacion-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #95a5a6;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.icon-check {
    width: 32px;
    height: 32px;
    fill: var(--notification-success);
}

@keyframes ping {
    75%, 100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

/* Estilos exclusivos para el botón de eliminar */
.btn-delete {
    --size: 40px;
    --icon-color: hsl(220, 3%, 40%);
    --hover-bg: hsl(3, 82%, 95%);
    --hover-color: hsl(3, 82%, 55%);
    --border: 1px solid hsl(220, 10%, 90%);
    
    width: var(--size);
    height: var(--size);
    background: transparent;
    border: var(--border);
    border-radius: 10px;
    padding: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.delete-icon {
    width: 20px;
    height: 20px;
    stroke: var(--icon-color);
    stroke-width: 1.9;
    transition: all 0.3s ease;
}

/* Efectos hover */
.btn-delete:hover {
    background: var(--hover-bg);
    border-color: hsl(3, 82%, 85%);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

.btn-delete:hover .delete-icon {
    stroke: var(--hover-color);
    transform: scale(1.08);
}

/* Efecto active */
.btn-delete:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Focus state */
.btn-delete:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px hsl(3, 82%, 85%);
}

/* Borde sutil de profundidad */
.btn-delete::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 8px;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8);
    mix-blend-mode: overlay;
    pointer-events: none;
}

           
</style>
        </div>

        <!-- Tabla de Transacciones -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total (T)</th>
                        <th>Pagado (T)</th>
                        <th>Crédito (T)</th>
                        <th>Método Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM transacciones_hielo ORDER BY fecha DESC");
                    while ($row = $result->fetch_assoc()):
                        $creditoClass = $row['peso_credito'] > 0 ? 'credito-pendiente' : '';
                    ?>
                    <tr class="<?= $creditoClass ?>">
                        <td><?= $row['fecha'] ?></td>
                        <td><?= $row['cliente'] ?></td>
                        <td><?= number_format($row['peso_total'], 2) ?></td>
                        <td><?= number_format($row['peso_pagado'], 2) ?></td>
                        <td><?= number_format($row['peso_credito'], 2) ?></td>
                        <td><?= $row['metodo_pago'] ?></td>
                        <td>
                        <form method="POST" action="" class="delete-form" style="margin-left: 15px;">
    <input type="hidden" name="eliminar_id" value="<?= $row['id'] ?>">
    <button  class="btn-delete" aria-label="Eliminar transacción">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="delete-icon">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" 
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a2 2 0 012 2v0a2 2 0 01-2 2H7a2 2 0 01-2-2v0a2 2 0 012-2h10z"/>
        </svg>
    </button>
</form>

<!-- Modal de confirmación personalizado -->
<div class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-header">
            <h3>Confirmar Eliminación</h3>
        </div>
        <div class="delete-modal-body">
            <p>¿Estás seguro de eliminar esta transacción? Esta acción no se puede deshacer.</p>
        </div>
        <div class="delete-modal-footer">
            <button class="delete-modal-cancel">Cancelar</button>
            <button class="delete-modal-confirm">Eliminar</button>
        </div>
    </div>
</div>

<style>
/* Estilos modificados para el botón */
.delete-form {
    margin-left: auto; /* Empuja el botón a la derecha */
    display: inline-block;
}

.btn-delete {
    --size: 40px;
    --icon-color: hsl(220, 3%, 40%);
    --hover-bg: hsl(3, 82%, 95%);
    --hover-color: hsl(3, 82%, 55%);
    --border: 1px solid hsl(220, 10%, 90%);
    
    width: var(--size);
    height: var(--size);
    background: transparent;
    border: var(--border);
    border-radius: 10px;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    margin-left: 3px; /* Espaciado adicional a la izquierda */
}

/* Estilos del modal personalizado */
.delete-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.delete-modal.show {
    display: flex;
    opacity: 1;
}

.delete-modal-content {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.delete-modal.show .delete-modal-content {
    transform: translateY(0);
}

.delete-modal-header h3 {
    margin: 0;
    color: #2d3748;
    font-size: 1.4rem;
    font-weight: 600;
}

.delete-modal-body p {
    color: #4a5568;
    margin: 15px 0;
    line-height: 1.5;
}

.delete-modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.delete-modal-footer button {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.delete-modal-cancel {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    color: #4a5568;
}

.delete-modal-cancel:hover {
    background: #edf2f7;
}

.delete-modal-confirm {
    background: #ef4444;
    border: 1px solid #ef4444;
    color: white;
}

.delete-modal-confirm:hover {
    background: #dc2626;
    border-color: #dc2626;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.delete-form');
    const modal = document.querySelector('.delete-modal');
    const cancelBtn = document.querySelector('.delete-modal-cancel');
    const confirmBtn = document.querySelector('.delete-modal-confirm');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            modal.classList.add('show');
        });
    });
    
    cancelBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });
    
    confirmBtn.addEventListener('click', () => {
        deleteForms.forEach(form => form.submit());
        modal.classList.remove('show');
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
});
</script>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- R Venta -->
        <div id="modalVenta" class="modal">
            <div class="modal-content">
                <span class="cerrar" onclick="cerrarModal('modalVenta')">&times;</span>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <input type="date" name="fecha" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <input type="text" name="cliente" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_total" class="form-label">Peso Total (Toneladas):</label>
                        <input type="number" name="peso_total" class="form-input" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_pago" class="form-label">Método de Pago:</label>
                        <select name="metodo_pago" class="form-input" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Cuenta Señor Nestor">Cuenta Señor Nestor</option>
                            <option value="Cuenta D.Seafood-Hielo">Cuenta D.Seafood-Hielo</option>
                            <option value="Cuenta Fiesta BCP">Cuenta Fiesta BCP</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notas" class="form-label">Notas:</label>
                        <textarea name="notas" class="form-input"></textarea>
                    </div>
                    
                    <button type="submit" name="registrar_venta">Guardar Venta</button>
                </form>
            </div>
        </div>

        <!-- Registrar Crédito -->
        <div id="modalCredito" class="modal">
            <div class="modal-content">
                <span class="cerrar" onclick="cerrarModal('modalCredito')">&times;</span>
                <form method="POST">
                    <div class="form-group">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <input type="date" name="fecha" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <input type="text" name="cliente" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_total" class="form-label">Peso Total (Toneladas):</label>
                        <input type="number" name="peso_total" class="form-input" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_pagado" class="form-label">Peso Pagado (Toneladas):</label>
                        <input type="number" name="peso_pagado" class="form-input" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_pago" class="form-label">Método de Pago:</label>
                        <select name="metodo_pago" class="form-input" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Cuenta Señor Nestor">Cuenta Señor Nestor</option>
                            <option value="Cuenta D.Seafood-Hielo">Cuenta D.Seafood-Hielo</option>
                            <option value="Cuenta Fiesta BCP">Cuenta Fiesta BCP</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_pago" class="form-label">Fecha de Pago:</label>
                        <input type="date" name="fecha_pago" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notas" class="form-label">Notas:</label>
                        <textarea name="notas" class="form-input"></textarea>
                    </div>
                    
                    <button type="submit" name="registrar_credito">Guardar Crédito</button>
                </form>
            </div>
        </div>

     

<!-- Colocar este script al final del body, ANTES de cerrar el body -->
<script>
// Funciones mejoradas para modales
function abrirModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Bloquear scroll
}

function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaurar scroll
}

// Control de clics fuera del modal (versión unificada)
document.addEventListener('click', (e) => {
    // Cerrar todos los modales al hacer clic fuera
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar notificaciones al hacer clic fuera
    if (!e.target.closest('.notificacion-bell') && 
        !e.target.closest('.notificaciones-dropdown')) {
        document.querySelector('.notificaciones-dropdown').classList.remove('show');
    }
});

// Controlador único para teclado
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        document.querySelector('.notificaciones-dropdown').classList.remove('show');
    }
});
</script>

<!-- Añadir este estilo para asegurar la visualización -->
<style>

</style>
    </div>
</body>
</html>
<?php $conn->close(); ?>