<?php
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

// Obtener parámetros del formulario
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$rol = isset($_GET['rol']) ? $conn->real_escape_string($_GET['rol']) : '';

// Construir consulta SQL segura con prepared statements
$sql = "SELECT * FROM area_hielo WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (nombres LIKE CONCAT('%', ?, '%') 
                  OR apellidos LIKE CONCAT('%', ?, '%') 
                  OR dni LIKE CONCAT('%', ?, '%'))";
    $types .= 'sss';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($rol)) {
    $sql .= " AND rol = ?";
    $types .= 's';
    $params[] = $rol;
}

// Preparar y ejecutar consulta principal
$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        die("Error en la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
} else {
    die("Error en la preparación de la consulta: " . $conn->error);
}

// Lógica para agregar nuevo trabajador
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_hielo'])) {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];
    $fecha_contratacion = $_POST['fecha_contratacion'];
    $rol = $_POST['rol'];

    // Validar DNI
    if (strlen($dni) != 8 || !is_numeric($dni)) {
        die("<div class='alert alert-error'>Error: DNI debe tener 8 dígitos numéricos</div>");
    }

    // Procesar firma
    if ($_FILES['firma']['error'] == 0) {
        $firma_tipo = $_FILES['firma']['type'];
        if (strpos($firma_tipo, 'image/') !== false) {
            $firma_imagen = file_get_contents($_FILES['firma']['tmp_name']);

            $insert_stmt = $conn->prepare("INSERT INTO area_hielo 
                (nombres, apellidos, dni, correo, firma, fecha_contratacion, rol)
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $insert_stmt->bind_param("sssssss", $nombres, $apellidos, $dni, $correo, 
                                   $firma_imagen, $fecha_contratacion, $rol);

            if ($insert_stmt->execute()) {
                echo "<div class='alert alert-success'>Trabajador agregado exitosamente</div>";
                // Recargar los resultados después de insertar
                header("Refresh:2");
            } else {
                echo "<div class='alert alert-error'>Error: " . $insert_stmt->error . "</div>";
            }
            $insert_stmt->close();
        } else {
            echo "<div class='alert alert-error'>Error: El archivo debe ser una imagen</div>";
        }
    } else {
        echo "<div class='alert alert-error'>Error: Problema con la subida de la firma</div>";
    }
}

// Lógica para eliminar trabajador
if (isset($_GET['eliminar_hielo'])) {
    $id = $_GET['eliminar_hielo'];
    
    $delete_stmt = $conn->prepare("DELETE FROM area_hielo WHERE id = ?");
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success'>Trabajador eliminado correctamente</div>";
        // Recargar los resultados después de eliminar
        header("Refresh:2");
    } else {
        echo "<div class='alert alert-error'>Error al eliminar: " . $delete_stmt->error . "</div>";
    }
    $delete_stmt->close();
}

// Cerrar conexión principal
$stmt->close();
$conn->close();
?>

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

    /* Botones - Todos iguales */
.btn {
    padding: 0.75rem 1.5rem; /* Tamaño base */
    min-width: 120px; /* Ancho mínimo igual para todos */
    text-align: center;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center; /* Centrado horizontal */
    gap: 0.5rem;
    text-decoration: none !important;
    height: 40px; /* Altura fija */
}

/* Botón Eliminar */
.btn-danger {
    padding: 0.75rem 1.5rem !important; /* Mismo padding que otros */
    min-width: 120px;
    height: 40px;
}

/* Botones en formulario */
.form-actions .btn {
    flex: 1; /* Ocupan igual espacio */
    min-width: auto; /* Ancho flexible */
}

/* Responsive */
@media (max-width: 768px) {
    .btn {
        width: 100%; /* Full width en móviles */
        min-width: auto;
        padding: 0.75rem !important;
    }
    
    .header .btn {
        width: auto; /* Botones en header mantienen tamaño */
    }
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

    /* Alertas */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
    }

    .alert-success {
        background-color: rgba(72, 187, 120, 0.1);
        color: var(--success);
        border: 1px solid rgba(72, 187, 120, 0.3);
    }

    .alert-error {
        background-color: rgba(245, 101, 101, 0.1);
        color: var(--error);
        border: 1px solid rgba(245, 101, 101, 0.3);
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

    h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--primary);
    }

    /* Stats Card */
    .stat-card {
        background: var(--background);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        background: var(--accent);
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        opacity: 0.9;
    }

    /* Botones */
    .btn {
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
        text-decoration: none !important;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 2px 4px rgba(45, 55, 72, 0.1);
        text-decoration: none !important;
    }

    .btn-primary:hover {
        background: #1A202C;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(45, 55, 72, 0.15);
        text-decoration: none !important;
    }

    /* Tabla */
    .table-container {
        background: var(--background);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #E2E8F0;
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

    .firma-img {
        width: 80px;
        height: 45px;
        object-fit: contain;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        background: white;
        padding: 4px;
    }

    .btn-danger {
        color: var(--error);
        background: transparent;
        border: 1px solid var(--error);
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
         text-decoration: none;
    }

    .btn-danger:hover {
        background: rgba(245, 101, 101, 0.1);
    }

    /* Modal */
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
        backdrop-filter: blur(2px);
    }

    .modal-card {
        background: var(--background);
        width: 90%;
        max-width: 450px;
        border-radius: 12px;
        padding: 2rem;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        animation: modalFadeIn 0.3s ease-out;
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

    /* Formulario */
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

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        body {
            padding: 1.25rem;
        }

        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .modal-card {
            padding: 1.5rem;
        }

        table {
            display: block;
            overflow-x: auto;
        }
    }

    /* Utilidades */
    .text-muted {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }
