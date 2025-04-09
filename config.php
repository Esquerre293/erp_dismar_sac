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
