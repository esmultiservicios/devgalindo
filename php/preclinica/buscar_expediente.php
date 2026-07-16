<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    $valor = trim((string)($_POST['expediente'] ?? ''));

    if ($valor === '') {
        echo json_encode(array('Error', '', ''), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT expediente, identidad, CONCAT(nombre, ' ', apellido) AS nombre
         FROM pacientes
         WHERE (CAST(expediente AS CHAR) = ? OR identidad = ?)
           AND estado = 1
         LIMIT 1"
    );
    if (!$stmt) throw new Exception($mysqli->error);
    $stmt->bind_param('ss', $valor, $valor);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        echo json_encode(array('Error', '', ''), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $paciente = $resultado->fetch_assoc();

    if ((int)$paciente['expediente'] === 0) {
        echo json_encode(array('Error1', '', ''), JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(array(
        (string)$paciente['identidad'],
        (string)$paciente['nombre']
    ), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error buscar_expediente.php: ' . $e->getMessage());
    echo json_encode(array('Error', '', ''), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
