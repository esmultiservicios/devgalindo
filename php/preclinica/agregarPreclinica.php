<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responder($titulo, $mensaje, $tipo, $extra = array())
{
    echo json_encode(array_merge(array(
        0 => $titulo,
        1 => $mensaje,
        2 => $tipo,
        3 => $tipo === 'success' ? 'btn-primary' : 'btn-danger',
        4 => $tipo === 'success' ? 'formulario_agregar_preclinica' : '',
        5 => $tipo === 'success' ? 'Registro' : '',
        6 => $tipo === 'success' ? 'Preclinica' : '',
        7 => $tipo === 'success' ? 'agregar_preclinica' : ''
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmtPaciente = null;
$stmtTipoPaciente = null;
$stmtAgenda = null;
$stmtExiste = null;
$stmtInsertPreclinica = null;
$stmtInsertAgenda = null;
$stmtInsertHistorial = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    $expediente_valor = trim((string)($_POST['expediente'] ?? ''));
    $fecha = trim((string)($_POST['fecha'] ?? ''));
    $pa = trim((string)($_POST['pa'] ?? ''));
    $fr = trim((string)($_POST['fr'] ?? ''));
    $fc = trim((string)($_POST['fc'] ?? ''));
    $temperatura = trim((string)($_POST['temperatura'] ?? ''));
    $peso = trim((string)($_POST['peso'] ?? ''));
    $talla = trim((string)($_POST['talla'] ?? ''));
    $observaciones = trim((string)($_POST['observaciones'] ?? ''));
    $servicio = isset($_POST['servicio']) && $_POST['servicio'] !== '' ? (int)$_POST['servicio'] : 0;
    $medico = isset($_POST['medico']) && $_POST['medico'] !== '' ? (int)$_POST['medico'] : 0;
    $usuario = (int)$_SESSION['colaborador_id'];
    $fecha_registro = date('Y-m-d H:i:s');

    if ($expediente_valor === '') {
        throw new Exception('El expediente o identidad es obligatorio.');
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new Exception('La fecha no es válida.');
    }

    if ($servicio <= 0 || $medico <= 0) {
        throw new Exception('El servicio y el profesional no pueden quedar en blanco.');
    }

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }
    $mysqli->set_charset('utf8mb4');

    $stmtPaciente = $mysqli->prepare(
        "SELECT pacientes_id, expediente, fecha_nacimiento
         FROM pacientes
         WHERE (CAST(expediente AS CHAR) = ? OR identidad = ?)
           AND estado = 1
         LIMIT 1"
    );
    if (!$stmtPaciente) throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    $stmtPaciente->bind_param('ss', $expediente_valor, $expediente_valor);
    if (!$stmtPaciente->execute()) throw new Exception('No se pudo consultar el paciente: ' . $stmtPaciente->error);
    $pacienteResult = $stmtPaciente->get_result();

    if ($pacienteResult->num_rows !== 1) {
        throw new Exception('El paciente no existe o se encuentra inactivo.');
    }

    $pacienteDatos = $pacienteResult->fetch_assoc();
    $pacientes_id = (int)$pacienteDatos['pacientes_id'];
    $expediente = (int)$pacienteDatos['expediente'];
    $fecha_nacimiento = (string)$pacienteDatos['fecha_nacimiento'];

    if ($expediente === 0) {
        throw new Exception('Este es un expediente temporal y no se puede registrar la preclínica.');
    }

    $edad = getEdad($fecha_nacimiento);
    $anos = isset($edad['anos']) ? (int)$edad['anos'] : 0;

    $stmtTipoPaciente = $mysqli->prepare(
        "SELECT agenda_id
         FROM agenda
         WHERE pacientes_id = ? AND servicio_id = ?
         LIMIT 1"
    );
    if (!$stmtTipoPaciente) throw new Exception('No se pudo preparar la validación del paciente: ' . $mysqli->error);
    $stmtTipoPaciente->bind_param('ii', $pacientes_id, $servicio);
    if (!$stmtTipoPaciente->execute()) throw new Exception('No se pudo validar el tipo de paciente: ' . $stmtTipoPaciente->error);
    $tipoResult = $stmtTipoPaciente->get_result();
    $paciente = $tipoResult->num_rows > 0 ? 'S' : 'N';

    $stmtAgenda = $mysqli->prepare(
        "SELECT a.agenda_id
         FROM agenda AS a
         WHERE a.pacientes_id = ?
           AND CAST(a.fecha_cita AS DATE) = ?
           AND a.colaborador_id = ?
           AND a.servicio_id = ?
         LIMIT 1"
    );
    if (!$stmtAgenda) throw new Exception('No se pudo preparar la validación de agenda: ' . $mysqli->error);
    $stmtAgenda->bind_param('isii', $pacientes_id, $fecha, $medico, $servicio);
    if (!$stmtAgenda->execute()) throw new Exception('No se pudo validar la agenda: ' . $stmtAgenda->error);
    $agendaResult = $stmtAgenda->get_result();

    $stmtExiste = $mysqli->prepare(
        "SELECT preclinica_id
         FROM preclinica
         WHERE expediente = ?
           AND fecha = ?
           AND servicio_id = ?
           AND colaborador_id = ?
         LIMIT 1"
    );
    if (!$stmtExiste) throw new Exception('No se pudo preparar la validación de preclínica: ' . $mysqli->error);
    $stmtExiste->bind_param('isii', $expediente, $fecha, $servicio, $medico);
    if (!$stmtExiste->execute()) throw new Exception('No se pudo validar la preclínica: ' . $stmtExiste->error);
    $existeResult = $stmtExiste->get_result();

    if ($existeResult->num_rows > 0) {
        responder('Error', 'Lo sentimos, este registro ya existe y no puede almacenarse nuevamente.', 'error');
    }

    $numero = (int)correlativo('preclinica_id', 'preclinica');

    $stmtInsertPreclinica = $mysqli->prepare(
        "INSERT INTO preclinica (
            preclinica_id, pacientes_id, expediente, colaborador_id, edad,
            fecha, pa, fr, fc, t, peso, talla, servicio_id, observacion,
            usuario, paciente, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtInsertPreclinica) throw new Exception('No se pudo preparar el registro de preclínica: ' . $mysqli->error);
    $stmtInsertPreclinica->bind_param(
        'iiiiissssssssisss',
        $numero, $pacientes_id, $expediente, $medico, $anos, $fecha,
        $pa, $fr, $fc, $temperatura, $peso, $talla, $servicio,
        $observaciones, $usuario, $paciente, $fecha_registro
    );
    if (!$stmtInsertPreclinica->execute()) {
        throw new Exception('No se pudo almacenar la preclínica: ' . $stmtInsertPreclinica->error);
    }

    if ($agendaResult->num_rows === 0) {
        $numero_agenda = (int)correlativo('agenda_id', 'agenda');
        $fecha_cita = $fecha . ' 00:00:00';
        $fecha_cita_end = $fecha . ' 00:00:00';
        $hora = '00:00';
        $status = 0;
        $color = '#DF0101';
        $observacionAgenda = 'Se registró fuera de admisión';
        $comentario = 'Hecho en preclínica';
        $preclinica = 1;
        $postclinica = 0;
        $reprogramo = 2;
        $status_id = 0;

        $stmtInsertAgenda = $mysqli->prepare(
            "INSERT INTO agenda (
                agenda_id, pacientes_id, expediente, colaborador_id, hora,
                fecha_cita, fecha_cita_end, fecha_registro, status, color,
                observacion, usuario, servicio_id, comentario, preclinica,
                postclinica, reprogramo, paciente, status_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmtInsertAgenda) throw new Exception('No se pudo preparar la agenda: ' . $mysqli->error);
        $stmtInsertAgenda->bind_param(
            'iiiissssissiiiiiisi',
            $numero_agenda, $pacientes_id, $expediente, $medico, $hora,
            $fecha_cita, $fecha_cita_end, $fecha_registro, $status, $color,
            $observacionAgenda, $usuario, $servicio, $comentario, $preclinica,
            $postclinica, $reprogramo, $paciente, $status_id
        );
        if (!$stmtInsertAgenda->execute()) {
            throw new Exception('La preclínica se guardó, pero no se pudo crear la agenda: ' . $stmtInsertAgenda->error);
        }
    }

    $historial_numero = (int)historial();
    $modulo = 'Agenda';
    $estado = 'Actualizar';
    $observacionHistorial = 'Se actualiza el campo preclínica en la entidad agenda, desde preclínica';

    $stmtInsertHistorial = $mysqli->prepare(
        "INSERT INTO historial (
            historial_id, pacientes_id, expediente, modulo, codigo,
            colaborador_id, servicio_id, fecha, status, observacion,
            usuario, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtInsertHistorial) throw new Exception('No se pudo preparar el historial: ' . $mysqli->error);
    $stmtInsertHistorial->bind_param(
        'iiisiiisssis',
        $historial_numero, $pacientes_id, $expediente, $modulo, $numero,
        $medico, $servicio, $fecha, $estado, $observacionHistorial,
        $usuario, $fecha_registro
    );
    if (!$stmtInsertHistorial->execute()) {
        throw new Exception('La preclínica se guardó, pero no se pudo registrar el historial: ' . $stmtInsertHistorial->error);
    }

    responder('Almacenado', 'Registro almacenado correctamente.', 'success');
} catch (Throwable $e) {
    error_log('Error agregarPreclinica.php: ' . $e->getMessage());
    responder('Error', $e->getMessage(), 'error');
} finally {
    foreach (array(
        $stmtPaciente, $stmtTipoPaciente, $stmtAgenda, $stmtExiste,
        $stmtInsertPreclinica, $stmtInsertAgenda, $stmtInsertHistorial
    ) as $stmt) {
        if ($stmt instanceof mysqli_stmt) $stmt->close();
    }
    if ($mysqli instanceof mysqli) $mysqli->close();
}