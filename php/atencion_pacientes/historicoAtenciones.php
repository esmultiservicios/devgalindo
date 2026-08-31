<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responderHistorico($status, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'message' => $message
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function formatoFechaHistorico($fecha)
{
    if (empty($fecha)) {
        return '';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : (string) $fecha;
}

function formatoHoraHistorico($fechaRegistro)
{
    if (empty($fechaRegistro)) {
        return '';
    }

    $timestamp = strtotime($fechaRegistro);
    return $timestamp ? date('h:i A', $timestamp) : '';
}

function construirDetalleHistorico($fila)
{
    $partes = array();

    $diagnostico = trim((string) ($fila['diagnostico'] ?? ''));
    $seguimiento = trim((string) ($fila['seguimiento'] ?? ''));
    $pendientes = trim((string) ($fila['pendientes'] ?? ''));

    if ($diagnostico !== '') {
        $partes[] = 'Diagnóstico: ' . $diagnostico;
    }

    if ($seguimiento !== '') {
        $partes[] = 'Seguimiento: ' . $seguimiento;
    }

    if ($pendientes !== '') {
        $partes[] = 'Pendientes: ' . $pendientes;
    }

    if (empty($partes)) {
        return 'Sin detalle clínico disponible.';
    }

    return implode("\n", $partes);
}

$mysqli = null;
$stmt = null;
$stmtFechas = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];
    $pacientes_id = isset($_POST['pacientes_id']) ? (int) $_POST['pacientes_id'] : 0;
    $modo = isset($_POST['modo']) ? trim((string) $_POST['modo']) : 'todas';
    $fecha = isset($_POST['fecha']) ? trim((string) $_POST['fecha']) : '';

    if ($modo !== 'dia') {
        $modo = 'todas';
    }

    if ($pacientes_id <= 0) {
        responderHistorico('success', '', array(
            'ultima_atencion' => null,
            'atenciones' => array(),
            'fechas_disponibles' => array()
        ));
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }
    $mysqli->set_charset('utf8mb4');

    $stmtFechas = $mysqli->prepare(
        'SELECT fecha, COUNT(*) AS total
         FROM atenciones_medicas
         WHERE pacientes_id = ? AND colaborador_id = ?
         GROUP BY fecha
         ORDER BY fecha DESC
         LIMIT 3'
    );
    if (!$stmtFechas) {
        throw new Exception('No se pudieron preparar las fechas recientes del histórico: ' . $mysqli->error);
    }

    $stmtFechas->bind_param('ii', $pacientes_id, $colaborador_id);
    if (!$stmtFechas->execute()) {
        throw new Exception('No se pudieron consultar las fechas recientes del histórico: ' . $stmtFechas->error);
    }

    $resultadoFechas = $stmtFechas->get_result();
    $fechasDisponibles = array();

    while ($filaFecha = $resultadoFechas->fetch_assoc()) {
        $fechasDisponibles[] = array(
            'fecha' => (string) $filaFecha['fecha'],
            'fecha_visual' => formatoFechaHistorico($filaFecha['fecha']),
            'total' => (int) $filaFecha['total']
        );
    }

    $stmtFechas->close();
    $stmtFechas = null;

    if ($modo === 'dia' && $fecha !== '') {
        $stmt = $mysqli->prepare(
            'SELECT atencion_id, fecha, fecha_registro, paciente, estado,
                    diagnostico, seguimiento, pendientes
             FROM atenciones_medicas
             WHERE pacientes_id = ? AND colaborador_id = ? AND fecha = ?
             ORDER BY fecha_registro DESC, atencion_id DESC
             LIMIT 50'
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar el histórico de atenciones del día: ' . $mysqli->error);
        }
        $stmt->bind_param('iis', $pacientes_id, $colaborador_id, $fecha);
    } else {
        $stmt = $mysqli->prepare(
            'SELECT atencion_id, fecha, fecha_registro, paciente, estado,
                    diagnostico, seguimiento, pendientes
             FROM atenciones_medicas
             WHERE pacientes_id = ? AND colaborador_id = ?
             ORDER BY fecha DESC, fecha_registro DESC, atencion_id DESC
             LIMIT 50'
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar el histórico de atenciones: ' . $mysqli->error);
        }
        $stmt->bind_param('ii', $pacientes_id, $colaborador_id);
    }
    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar el histórico de atenciones: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();
    $atenciones = array();

    while ($fila = $resultado->fetch_assoc()) {
        $atenciones[] = array(
            'atencion_id' => (int) $fila['atencion_id'],
            'fecha' => formatoFechaHistorico($fila['fecha']),
            'hora' => formatoHoraHistorico($fila['fecha_registro']),
            'tipo' => ((string) $fila['paciente'] === 'N') ? 'Nuevo' : 'Subsiguiente',
            'estado' => (int) $fila['estado'],
            'estado_texto' => ((int) $fila['estado'] === 1) ? 'Pendiente' : 'Pagada',
            'detalle' => construirDetalleHistorico($fila)
        );
    }

    responderHistorico('success', '', array(
        'ultima_atencion' => !empty($atenciones) ? $atenciones[0] : null,
        'atenciones' => $atenciones,
        'fechas_disponibles' => $fechasDisponibles
    ));
} catch (Throwable $e) {
    error_log('Error al consultar histórico reciente de atenciones: ' . $e->getMessage());
    responderHistorico('error', $e->getMessage(), array(
        'ultima_atencion' => null,
        'atenciones' => array(),
        'fechas_disponibles' => array()
    ));
} finally {
    if ($stmtFechas instanceof mysqli_stmt) {
        $stmtFechas->close();
    }
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
