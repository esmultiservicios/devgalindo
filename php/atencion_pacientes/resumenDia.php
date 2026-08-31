<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responderResumen($status, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'message' => $message
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function fechaResumenValida($fecha)
{
    $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
    $errores = DateTime::getLastErrors();

    return $objeto !== false
        && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
        && $objeto->format('Y-m-d') === $fecha;
}

$mysqli = null;
$stmt = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];
    $pacientes_id = isset($_POST['pacientes_id']) ? (int) $_POST['pacientes_id'] : 0;
    $fecha = isset($_POST['fecha']) ? trim((string) $_POST['fecha']) : '';

    if ($pacientes_id <= 0 || !fechaResumenValida($fecha)) {
        responderResumen('success', '', array('total' => 0, 'atenciones' => array()));
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        'SELECT atencion_id, fecha, fecha_registro, paciente, servicio_id, estado,
                diagnostico, seguimiento, agenda_id
         FROM atenciones_medicas
         WHERE pacientes_id = ? AND colaborador_id = ? AND fecha = ?
         ORDER BY fecha_registro DESC, atencion_id DESC'
    );
    if (!$stmt) {
        throw new Exception('No se pudo preparar el resumen de atenciones: ' . $mysqli->error);
    }

    $stmt->bind_param('iis', $pacientes_id, $colaborador_id, $fecha);
    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar el resumen de atenciones: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();
    $atenciones = array();

    while ($fila = $resultado->fetch_assoc()) {
        $hora = '';
        if (!empty($fila['fecha_registro'])) {
            $timestamp = strtotime($fila['fecha_registro']);
            if ($timestamp !== false) {
                $hora = date('h:i A', $timestamp);
            }
        }

        $atenciones[] = array(
            'atencion_id' => (int) $fila['atencion_id'],
            'fecha' => $fila['fecha'],
            'hora' => $hora,
            'tipo' => $fila['paciente'] === 'N' ? 'Nuevo' : 'Subsiguiente',
            'servicio_id' => (int) $fila['servicio_id'],
            'estado' => (int) $fila['estado'],
            'estado_texto' => (int) $fila['estado'] === 1 ? 'Pendiente' : 'Pagada',
            'diagnostico' => trim((string) $fila['diagnostico']),
            'seguimiento' => trim((string) $fila['seguimiento']),
            'agenda_id' => isset($fila['agenda_id']) ? (int) $fila['agenda_id'] : 0,
            'puede_eliminar' => (int) $fila['estado'] === 1
        );
    }

    responderResumen('success', '', array(
        'total' => count($atenciones),
        'atenciones' => $atenciones
    ));
} catch (Throwable $e) {
    error_log('Error al cargar resumen diario de atenciones: ' . $e->getMessage());
    responderResumen('error', $e->getMessage(), array('total' => 0, 'atenciones' => array()));
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
