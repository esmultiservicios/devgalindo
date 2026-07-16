<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responderEliminarPaciente($status, $title, $message, $type = 'error', $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $type
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmtAgenda = null;
$stmtPaciente = null;
$stmtHistorial = null;
$stmtEliminar = null;
$stmtCompensar = null;
$stmtTipoIdentidad = null;
$historialInsertado = false;
$historial_id = 0;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('El identificador del paciente no es válido.');
    }

    $pacientes_id = (int) $_POST['id'];
    $usuario = (int) $_SESSION['colaborador_id'];

    if ($pacientes_id <= 0 || $usuario <= 0) {
        throw new Exception('El paciente o el usuario de la sesión no son válidos.');
    }

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    $stmtAgenda = $mysqli->prepare(
        'SELECT 1 FROM agenda WHERE pacientes_id = ? LIMIT 1'
    );

    if (!$stmtAgenda) {
        throw new Exception('No se pudo preparar la consulta de agenda: ' . $mysqli->error);
    }

    $stmtAgenda->bind_param('i', $pacientes_id);

    if (!$stmtAgenda->execute()) {
        throw new Exception('No se pudo consultar la agenda del paciente: ' . $stmtAgenda->error);
    }

    $stmtAgenda->store_result();

    if ($stmtAgenda->num_rows > 0) {
        responderEliminarPaciente(
            'warning',
            'No se puede eliminar',
            'El paciente tiene información registrada en agenda y no puede eliminarse.',
            'warning'
        );
    }

    $stmtAgenda->close();
    $stmtAgenda = null;

    $stmtPaciente = $mysqli->prepare(
        'SELECT
            expediente,
            identidad,
            nombre,
            apellido,
            genero,
            telefono1,
            telefono2,
            fecha_nacimiento,
            email,
            fecha,
            departamento_id,
            municipio_id,
            localidad,
            religion_id,
            profesion_id
         FROM pacientes
         WHERE pacientes_id = ?
         LIMIT 1'
    );

    if (!$stmtPaciente) {
        throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    }

    $stmtPaciente->bind_param('i', $pacientes_id);

    if (!$stmtPaciente->execute()) {
        throw new Exception('No se pudo consultar el paciente: ' . $stmtPaciente->error);
    }

    $stmtPaciente->store_result();

    if ($stmtPaciente->num_rows !== 1) {
        throw new Exception('El paciente no existe o ya fue eliminado.');
    }

    $stmtPaciente->bind_result(
        $expediente,
        $identidad,
        $nombre,
        $apellido,
        $genero,
        $telefono1,
        $telefono2,
        $fecha_nacimiento,
        $email,
        $fecha,
        $departamento_id,
        $municipio_id,
        $localidad,
        $religion_id,
        $profesion_id
    );

    if (!$stmtPaciente->fetch()) {
        throw new Exception('No se pudieron obtener los datos del paciente.');
    }

    $stmtPaciente->close();
    $stmtPaciente = null;

    $identidad = trim((string) $identidad);

    if ($identidad === '') {
        $identidad = '0';
    }

    /*
     * Una identidad hondureña de 13 dígitos no cabe en INT. Se consulta el
     * tipo real de la columna para mostrar el motivo exacto únicamente cuando
     * la migración todavía no se ha aplicado.
     */
    $stmtTipoIdentidad = $mysqli->prepare(
        "SELECT DATA_TYPE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'historial_pacientes'
           AND COLUMN_NAME = 'identidad'
         LIMIT 1"
    );

    if (!$stmtTipoIdentidad) {
        throw new Exception('No se pudo validar el tipo de la columna identidad: ' . $mysqli->error);
    }

    if (!$stmtTipoIdentidad->execute()) {
        throw new Exception('No se pudo consultar el tipo de la columna identidad: ' . $stmtTipoIdentidad->error);
    }

    $stmtTipoIdentidad->bind_result($tipoIdentidadHistorial);

    if (!$stmtTipoIdentidad->fetch()) {
        throw new Exception('No se encontró la columna historial_pacientes.identidad.');
    }

    $stmtTipoIdentidad->close();
    $stmtTipoIdentidad = null;

    $tiposEnteros = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint');

    if (
        in_array(strtolower((string) $tipoIdentidadHistorial), $tiposEnteros, true) &&
        (strlen(ltrim($identidad, '-')) > 10 || (ctype_digit($identidad) && (float) $identidad > 2147483647))
    ) {
        throw new Exception(
            'La eliminación no puede continuar porque historial_pacientes.identidad está definido como ' .
            strtoupper((string) $tipoIdentidadHistorial) . ' y la identidad ' . $identidad .
            ' supera ese límite. Ejecute primero el archivo corregir_historial_pacientes.sql.'
        );
    }

    $historial_id = correlativo('historial_id', 'historial_pacientes');

    if (!is_numeric($historial_id) || (int) $historial_id <= 0) {
        throw new Exception('No se pudo generar el correlativo de historial_pacientes.');
    }

    $historial_id = (int) $historial_id;
    $expediente = (int) $expediente;
    $departamento_id = (int) $departamento_id;
    $municipio_id = (int) $municipio_id;
    $religion_id = (int) $religion_id;
    $profesion_id = (int) $profesion_id;
    $estado = 1;
    $observacion = 'Expediente ha sido eliminado correctamente';
    $fecha_registro = date('Y-m-d H:i:s');

    /*
     * IMPORTANTE: en la tabla enviada el campo se llama prefesion_id,
     * no profesion_id. Se usa exactamente el nombre real de la tabla.
     */
    $stmtHistorial = $mysqli->prepare(
        'INSERT INTO historial_pacientes (
            historial_id,
            pacientes_id,
            expediente,
            identidad,
            nombre,
            apellido,
            genero,
            telefono1,
            telefono2,
            fecha_nacimiento,
            email,
            fecha,
            departamento_id,
            municipio_id,
            localidad,
            religion_id,
            prefesion_id,
            usuario,
            estado,
            observacion,
            fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmtHistorial) {
        throw new Exception('No se pudo preparar el historial del paciente: ' . $mysqli->error);
    }

    $stmtHistorial->bind_param(
        'iiisssssssssiisiiiiss',
        $historial_id,
        $pacientes_id,
        $expediente,
        $identidad,
        $nombre,
        $apellido,
        $genero,
        $telefono1,
        $telefono2,
        $fecha_nacimiento,
        $email,
        $fecha,
        $departamento_id,
        $municipio_id,
        $localidad,
        $religion_id,
        $profesion_id,
        $usuario,
        $estado,
        $observacion,
        $fecha_registro
    );

    if (!$stmtHistorial->execute() || $stmtHistorial->affected_rows !== 1) {
        throw new Exception('No se pudo guardar el historial del paciente: ' . $stmtHistorial->error);
    }

    $historialInsertado = true;
    $stmtHistorial->close();
    $stmtHistorial = null;

    $stmtEliminar = $mysqli->prepare(
        'DELETE FROM pacientes WHERE pacientes_id = ?'
    );

    if (!$stmtEliminar) {
        throw new Exception('No se pudo preparar la eliminación del paciente: ' . $mysqli->error);
    }

    $stmtEliminar->bind_param('i', $pacientes_id);

    if (!$stmtEliminar->execute()) {
        throw new Exception('No se pudo eliminar el paciente: ' . $stmtEliminar->error);
    }

    if ($stmtEliminar->affected_rows !== 1) {
        throw new Exception('El paciente no fue eliminado porque el registro ya no existe o fue modificado.');
    }

    responderEliminarPaciente(
        'success',
        'Eliminado',
        'Registro eliminado correctamente.',
        'success'
    );
} catch (Throwable $e) {
    /*
     * historial_pacientes es MyISAM, por lo que no admite transacciones.
     * Si el historial fue insertado y luego falla el DELETE, se elimina el
     * historial recién creado para dejar el flujo consistente.
     */
    if ($historialInsertado && $mysqli instanceof mysqli && $historial_id > 0) {
        $stmtCompensar = $mysqli->prepare(
            'DELETE FROM historial_pacientes WHERE historial_id = ? LIMIT 1'
        );

        if ($stmtCompensar) {
            $stmtCompensar->bind_param('i', $historial_id);
            $stmtCompensar->execute();
        }
    }

    error_log('Error al eliminar paciente: ' . $e->getMessage());
    responderEliminarPaciente('error', 'Error al eliminar', $e->getMessage(), 'error');
} finally {
    foreach (array($stmtAgenda, $stmtPaciente, $stmtHistorial, $stmtEliminar, $stmtCompensar, $stmtTipoIdentidad) as $stmt) {
        if ($stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}