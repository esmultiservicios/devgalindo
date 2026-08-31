<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responderEliminar($status, $title, $message)
{
    echo json_encode(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $status === 'success' ? 'success' : 'error'
    ), JSON_UNESCAPED_UNICODE);
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
        'SELECT a.pacientes_id, a.servicio_id, a.fecha, a.estado, a.agenda_id,
                p.expediente, p.nombre, p.apellido, p.identidad
         FROM atenciones_medicas a
         INNER JOIN pacientes p ON p.pacientes_id = a.pacientes_id
         WHERE a.atencion_id = ? AND a.colaborador_id = ?
         LIMIT 1'
    );
    if (!$stmt) throw new Exception('No se pudo preparar la consulta de la atención: ' . $mysqli->error);
    $stmt->bind_param('ii', $atencion_id, $colaborador_id);
    if (!$stmt->execute()) throw new Exception('No se pudo consultar la atención: ' . $stmt->error);
    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        responderEliminar('error', 'Atención no encontrada', 'La atención no existe o no pertenece al usuario actual.');
    }

    $atencion = $resultado->fetch_assoc();
    $stmt->close();
    $stmt = null;

    if ((int) $atencion['estado'] !== 1) {
        responderEliminar('error', 'No se puede eliminar', 'La atención ya fue procesada y no puede eliminarse desde este historial.');
    }

    $agenda_id = isset($atencion['agenda_id']) ? (int) $atencion['agenda_id'] : 0;

    $stmt = $mysqli->prepare('DELETE FROM atenciones_medicas WHERE atencion_id = ? AND colaborador_id = ? AND estado = 1');
    if (!$stmt) throw new Exception('No se pudo preparar la eliminación de la atención: ' . $mysqli->error);
    $stmt->bind_param('ii', $atencion_id, $colaborador_id);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('No se pudo eliminar la atención seleccionada.');
    }
    $stmt->close();
    $stmt = null;

    if ($agenda_id > 0) {
        $stmt = $mysqli->prepare('SELECT observacion FROM agenda WHERE agenda_id = ? AND colaborador_id = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('ii', $agenda_id, $colaborador_id);
            if ($stmt->execute()) {
                $resultadoAgenda = $stmt->get_result();
                if ($resultadoAgenda->num_rows === 1) {
                    $agenda = $resultadoAgenda->fetch_assoc();
                    $esManual = trim((string) $agenda['observacion']) === 'Usuario agregado de forma manual';
                    $stmt->close();
                    $stmt = null;

                    if ($esManual) {
                        $stmt = $mysqli->prepare('DELETE FROM agenda WHERE agenda_id = ? AND colaborador_id = ?');
                        if ($stmt) {
                            $stmt->bind_param('ii', $agenda_id, $colaborador_id);
                            $stmt->execute();
                            $stmt->close();
                            $stmt = null;
                        }
                    } else {
                        $stmt = $mysqli->prepare('UPDATE agenda SET status = 0 WHERE agenda_id = ? AND colaborador_id = ?');
                        if ($stmt) {
                            $stmt->bind_param('ii', $agenda_id, $colaborador_id);
                            $stmt->execute();
                            $stmt->close();
                            $stmt = null;
                        }
                    }
                }
            }
        }
    }

    $historial_id = (int) historial();
    if ($historial_id > 0) {
        $pacientes_id = (int) $atencion['pacientes_id'];
        $expediente = (int) $atencion['expediente'];
        $servicio_id = (int) $atencion['servicio_id'];
        $fecha = (string) $atencion['fecha'];
        $modulo = 'Atención Pacientes';
        $statusHistorial = 'Eliminar';
        $nombrePaciente = trim($atencion['nombre'] . ' ' . $atencion['apellido']);
        $observacionHistorial = 'Se eliminó la atención n° ' . $atencion_id . ' del paciente ' . $nombrePaciente .
            ' con identidad n° ' . (string) $atencion['identidad'];
        $fecha_registro = date('Y-m-d H:i:s');

        $stmt = $mysqli->prepare(
            'INSERT INTO historial (
                historial_id, pacientes_id, expediente, modulo, codigo,
                colaborador_id, servicio_id, fecha, status, observacion, usuario, fecha_registro
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        if ($stmt) {
            $stmt->bind_param(
                'iiisiiisssis',
                $historial_id, $pacientes_id, $expediente, $modulo, $atencion_id,
                $colaborador_id, $servicio_id, $fecha, $statusHistorial,
                $observacionHistorial, $colaborador_id, $fecha_registro
            );
            $stmt->execute();
        }
    }

    responderEliminar('success', 'Atención eliminada', 'La atención seleccionada fue eliminada correctamente.');
} catch (Throwable $e) {
    error_log('Error al eliminar atención médica: ' . $e->getMessage());
    responderEliminar('error', 'Error al eliminar', $e->getMessage());
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
