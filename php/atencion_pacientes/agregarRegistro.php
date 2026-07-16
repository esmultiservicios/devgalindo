<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responder($status, $title, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $status === 'success' ? 'success' : ($status === 'warning' ? 'warning' : 'error')
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function textoPost($nombre)
{
    return isset($_POST[$nombre]) ? trim((string) $_POST[$nombre]) : '';
}

function enteroPost($nombre, $predeterminado = 0)
{
    if (!isset($_POST[$nombre]) || $_POST[$nombre] === '') {
        return $predeterminado;
    }

    $valor = filter_var($_POST[$nombre], FILTER_VALIDATE_INT);

    if ($valor === false) {
        throw new Exception("El campo {$nombre} no contiene un número válido.");
    }

    return (int) $valor;
}

function fechaValida($fecha)
{
    $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
    $errores = DateTime::getLastErrors();

    return $objeto !== false
        && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
        && $objeto->format('Y-m-d') === $fecha;
}


$mysqli = null;
$stmt = null;
$atencionCreada = false;
$agendaActualizada = false;
$atencion_id = 0;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];
    $agenda_id = enteroPost('agenda_id');
    $pacientes_id = enteroPost('pacientes_id');
    $fecha = textoPost('fecha');

    if ($agenda_id <= 0) throw new Exception('No se recibió una agenda válida.');
    if ($pacientes_id <= 0) throw new Exception('Debe seleccionar un paciente.');
    if (!fechaValida($fecha)) throw new Exception('La fecha de la atención no es válida.');

    $fecha_nac = textoPost('fecha_nac');
    if (!fechaValida($fecha_nac)) throw new Exception('La fecha de nacimiento no es válida.');

    $telefono1 = textoPost('telefono1');
    if ($telefono1 === '') throw new Exception('El teléfono 1 es obligatorio.');

    $identidad = textoPost('identidad');
    $localidad = textoPost('procedencia');
    $red_apoyo = textoPost('red_apoyo');
    $terapeuta_actual = textoPost('terapeuta_actual');
    $num_hijos = enteroPost('num_hijos');

    $religion = textoPost('religion_id');
    $profesion = textoPost('profesion_id');
    if ($profesion === '') $profesion = textoPost('profesion');
    $estado_civil = textoPost('estado_civil');
    $escolaridad = textoPost('escolaridad');

    $antecedentes_medicos_no_psiquiatricos = textoPost('antecedentes_medicos_no_psiquiatricos');
    $hospitalizaciones = textoPost('hospitalizaciones');
    $cirugias = textoPost('cirugias');
    $alergias = textoPost('alergias');
    $antecedentes_medicos_psiquiatricos = textoPost('antecedentes_medicos_psiquiatricos');
    $historia_gineco_obstetrica = textoPost('historia_gineco_obstetrica');
    $medicamentos_previos = textoPost('medicamentos_previos');
    $medicamentos_actuales = textoPost('medicamentos_actuales');
    $legal = textoPost('legal');
    $sustancias = textoPost('sustancias');
    $rasgos_personalidad = textoPost('rasgos_personalidad');
    $informacion_adicional = textoPost('informacion_adicional');
    $pendientes = textoPost('pendientes');
    $diagnostico = textoPost('diagnostico');
    $seguimiento = textoPost('seguimiento');

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo establecer conexión con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        'SELECT servicio_id, expediente, status
         FROM agenda
         WHERE agenda_id = ? AND pacientes_id = ? AND colaborador_id = ?
         LIMIT 1'
    );
    if (!$stmt) throw new Exception('No se pudo preparar la consulta de agenda: ' . $mysqli->error);
    $stmt->bind_param('iii', $agenda_id, $pacientes_id, $colaborador_id);
    if (!$stmt->execute()) throw new Exception('No se pudo consultar la agenda: ' . $stmt->error);
    $resultadoAgenda = $stmt->get_result();
    if ($resultadoAgenda->num_rows !== 1) throw new Exception('La agenda seleccionada no existe o no le pertenece al usuario actual.');
    $agenda = $resultadoAgenda->fetch_assoc();
    $servicio_id = (int) $agenda['servicio_id'];
    $expediente = (int) $agenda['expediente'];
    if ((int) $agenda['status'] !== 0) throw new Exception('La agenda ya fue atendida o ya no está pendiente.');
    $stmt->close();
    $stmt = null;

    $stmt = $mysqli->prepare(
        'UPDATE pacientes
         SET estado_civil_texto = ?, religion_texto = ?, profesion_texto = ?,
             localidad = ?, escolaridad_texto = ?, red_apoyo = ?, terapeuta_actual = ?,
             telefono1 = ?, identidad = ?, fecha_nacimiento = ?
         WHERE pacientes_id = ?'
    );
    if (!$stmt) throw new Exception('No se pudo preparar la actualización del paciente: ' . $mysqli->error);
    $stmt->bind_param(
        'ssssssssssi',
        $estado_civil, $religion, $profesion, $localidad, $escolaridad,
        $red_apoyo, $terapeuta_actual, $telefono1, $identidad, $fecha_nac, $pacientes_id
    );
    if (!$stmt->execute()) throw new Exception('No se pudo actualizar el paciente: ' . $stmt->error);
    $stmt->close();
    $stmt = null;

    $stmt = $mysqli->prepare(
        'SELECT nombre, apellido, identidad, fecha_nacimiento
         FROM pacientes WHERE pacientes_id = ? LIMIT 1'
    );
    if (!$stmt) throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    $stmt->bind_param('i', $pacientes_id);
    if (!$stmt->execute()) throw new Exception('No se pudo consultar el paciente: ' . $stmt->error);
    $resultadoPaciente = $stmt->get_result();
    if ($resultadoPaciente->num_rows !== 1) throw new Exception('El paciente seleccionado no existe.');
    $paciente = $resultadoPaciente->fetch_assoc();
    $stmt->close();
    $stmt = null;

    $edad = getEdad($paciente['fecha_nacimiento']);
    $anos = (int) ($edad['anos'] ?? 0);
    $nombrePaciente = trim($paciente['nombre'] . ' ' . $paciente['apellido']);
    $identidadPaciente = (string) $paciente['identidad'];

    $stmt = $mysqli->prepare(
        'SELECT atencion_id FROM atenciones_medicas
         WHERE pacientes_id = ? AND fecha = ? AND servicio_id = ? LIMIT 1'
    );
    if (!$stmt) throw new Exception('No se pudo preparar la validación de atención: ' . $mysqli->error);
    $stmt->bind_param('isi', $pacientes_id, $fecha, $servicio_id);
    if (!$stmt->execute()) throw new Exception('No se pudo validar la atención: ' . $stmt->error);
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        responder('warning', 'Registro existente', 'Este paciente ya tiene una atención registrada para esta fecha y consultorio.');
    }
    $stmt->close();
    $stmt = null;

    $stmt = $mysqli->prepare(
        'SELECT atencion_id FROM atenciones_medicas
         WHERE pacientes_id = ? AND colaborador_id = ? AND servicio_id = ? LIMIT 1'
    );
    if (!$stmt) throw new Exception('No se pudo validar el tipo de paciente: ' . $mysqli->error);
    $stmt->bind_param('iii', $pacientes_id, $colaborador_id, $servicio_id);
    if (!$stmt->execute()) throw new Exception('No se pudo validar el tipo de paciente: ' . $stmt->error);
    $stmt->store_result();
    $tipo_paciente = $stmt->num_rows === 0 ? 'N' : 'S';
    $stmt->close();
    $stmt = null;

    $atencion_id = (int) correlativo('atencion_id', 'atenciones_medicas');
    if ($atencion_id <= 0) throw new Exception('No se pudo generar el correlativo de la atención.');

    $estado = 1;
    $fecha_registro = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare(
        'INSERT INTO atenciones_medicas (
            atencion_id, pacientes_id, edad, fecha,
            antecedentes_medicos_no_psiquiatricos, hospitalizaciones, cirugias, alergias,
            antecedentes_medicos_psiquiatricos, historia_gineco_obstetrica,
            medicamentos_previos, medicamentos_actuales, legal, sustancias,
            rasgos_personalidad, informacion_adicional, pendientes, diagnostico, seguimiento,
            paciente, servicio_id, colaborador_id, num_hijos, estado, fecha_registro
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    if (!$stmt) throw new Exception('No se pudo preparar el registro de atención: ' . $mysqli->error);
    $stmt->bind_param(
        'iiisssssssssssssssssiiiis',
        $atencion_id, $pacientes_id, $anos, $fecha,
        $antecedentes_medicos_no_psiquiatricos, $hospitalizaciones, $cirugias, $alergias,
        $antecedentes_medicos_psiquiatricos, $historia_gineco_obstetrica,
        $medicamentos_previos, $medicamentos_actuales, $legal, $sustancias,
        $rasgos_personalidad, $informacion_adicional, $pendientes, $diagnostico, $seguimiento,
        $tipo_paciente, $servicio_id, $colaborador_id, $num_hijos, $estado, $fecha_registro
    );
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('No se pudo registrar la atención: ' . $stmt->error);
    }
    $atencionCreada = true;
    $stmt->close();
    $stmt = null;

    $statusAgenda = 1;
    $stmt = $mysqli->prepare('UPDATE agenda SET status = ? WHERE agenda_id = ? AND status = 0');
    if (!$stmt) throw new Exception('No se pudo preparar la actualización de agenda: ' . $mysqli->error);
    $stmt->bind_param('ii', $statusAgenda, $agenda_id);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('La atención se registró, pero no se pudo actualizar la agenda.');
    }
    $agendaActualizada = true;
    $stmt->close();
    $stmt = null;

    $historial_id = (int) historial();
    if ($historial_id <= 0) throw new Exception('No se pudo generar el correlativo de historial.');

    $modulo = 'Atención Pacientes';
    $statusHistorial = 'Agregar';
    $observacionHistorial = 'Se ha agregado una nueva atención para este paciente: ' .
        $nombrePaciente . ' con identidad n° ' . $identidadPaciente;

    $stmt = $mysqli->prepare(
        'INSERT INTO historial (
            historial_id, pacientes_id, expediente, modulo, codigo,
            colaborador_id, servicio_id, fecha, status, observacion, usuario, fecha_registro
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    if (!$stmt) throw new Exception('No se pudo preparar el historial: ' . $mysqli->error);
    $stmt->bind_param(
        'iiisiiisssis',
        $historial_id, $pacientes_id, $expediente, $modulo, $atencion_id,
        $colaborador_id, $servicio_id, $fecha, $statusHistorial,
        $observacionHistorial, $colaborador_id, $fecha_registro
    );
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('La atención se registró, pero no se pudo guardar su historial: ' . $stmt->error);
    }

    responder('success', 'Atención registrada', 'La atención se registró correctamente.', array(
        'atencion_id' => $atencion_id,
        'agenda_id' => $agenda_id
    ));
} catch (Throwable $e) {
    if ($mysqli instanceof mysqli) {
        if ($agendaActualizada && $agenda_id > 0) {
            $restaurar = $mysqli->prepare('UPDATE agenda SET status = 0 WHERE agenda_id = ?');
            if ($restaurar) {
                $restaurar->bind_param('i', $agenda_id);
                $restaurar->execute();
                $restaurar->close();
            }
        }
        if ($atencionCreada && $atencion_id > 0) {
            $compensar = $mysqli->prepare('DELETE FROM atenciones_medicas WHERE atencion_id = ?');
            if ($compensar) {
                $compensar->bind_param('i', $atencion_id);
                $compensar->execute();
                $compensar->close();
            }
        }
    }

    error_log('Error al registrar atención desde agenda: ' . $e->getMessage());
    responder('error', 'Error al registrar', $e->getMessage());
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}	