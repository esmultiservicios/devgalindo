<?php
session_start();
include "../funtions.php";

header('Content-Type: text/plain; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];
    session_write_close();

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT CONCAT(nombre, ' ', apellido) AS nombre
         FROM colaboradores
         WHERE colaborador_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception('No se pudo preparar la consulta del profesional: ' . $mysqli->error);
    }

    $stmt->bind_param('i', $colaborador_id);

    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar el profesional: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();

    echo $resultado->num_rows === 1
        ? (string) $resultado->fetch_assoc()['nombre']
        : '';
} catch (Throwable $e) {
    error_log('Error getProfeisonal.php: ' . $e->getMessage());
    echo '';
} finally {
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
