<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) throw new Exception('El identificador de agenda no es válido.');

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT
            p.expediente,
            CONCAT(p.nombre, ' ', p.apellido) AS paciente,
            p.identidad,
            CONCAT(c.nombre, ' ', c.apellido) AS profesional,
            CAST(a.fecha_cita AS DATE) AS fecha_cita
         FROM agenda AS a
         INNER JOIN pacientes AS p ON a.pacientes_id = p.pacientes_id
         INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
         WHERE a.agenda_id = ?
         LIMIT 1"
    );
    if (!$stmt) throw new Exception($mysqli->error);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $resultado = $stmt->get_result();
    if ($resultado->num_rows !== 1) throw new Exception('La agenda solicitada no existe.');

    $datos = $resultado->fetch_assoc();

    echo json_encode(array(
        (string)$datos['paciente'],
        (string)$datos['identidad'],
        (int)$datos['expediente'],
        (string)$datos['profesional'],
        (string)$datos['fecha_cita']
    ), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(array('error' => true, 'message' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
