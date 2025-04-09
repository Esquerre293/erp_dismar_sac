<?php
// Configuración de la base de datos
$host = '127.0.0.1';  // Dirección del servidor
$port = 3309;         // Puerto de MySQL
$user = 'root';       // Usuario de la base de datos
$pass = '';           // Contraseña (vacía en XAMPP por defecto)
$db = 'dismar_sac';   // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para agregar un nuevo empleado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_empleado'])) {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];
    $fecha_contratacion = $_POST['fecha_contratacion'];
    $rol = $_POST['rol'];

    // Validar y procesar la firma
    if ($_FILES['firma']['error'] == 0) {
        $firma_tipo = $_FILES['firma']['type'];
        if (strpos($firma_tipo, 'image/') !== false) {  // Validar que sea una imagen
            $firma = $_FILES['firma']['tmp_name'];
            $firma_imagen = file_get_contents($firma);  // Obtener el contenido de la imagen

            // Consulta SQL para insertar (sin incluir el id)
            $stmt = $conn->prepare("INSERT INTO empleados (nombres, apellidos, dni, correo, firma, fecha_contratacion, rol)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");

            // Usar 'b' para la firma (binario) y 's' para las cadenas
            $stmt->bind_param("sssssss", $nombres, $apellidos, $dni, $correo, $firma_imagen, $fecha_contratacion, $rol);

            if ($stmt->execute()) {
                echo "";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: El archivo debe ser una imagen.";
        }
    } else {
        echo "Error: Hubo un problema con la subida de la firma.";
    }
}

// Lógica para eliminar un empleado
if (isset($_GET['eliminar'])) {
    $id_empleado = $_GET['eliminar'];
    $sql_eliminar = "DELETE FROM empleados WHERE id = ?";
    $stmt_eliminar = $conn->prepare($sql_eliminar);
    $stmt_eliminar->bind_param("i", $id_empleado);
    
    if ($stmt_eliminar->execute()) {
        echo "";
    } else {
        echo "Error al eliminar el empleado: " . $stmt_eliminar->error;
    }
    $stmt_eliminar->close();
}

// Consulta para obtener los empleados
$sql = "SELECT * FROM empleados";
$result = $conn->query($sql);
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
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1A202C;
        }

        .btn-ice {
            background: rgb(41, 50, 70);
            color: white;
        }

        .btn-ice:hover {
            background: #4299E1;
            transform: translateY(-1px);
        }

        .btn-beach {
            background: rgb(41, 50, 70);
            color: white;
        }

        .btn-beach:hover {
            background: #ED8936;
            transform: translateY(-1px);
        }

        /* Añade esto en tu sección de estilos */
        a.btn-ice, 
        a.btn-beach {
            text-decoration: none !important;
            cursor: pointer; /* Opcional: mantiene el cursor de pointer */
        }

        /* Para estados hover y active */
        a.btn-ice:hover, 
        a.btn-ice:active,
        a.btn-beach:hover,
        a.btn-beach:active
         {
            text-decoration: none !important;
        }
    </style>

<?php 
// Obtener valores de búsqueda y filtro
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$roleFilter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : "";

// Construir consulta SQL con filtros dinámicos
$sql = "SELECT * FROM empleados WHERE 1";

if (!empty($search)) {
    $sql .= " AND (nombres LIKE '%$search%' OR apellidos LIKE '%$search%' OR dni LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $sql .= " AND rol = '$roleFilter'";
}

// Ejecutar consulta
$result = $conn->query($sql);
?>


</head>
<body>

<div class="container">
    <!-- Buscador y Filtro -->
    <div class="header">
        <form method="GET" action="" class="search-bar">
            <input type="text" name="search" placeholder="Buscar empleado..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="role">
                <option value="">Todos los roles</option>
                <option value="Administrador" <?php if ($roleFilter == "Administrador") echo "selected"; ?>>Administrador</option>
                <option value="Sistemas" <?php if ($roleFilter == "Sistemas") echo "selected"; ?>>Sistemas</option>
                <option value="Obrero" <?php if ($roleFilter == "Obrero") echo "selected"; ?>>Obrero</option>
                <option value="Limpieza" <?php if ($roleFilter == "Limpieza") echo "selected"; ?>>Limpieza</option>
                <option value="Cocina" <?php if ($roleFilter == "Cocina") echo "selected"; ?>>Cocina</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
        <div style="display: flex; gap: 0.5rem;">
            <a href="area_hielo.php" class="btn <?php echo (basename($_SERVER['PHP_SELF']) == 'area_hielo.php') ? 'btn-ice active' : 'btn-ice'; ?>">Área de Hielo</a>
            <a href="area_playa.php" class="btn <?php echo (basename($_SERVER['PHP_SELF']) == 'area_playa.php') ? 'btn-beach active' : 'btn-beach'; ?>">Área de Playa</a>
            <button class="btn btn-primary" id="btnAgregar">+ Nuevo Trabajador</button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="table-container">
        <?php
        // Tabla original (empleados.php)
        if(basename($_SERVER['PHP_SELF']) == 'empleados.php'){
            include 'tabla_empleados.php';
        }
        ?>
    </div>
