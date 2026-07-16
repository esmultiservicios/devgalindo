<?php
session_start();
include "../funtions.php";

header('Content-Type: text/html; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida.');
    }

    session_write_close();

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT religion_id, nombre
         FROM religion
         ORDER BY nombre ASC"
    );

    if (!$stmt) {
        throw new Exception('No se pudo preparar la consulta de Religión: ' . $mysqli->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar Religión: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();

    echo '<option value="">Seleccione</option>';

    if ($resultado->num_rows === 0) {
        echo '<option value="">No hay registros</option>';
    } else {
        while ($registro = $resultado->fetch_assoc()) {
            $id = (int) $registro['religion_id'];
            $nombre = htmlspecialchars(
                (string) $registro['nombre'],
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            echo '<option value="' . $id . '">' . $nombre . '</option>';
        }
    }
} catch (Throwable $e) {
    error_log('Error getReligion.php: ' . $e->getMessage());
    echo '<option value="">No se pudo cargar Religión</option>';
} finally {
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
