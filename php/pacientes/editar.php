<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responderEditarConsulta($status, $title, $message, $data = array())
{
    echo json_encode(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $status === 'success' ? 'success' : 'error',
        'data' => $data
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmt = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    if (!isset($_POST['pacientes_id']) || trim((string) $_POST['pacientes_id']) === '') {
        throw new Exception('No se recibió el identificador del paciente.');
    }

    $pacientes_id = filter_var($_POST['pacientes_id'], FILTER_VALIDATE_INT);

    if ($pacientes_id === false || $pacientes_id <= 0) {
        throw new Exception('El identificador del paciente no es válido.');
    }

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT
            pacientes_id,
            nombre,
            apellido,
            identidad,
            telefono1,
            telefono2,
            fecha_nacimiento,
            fecha,
            email,
            genero,
            localidad,
            estado,
            expediente,
            departamento_id,
            municipio_id,
            pais_id,
            responsable,
            responsable_id,
            referido_id
         FROM pacientes
         WHERE pacientes_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    }

    $stmt->bind_param('i', $pacientes_id);

    if (!$stmt->execute()) {
        throw new Exception('No se pudo consultar el paciente: ' . $stmt->error);
    }

    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        throw new Exception('El paciente solicitado no existe.');
    }

    $paciente = $resultado->fetch_assoc();

    $fecha_nacimiento = trim((string) ($paciente['fecha_nacimiento'] ?? ''));
    $edad_texto = '';

    if ($fecha_nacimiento !== '' && $fecha_nacimiento !== '0000-00-00') {
        $valores_edad = getEdad($fecha_nacimiento);

        $anos = isset($valores_edad['anos']) ? (int) $valores_edad['anos'] : 0;
        $meses = isset($valores_edad['meses']) ? (int) $valores_edad['meses'] : 0;
        $dias = isset($valores_edad['dias']) ? (int) $valores_edad['dias'] : 0;

        $palabra_anos = $anos === 1 ? 'Año' : 'Años';
        $palabra_meses = $meses === 1 ? 'Mes' : 'Meses';
        $palabra_dias = $dias === 1 ? 'Día' : 'Días';

        $edad_texto = $anos . ' ' . $palabra_anos . ', ' .
                      $meses . ' ' . $palabra_meses . ' y ' .
                      $dias . ' ' . $palabra_dias;
    }

    $expediente_original = isset($paciente['expediente'])
        ? (int) $paciente['expediente']
        : 0;

    responderEditarConsulta(
        'success',
        'Paciente encontrado',
        'La información del paciente se cargó correctamente.',
        array(
            'pacientes_id' => (int) $paciente['pacientes_id'],
            'nombre' => (string) ($paciente['nombre'] ?? ''),
            'apellido' => (string) ($paciente['apellido'] ?? ''),
            'identidad' => (string) ($paciente['identidad'] ?? ''),
            'telefono1' => (string) ($paciente['telefono1'] ?? ''),
            'telefono2' => (string) ($paciente['telefono2'] ?? ''),
            'fecha_nacimiento' => $fecha_nacimiento,
            'fecha' => (string) ($paciente['fecha'] ?? ''),
            'correo' => (string) ($paciente['email'] ?? ''),
            'sexo' => (string) ($paciente['genero'] ?? ''),
            'direccion' => (string) ($paciente['localidad'] ?? ''),
            'estado' => (int) ($paciente['estado'] ?? 0),
            'estado_texto' => ((int) ($paciente['estado'] ?? 0) === 1) ? 'Activo' : 'Inactivo',
            'expediente' => $expediente_original,
            'expediente_texto' => $expediente_original === 0 ? 'TEMP' : (string) $expediente_original,
            'edad' => $edad_texto,
            'departamento_id' => (int) ($paciente['departamento_id'] ?? 0),
            'municipio_id' => (int) ($paciente['municipio_id'] ?? 0),
            'pais_id' => (int) ($paciente['pais_id'] ?? 0),
            'responsable' => (string) ($paciente['responsable'] ?? ''),
            'responsable_id' => (int) ($paciente['responsable_id'] ?? 0),
            'referido_id' => (int) ($paciente['referido_id'] ?? 0)
        )
    );
} catch (Throwable $e) {
    error_log('Error al consultar paciente para edición: ' . $e->getMessage());

    responderEditarConsulta(
        'error',
        'Error',
        $e->getMessage()
    );
} finally {
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}