<?php
session_start();
include "../funtions.php";

header('Content-Type: text/html; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT servicio_id, nombre
         FROM servicios
         ORDER BY nombre"
    );
    if (!$stmt) throw new Exception($mysqli->error);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    echo '<option value="">Seleccione</option>';

    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        echo '<option value="' . (int)$fila['servicio_id'] . '">' .
             htmlspecialchars((string)$fila['nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
             '</option>';
    }
} catch (Throwable $e) {
    error_log('Error servicios.php: ' . $e->getMessage());
    echo '<option value="">No se pudo cargar Servicios</option>';
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}