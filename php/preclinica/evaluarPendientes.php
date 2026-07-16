<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    $fecha = date('Y-m-d');
    $primerDia = date('Y-m-01', strtotime($fecha));
    $ayer = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
    $mes_actual = nombremes(date('m', strtotime($fecha)));

    $total = 0;

    if ($fecha !== $primerDia) {
        $mysqli = connect_mysqli();
        if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
        $mysqli->set_charset('utf8mb4');

        $stmt = $mysqli->prepare(
            "SELECT COUNT(a.pacientes_id) AS total
             FROM agenda AS a
             INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
             WHERE CAST(a.fecha_cita AS DATE) BETWEEN ? AND ?
               AND a.preclinica = 0
               AND c.puesto_id = 2"
        );
        if (!$stmt) throw new Exception($mysqli->error);
        $stmt->bind_param('ss', $primerDia, $ayer);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        $fila = $stmt->get_result()->fetch_assoc();
        $total = (int)($fila['total'] ?? 0);
    }

    echo json_encode(array($total, $mes_actual), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error evaluarPendientes.php: ' . $e->getMessage());
    echo json_encode(array(0, ''), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
