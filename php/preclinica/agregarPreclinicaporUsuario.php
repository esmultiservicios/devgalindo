<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responder($titulo, $mensaje, $tipo)
{
    echo json_encode(array(
        0 => $titulo,
        1 => $mensaje,
        2 => $tipo,
        3 => $tipo === 'success' ? 'btn-primary' : 'btn-danger',
        4 => $tipo === 'success' ? 'formulario_agregar_preclinica' : '',
        5 => $tipo === 'success' ? 'Registro' : '',
        6 => $tipo === 'success' ? 'Preclinica' : '',
        7 => $tipo === 'success' ? 'agregar_preclinica' : '',
        8 => '',
        9 => $tipo === 'success' ? 'Guardar' : ''
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmtAgenda = null;
$stmtPaciente = null;
$stmtTipoPaciente = null;
$stmtExiste = null;
$stmtInsert = null;
$stmtHistorialPre = null;
$stmtUpdateAgenda = null;
$stmtHistorialAgenda = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    $agenda_id = isset($_POST['id-registro']) ? (int)$_POST['id-registro'] : 0;
    $expediente = isset($_POST['expediente']) ? (int)$_POST['expediente'] : 0;
    $fecha = trim((string)($_POST['fecha'] ?? ''));
    $pa = trim((string)($_POST['pa'] ?? ''));
    $fr = trim((string)($_POST['fr'] ?? ''));
    $fc = trim((string)($_POST['fc'] ?? ''));
    $temperatura = trim((string)($_POST['temperatura'] ?? ''));
    $peso = trim((string)($_POST['peso'] ?? ''));
    $talla = trim((string)($_POST['talla'] ?? ''));
    $observaciones = trim((string)($_POST['observaciones'] ?? ''));
    $usuario = (int)$_SESSION['colaborador_id'];
    $fecha_registro = date('Y-m-d H:i:s');

    if ($agenda_id <= 0 || $expediente <= 0) {
        throw new Exception('La agenda o el expediente no son válidos.');
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new Exception('La fecha no es válida.');
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo establecer conexión con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmtAgenda = $mysqli->prepare(
        "SELECT a.servicio_id, a.pacientes_id, a.colaborador_id, c.puesto_id
         FROM agenda AS a
         INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
         WHERE a.agenda_id = ?
         LIMIT 1"
    );
    if (!$stmtAgenda) throw new Exception('No se pudo preparar la consulta de agenda: ' . $mysqli->error);
    $stmtAgenda->bind_param('i', $agenda_id);
    if (!$stmtAgenda->execute()) throw new Exception('No se pudo consultar la agenda: ' . $stmtAgenda->error);
    $agendaResult = $stmtAgenda->get_result();

    if ($agendaResult->num_rows !== 1) throw new Exception('La agenda seleccionada no existe.');

    $agenda = $agendaResult->fetch_assoc();
    $servicio = (int)$agenda['servicio_id'];
    $pacientes_id = (int)$agenda['pacientes_id'];
    $medico = (int)$agenda['colaborador_id'];

    $stmtPaciente = $mysqli->prepare(
        "SELECT fecha_nacimiento
         FROM pacientes
         WHERE pacientes_id = ? AND expediente = ?
         LIMIT 1"
    );
    if (!$stmtPaciente) throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    $stmtPaciente->bind_param('ii', $pacientes_id, $expediente);
    if (!$stmtPaciente->execute()) throw new Exception('No se pudo consultar el paciente: ' . $stmtPaciente->error);
    $pacienteResult = $stmtPaciente->get_result();

    if ($pacienteResult->num_rows !== 1) throw new Exception('El paciente no coincide con la agenda.');

    $fecha_nacimiento = (string)$pacienteResult->fetch_assoc()['fecha_nacimiento'];
    $edad = getEdad($fecha_nacimiento);
    $anos = isset($edad['anos']) ? (int)$edad['anos'] : 0;

    $stmtTipoPaciente = $mysqli->prepare(
        "SELECT agenda_id FROM agenda
         WHERE pacientes_id = ? AND servicio_id = ?
         LIMIT 1"
    );
    if (!$stmtTipoPaciente) throw new Exception('No se pudo preparar la validación del paciente: ' . $mysqli->error);
    $stmtTipoPaciente->bind_param('ii', $pacientes_id, $servicio);
    if (!$stmtTipoPaciente->execute()) throw new Exception('No se pudo validar el tipo de paciente: ' . $stmtTipoPaciente->error);
    $paciente = $stmtTipoPaciente->get_result()->num_rows > 0 ? 'S' : 'N';

    $stmtExiste = $mysqli->prepare(
        "SELECT preclinica_id
         FROM preclinica
         WHERE expediente = ? AND fecha = ? AND servicio_id = ? AND colaborador_id = ?
         LIMIT 1"
    );
    if (!$stmtExiste) throw new Exception('No se pudo preparar la validación de preclínica: ' . $mysqli->error);
    $stmtExiste->bind_param('isii', $expediente, $fecha, $servicio, $medico);
    if (!$stmtExiste->execute()) throw new Exception('No se pudo validar la preclínica: ' . $stmtExiste->error);

    if ($stmtExiste->get_result()->num_rows > 0) {
        responder('Error', 'Lo sentimos, este registro ya existe y no puede almacenarse nuevamente.', 'error');
    }

    $numero = (int)correlativo('preclinica_id', 'preclinica');

    $stmtInsert = $mysqli->prepare(
        "INSERT INTO preclinica (
            preclinica_id, pacientes_id, expediente, colaborador_id, edad,
            fecha, pa, fr, fc, t, peso, talla, servicio_id, observacion,
            usuario, paciente, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtInsert) throw new Exception('No se pudo preparar el registro de preclínica: ' . $mysqli->error);
    $stmtInsert->bind_param(
        'iiiiissssssssisss',
        $numero, $pacientes_id, $expediente, $medico, $anos, $fecha,
        $pa, $fr, $fc, $temperatura, $peso, $talla, $servicio,
        $observaciones, $usuario, $paciente, $fecha_registro
    );
    if (!$stmtInsert->execute()) throw new Exception('No se pudo almacenar la preclínica: ' . $stmtInsert->error);

    $historial_numero = (int)historial();
    $modulo = 'Preclinica';
    $status = 'Agregar';
    $observacionHistorial = 'Se realizó la preclínica para este usuario';

    $stmtHistorialPre = $mysqli->prepare(
        "INSERT INTO historial (
            historial_id, pacientes_id, expediente, modulo, codigo,
            colaborador_id, servicio_id, fecha, status, observacion,
            usuario, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtHistorialPre) throw new Exception('No se pudo preparar el historial de preclínica: ' . $mysqli->error);
    $stmtHistorialPre->bind_param(
        'iiisiiisssis',
        $historial_numero, $pacientes_id, $expediente, $modulo, $numero,
        $medico, $servicio, $fecha, $status, $observacionHistorial,
        $usuario, $fecha_registro
    );
    if (!$stmtHistorialPre->execute()) throw new Exception('No se pudo guardar el historial de preclínica: ' . $stmtHistorialPre->error);

    $stmtUpdateAgenda = $mysqli->prepare(
        "UPDATE agenda
         SET preclinica = 1
         WHERE agenda_id = ? AND CAST(fecha_cita AS DATE) = ?"
    );
    if (!$stmtUpdateAgenda) throw new Exception('No se pudo preparar la actualización de agenda: ' . $mysqli->error);
    $stmtUpdateAgenda->bind_param('is', $agenda_id, $fecha);
    if (!$stmtUpdateAgenda->execute()) throw new Exception('No se pudo actualizar la agenda: ' . $stmtUpdateAgenda->error);

    $historial_numero = (int)historial();
    $modulo = 'Agenda';
    $status = 'Actualizar';
    $observacionHistorial = 'Se actualiza el campo preclínica en la entidad agenda, desde preclínica';

    $stmtHistorialAgenda = $mysqli->prepare(
        "INSERT INTO historial (
            historial_id, pacientes_id, expediente, modulo, codigo,
            colaborador_id, servicio_id, fecha, status, observacion,
            usuario, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtHistorialAgenda) throw new Exception('No se pudo preparar el historial de agenda: ' . $mysqli->error);
    $stmtHistorialAgenda->bind_param(
        'iiisiiisssis',
        $historial_numero, $pacientes_id, $expediente, $modulo, $numero,
        $medico, $servicio, $fecha, $status, $observacionHistorial,
        $usuario, $fecha_registro
    );
    if (!$stmtHistorialAgenda->execute()) throw new Exception('No se pudo guardar el historial de agenda: ' . $stmtHistorialAgenda->error);

    responder('Almacenado', 'Registro almacenado correctamente.', 'success');
} catch (Throwable $e) {
    error_log('Error agregarPreclinicaporUsuario.php: ' . $e->getMessage());
    responder('Error', $e->getMessage(), 'error');
} finally {
    foreach (array(
        $stmtAgenda, $stmtPaciente, $stmtTipoPaciente, $stmtExiste,
        $stmtInsert, $stmtHistorialPre, $stmtUpdateAgenda, $stmtHistorialAgenda
    ) as $stmt) {
        if ($stmt instanceof mysqli_stmt) $stmt->close();
    }
    if ($mysqli instanceof mysqli) $mysqli->close();
}