</div>
    

    
    <!-- Modal -->

    
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-card">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <input type="text" class="form-input" placeholder="Nombres" name="nombres" required>
                        <input type="text" class="form-input" placeholder="Apellidos" name="apellidos" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <input type="text" class="form-input" name="dni" required>
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
                        <option value="Sistemas">Sistemas</option>
                        <option value="Obrero">Obrero</option>
                        <option value="Limpieza">Limpieza</option>
                        <option value="Cocina">Cocina</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Firma Digital</label>
                    <div class="file-input">
                        <input type="file" name="firma" accept="image/*" required>
                        <span style="color: #718096;">Arrastra o selecciona archivo</span>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="agregar_empleado">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
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
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["nombres"]) ?> <?= htmlspecialchars($row["apellidos"]) ?></td>
                            <td><?= htmlspecialchars($row["dni"]) ?></td>
                            <td><?= htmlspecialchars($row["correo"]) ?></td>
                            <td><img class="firma-img" src="data:image/jpeg;base64,<?= base64_encode($row["firma"]) ?>"></td>
                            <td><?= date('d/m/Y', strtotime($row["fecha_contratacion"])) ?></td>
                            <td><?= htmlspecialchars($row["rol"]) ?></td>
                            <td><a href="?eliminar=<?= $row["id"] ?>" class="btn btn-danger">Eliminar</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #718096;">
                            No se encontraron empleados registrados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>

        
const modal = document.getElementById('modalOverlay');
        const openBtn = document.getElementById('btnAgregar');
        
        openBtn.addEventListener('click', () => modal.style.display = 'flex');
        window.addEventListener('click', (e) => e.target === modal && (modal.style.display = 'none'));
        
        function closeModal() {
            modal.style.display = 'none';
        }
document.addEventListener("DOMContentLoaded", function() {
    const busquedaInput = document.getElementById("busqueda");
    const filtroRol = document.getElementById("filtroRol");
    const filtroFecha = document.getElementById("filtroFecha");
    const tabla = document.getElementById("tablaEmpleados");

    function filtrarEmpleados() {
        const textoBusqueda = busquedaInput.value.toLowerCase();
        const rolSeleccionado = filtroRol.value.toLowerCase();
        const fechaSeleccionada = filtroFecha.value;

        Array.from(tabla.getElementsByTagName("tr")).forEach(row => {
            const nombres = row.dataset.nombres.toLowerCase();
            const apellidos = row.dataset.apellidos.toLowerCase();
            const dni = row.dataset.dni.toLowerCase();
            const rol = row.dataset.rol.toLowerCase();
            const fecha = row.dataset.fecha;

            const coincideBusqueda = nombres.includes(textoBusqueda) || apellidos.includes(textoBusqueda) || dni.includes(textoBusqueda);
            const coincideRol = !rolSeleccionado || rol === rolSeleccionado;
            const coincideFecha = !fechaSeleccionada || fecha === fechaSeleccionada;

            row.style.display = (coincideBusqueda && coincideRol && coincideFecha) ? "" : "none";
        });
    };

});

    busquedaInput.addEventListener("input", filtrarEmpleados);
    filtroRol.addEventListener("change", filtrarEmpleados);
    filtroFecha.addEventListener("change", filtrarEmpleados);
    // Para carga sin recargar la página
document.querySelectorAll('.btn-ice, .btn-beach').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const area = btn.classList.contains('btn-ice') ? 'hielo' : 'playa';
        
        // Cargar contenido
        const response = await fetch(`${btn.href}&ajax=1`);
        const data = await response.text();
        
        // Actualizar tabla
        document.querySelector('.table-container').innerHTML = data;
        
        // Actualizar clase activa
        document.querySelectorAll('.btn-ice, .btn-beach').forEach(b => 
            b.classList.remove('btn-active')
        );
        btn.classList.add('btn-active');
    });
});    // Control del Modal



    </script>
</body>
</html>
<?php
// Cerrar conexión al final del archivo
if (isset($conn)) {
    $conn->close();
}
?>