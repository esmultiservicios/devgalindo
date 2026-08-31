<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responderAtencionHistorica($status, $title, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $status === 'success' ? 'success' : 'error'
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmt = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];
    $atencion_id = isset($_POST['atencion_id']) ? (int) $_POST['atencion_id'] : 0;

    if ($atencion_id <= 0) {
        throw new Exception('No se recibió una atención válida.');
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        'SELECT atencion_id,
                antecedentes_medicos_no_psiquiatricos,
                hospitalizaciones,
                cirugias,
                alergias,
                antecedentes_medicos_psiquiatricos,
                historia_gineco_obstetrica,
                medicamentos_previos,
                medicamentos_actuales,
                legal,
                sustancias,
                rasgos_personalidad,
                informacion_adicional,
                pendientes,
                diagnostico,
                seguimiento
         FROM atenciones_medicas
         WHERE atencion_id = ? AND colaborador_id = ?
         LIMIT 1'
    );
    if (!$stmt) {
        throw new Exception('No se pudo preparar la consulta de la atención histórica: ' . $mysqli->error);
    }

    $stmt->bind_param('ii', $atencion_id, $colaborador_id);
    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar la atención histórica: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();
    if ($resultado->num_rows !== 1) {
        responderAtencionHistorica('error', 'Atención no encontrada', 'La atención seleccionada no existe o no pertenece al usuario actual.', array('campos' => array()));
    }

    $fila = $resultado->fetch_assoc();

    $campos = array(
        'antecedentes_medicos_no_psiquiatricos' => (string) $fila['antecedentes_medicos_no_psiquiatricos'],
        'hospitalizaciones' => (string) $fila['hospitalizaciones'],
        'cirugias' => (string) $fila['cirugias'],
        'alergias' => (string) $fila['alergias'],
        'antecedentes_medicos_psiquiatricos' => (string) $fila['antecedentes_medicos_psiquiatricos'],
        'historia_gineco_obstetrica' => (string) $fila['historia_gineco_obstetrica'],
        'medicamentos_previos' => (string) $fila['medicamentos_previos'],
        'medicamentos_actuales' => (string) $fila['medicamentos_actuales'],
        'legal' => (string) $fila['legal'],
        'sustancias' => (string) $fila['sustancias'],
        'rasgos_personalidad' => (string) $fila['rasgos_personalidad'],
        'informacion_adicional' => (string) $fila['informacion_adicional'],
        'pendientes' => (string) $fila['pendientes'],
        'diagnostico' => (string) $fila['diagnostico'],
        'seguimiento' => (string) $fila['seguimiento']
    );

    responderAtencionHistorica('success', 'Atención cargada', 'Se obtuvo correctamente la atención histórica.', array(
        'atencion_id' => (int) $fila['atencion_id'],
        'campos' => $campos
    ));
} catch (Throwable $e) {
    error_log('Error al obtener atención histórica: ' . $e->getMessage());
    responderAtencionHistorica('error', 'Error al cargar atención', $e->getMessage(), array('campos' => array()));
} finally {
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
