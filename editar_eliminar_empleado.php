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

// Modificar empleado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_empleado'])) {
    $id = $_POST['id'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];
    $fecha_contratacion = $_POST['fecha_contratacion'];
    $rol = $_POST['rol'];

    // Verificar si se sube una nueva firma
    if ($_FILES['firma']['tmp_name'] != "") {
        $firma = $_FILES['firma']['tmp_name'];
        $firma_imagen = addslashes(file_get_contents($firma));
        $sql = "UPDATE empleados SET nombres='$nombres', apellidos='$apellidos', dni='$dni', correo='$correo', firma='$firma_imagen', fecha_contratacion='$fecha_contratacion', rol='$rol' WHERE id=$id";
    } else {
        $sql = "UPDATE empleados SET nombres='$nombres', apellidos='$apellidos', dni='$dni', correo='$correo', fecha_contratacion='$fecha_contratacion', rol='$rol' WHERE id=$id";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Empleado modificado exitosamente";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Eliminar empleado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_empleado'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM empleados WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Empleado eliminado exitosamente";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