</style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    
</head>
<body>
    <?php
    // Verificar consulta y conexión
    if ($result === false) {
        echo "<p class='error'>Error al cargar los empleados: " . $conn->error . "</p>";
    }
    ?>

<style>
        :root {
            --primary: #2D3748;
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
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--surface);
            color: var(--text-primary);
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ENCABEZADO */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--background);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header .search-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .search-bar input, .search-bar select {
            padding: 0.75rem;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            font-size: 0.9rem;
            width: 100%;
            max-width: 250px;
        }

        .btn {
           
            border-radius: 6px;
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary {
            padding: 1rem 1.5rem;
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1A202C;
        }

    </style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div class="header">
    <form method="GET" class="search-bar">
        <input type="text" 
               name="search" 
               placeholder="Buscar por nombre, apellido o DNI"
               value="<?= htmlspecialchars($search) ?>"
               class="form-input">
        
        <select name="rol" class="form-input">
            <option value="">Todos los roles</option>
            <option value="Operador" <?= $rol == 'Operador' ? 'selected' : '' ?>>Operador</option>
            <option value="Mantenimiento" <?= $rol == 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
            <option value="Despacho de Hielo" <?= $rol == 'Despacho de Hielo' ? 'selected' : '' ?>>Despacho</option>
            <option value="Vigilantes" <?= $rol == 'Vigilantes' ? 'selected' : '' ?>>Vigilantes</option>
            <option value="Choferes" <?= $rol == 'Choferes' ? 'selected' : '' ?>>Choferes</option>
        </select>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>
    
    <div class="header-actions">
        <a href="subir_empleado.php" class="btn btn-primary">Volver</a>
        <button class="btn btn-primary" id="btnAgregarHielo">+ Nuevo Trabajador</button>
    </div>
</div>

        <!-- En el HTML, dentro del container -->
<div class="modal-overlay" id="modalHielo">
    <div class="modal-card">
        <form method="post" enctype="multipart/form-data" id="formHielo">
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <input type="text" class="form-input" placeholder="Nombres" name="nombres" required>
                    <input type="text" class="form-input" placeholder="Apellidos" name="apellidos" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">DNI</label>
                <input type="text" class="form-input" name="dni" pattern="\d{8}" title="8 dígitos numéricos" required>
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" class="form-input" name="correo" required>
            </div>

            <div class="form-group">
                <label class="form-label">Fecha de Contratación</label>
                <input type="date" class="form-input" name="fecha_contratacion" required>
            </div>

            <div class="form-group">
                <label class="form-label">Rol</label>
                <select class="form-input" name="rol" required>
                    <option value="Operador">Operador</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Despacho de Hielo">Despacho de Hielo</option>
                    <option value="Vigilantes">Vigilantes</option>
                    <option value="Choferes">Choferes</option>
                </select>
            </div>

            <div class="form-group">
                    <label class="form-label">Firma Digital</label>
                    <div class="file-input">
                        <input type="file" name="firma" accept="image/*" required>
                        <span style="color: #718096;">Arrastra o selecciona archivo</span>
                    </div>
                </div>

            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary" name="agregar_hielo">Guardar</button>
            </div>
        </form>
    </div>
</div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Firma</th>
                        <th>Contratación</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["nombres"] . " " . $row["apellidos"]) ?></td>
                                <td><?= htmlspecialchars($row["dni"]) ?></td>
                                <td><?= htmlspecialchars($row["correo"]) ?></td>
                                <td><img class="firma-img" src="data:image/jpeg;base64,<?= base64_encode($row["firma"]) ?>"></td>
                                <td><?= date('d/m/Y', strtotime($row["fecha_contratacion"])) ?></td>
                                <td><?= htmlspecialchars($row["rol"]) ?></td>
                                <td>
                                    <a href="?eliminar_hielo=<?= $row["id"] ?>" class="btn btn-danger">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No hay trabajadores registrados en esta área</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('btnAgregarHielo').addEventListener('click', () => {
            document.getElementById('modalHielo').style.display = 'flex';
        });

// JavaScript para el modal
const modal = document.getElementById('modalHielo');
const openBtn = document.getElementById('btnAgregarHielo');
const form = document.getElementById('formHielo');

// Abrir modal
openBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
});

// Cerrar al hacer clic fuera
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// Evitar cierre al hacer clic en el formulario
form.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Mostrar nombre de archivo
document.getElementById('firmaInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || '';
    document.getElementById('fileName').textContent = fileName;
});

function closeModal() {
    modal.style.display = 'none';
}

    </script>
</body>
</html>
