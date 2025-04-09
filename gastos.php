<?php
session_start();

// Configuración de la base de datos
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Insertar datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = trim($_POST["categoria"]);
    $detalle = trim($_POST["detalle"]);
    $monto = floatval($_POST["monto"]);
    $metodo_pago = strtolower(trim($_POST["metodo_pago"])); // Aseguramos minúsculas

    $sql = "INSERT INTO gastos (categoria, detalle, monto, metodo_pago) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssds", $categoria, $detalle, $monto, $metodo_pago);
        
        if ($stmt->execute()) {
            header("Location: gastos.php");
            exit();
        } else {
            echo "<div class='alert alert-error'>Error al registrar gasto: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-error'>Error en la preparación de la consulta.</div>";
    }
}

$resultado = $conn->query("SELECT * FROM gastos ORDER BY fecha DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Gestión de Gastos</title>
    <style>
        :root {
            --primary: #2D3748;
            --secondary: #4A5568;
            --accent: #4299E1;
            --background: #FFFFFF;
            --surface: #F7FAFC;
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

        /* Estilos del Modal */
        .modal-overlay {
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
        }

        .modal {
            background: var(--background);
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Estilos para los métodos de pago */
.metodo-pago {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
}

/* Colores específicos - Clases exactas en minúsculas */
.metodo-pago.transferencia {
    background: #4299E1 !important; /* Azul */
    color: white !important;
}

.metodo-pago.yape {
    background: #805AD5 !important; /* Morado */
    color: white !important;
}

.metodo-pago.plin {
    background: #48BB78 !important; /* Verde */
    color: white !important;
}

.metodo-pago.efectivo {
    background: #F6E05E !important; /* Amarillo */
    color: #1A202C !important;
}

        
.btn-group {
    display: flex;
    gap: 8px; /* Espacio entre botones */
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    display: flex;
    align-items: center;
}

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .table-container {
            background: var(--background);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
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
            color: var(--secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .btn-pdf {
        background: #dc3545 !important;
        color: white !important;
        margin-left: 10px;
    }

    .btn-excel {
        background: #28a745 !important;
        color: white !important;
        margin-left: 10px;
    }

    .btn i {
    margin-right: 8px;
    font-size: 1.1em;
}

.btn:hover {
    filter: brightness(90%);
    transform: translateY(-1px);
}

    </style>
</head>
<body>
    <div class="container">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="btn-group">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Nuevo Gasto
        </button>
        <button class="btn btn-excel" onclick="exportToExcel()">
            <i class="fas fa-file-excel"></i> Excel
        </button>
        <button class="btn btn-pdf" onclick="exportToPDF()">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
    </div>
</header>

        <!-- Modal -->
        <div class="modal-overlay" id="modalOverlay">
            <div class="modal">
                <form action="gastos.php" method="POST">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="font-size: 1.25rem;">Registrar Nuevo Gasto</h2>
                        <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">&times;</button>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">Categoría:</label>
                        <input type="text" name="categoria" required style="width: 100%; padding: 0.75rem; border: 1px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">Detalle:</label>
                        <input type="text" name="detalle" required style="width: 100%; padding: 0.75rem; border: 1px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">Monto:</label>
                        <input type="number" name="monto" step="0.01" required style="width: 100%; padding: 0.75rem; border: 1px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">Método de Pago:</label>
                        <select name="metodo_pago" required style="width: 100%; padding: 0.75rem; border: 1px solid #E2E8F0; border-radius: 8px;">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" onclick="closeModal()" style="background: #718096; color: white;" class="btn">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Detalle</th>
                        <th>Monto</th>
                        <th>Método de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $fila["fecha"] ?></td>
                            <td><?= $fila["categoria"] ?></td>
                            <td><?= $fila["detalle"] ?></td>
                            <td>S/<?= number_format($fila["monto"], 2) ?></td>
                            <td>
    <span class="metodo-pago <?= strtolower($fila["metodo_pago"]) ?>">
        <?= strtoupper($fila["metodo_pago"]) ?>
    </span>
</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalOverlay').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if(e.target === this) closeModal();
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') closeModal();
        });

        function exportToExcel() {
        window.location.href = 'export_excel.php';
    }

    function exportToPDF() {
        window.location.href = 'export_pdf.php';
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>