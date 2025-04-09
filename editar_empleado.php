<?php
// Conexión a la base de datos
$conn = new mysqli('127.0.0.1', 'root', '', 'dismar_sac', 3309);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM empleados WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $empleado = $result->fetch_assoc();
    } else {
        die("Empleado no encontrado");
    }
} else {
    die("ID no proporcionado");
}
?>

<form action="editar_eliminar_empleado.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $empleado['id']; ?>">
    <label for="nombres">Nombres:</label>
    <input type="text" name="nombres" value="<?php echo $empleado['nombres']; ?>" required><br>

    <label for="apellidos">Apellidos:</label>
    <input type="text" name="apellidos" value="<?php echo $empleado['apellidos']; ?>" required><br>

    <label for="dni">DNI:</label>
    <input type="text" name="dni" value="<?php echo $empleado['dni']; ?>" required><br>

    <label for="correo">Correo:</label>
    <input type="email" name="correo" value="<?php echo $empleado['correo']; ?>" required><br>

    <label for="fecha_contratacion">Fecha de Contratación:</label>
    <input type="date" name="fecha_contratacion" value="<?php echo $empleado['fecha_contratacion']; ?>" required><br>

    <label for="rol">Rol:</label>
    <input type="text" name="rol" value="<?php echo $empleado['rol']; ?>" required><br>

    <label for="firma">Firma (opcional):</label>
    <input type="file" name="firma"><br>

    <button type="submit" name="editar_empleado">Guardar cambios</button>
</form>
