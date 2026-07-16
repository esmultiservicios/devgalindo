<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responderPaciente($status, $title, $message, $type = 'error', $extra = array())
{
    echo json_encode(array_merge(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'buttonClass' => $type === 'success' ? 'btn-primary' : 'btn-danger',
        'form' => $type === 'success' ? 'formulario_pacientes' : '',
        'process' => $type === 'success' ? 'Registro' : '',
        'function' => $type === 'success' ? 'formPacientes' : '',
        'modal' => $type === 'success' ? 'modal_pacientes' : ''
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function enteroOpcional($valor, $nombreCampo)
{
    if ($valor === null || $valor === '') {
        return 0;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT);

    if ($entero === false || $entero < 0) {
        throw new Exception("El valor de {$nombreCampo} no es válido.");
    }

    return (int) $entero;
}

$mysqli = null;
$stmtIdentidad = null;
$stmtExiste = null;
$stmtInsert = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    $camposObligatorios = array(
        'name' => 'Nombre',
        'lastname' => 'Apellido',
        'identidad' => 'Identidad o RTN',
        'sexo' => 'Sexo',
        'telefono1' => 'Teléfono 1',
        'fecha_nac' => 'Fecha de nacimiento',
        'direccion' => 'Dirección'
    );

    foreach ($camposObligatorios as $campo => $etiqueta) {
        if (!isset($_POST[$campo]) || trim((string) $_POST[$campo]) === '') {
            throw new Exception("El campo {$etiqueta} es obligatorio.");
        }
    }

    $nombre = trim((string) $_POST['name']);
    $apellido = trim((string) $_POST['lastname']);
    $identidad = trim((string) $_POST['identidad']);
    $sexo = trim((string) $_POST['sexo']);
    $telefono1 = trim((string) $_POST['telefono1']);
    $telefono2 = isset($_POST['telefono2']) ? trim((string) $_POST['telefono2']) : '';
    $fecha_nacimiento = trim((string) $_POST['fecha_nac']);
    $correo = isset($_POST['correo']) ? trim((string) $_POST['correo']) : '';
    $localidad = trim((string) $_POST['direccion']);
    $responsable = isset($_POST['responsable']) ? trim((string) $_POST['responsable']) : '';

    if ($correo !== '') {
        $correo = function_exists('mb_strtolower')
            ? mb_strtolower($correo, 'UTF-8')
            : strtolower($correo);
    }

    if (mb_strlen($nombre, 'UTF-8') > 30) {
        throw new Exception('El nombre no puede superar los 30 caracteres.');
    }

    if (mb_strlen($apellido, 'UTF-8') > 30) {
        throw new Exception('El apellido no puede superar los 30 caracteres.');
    }

    if (mb_strlen($identidad, 'UTF-8') > 100) {
        throw new Exception('La identidad o RTN no puede superar los 100 caracteres.');
    }

    if (strlen($telefono1) > 8 || strlen($telefono2) > 8) {
        throw new Exception('Los teléfonos no pueden superar los 8 caracteres.');
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo electrónico ingresado no es válido.');
    }

    $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $erroresFecha = DateTime::getLastErrors();

    if (
        $fechaObjeto === false ||
        ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0)) ||
        $fechaObjeto->format('Y-m-d') !== $fecha_nacimiento
    ) {
        throw new Exception('La fecha de nacimiento no es válida.');
    }

    $pais_id = enteroOpcional($_POST['pais_id'] ?? '', 'país');
    $departamento_id = enteroOpcional($_POST['departamento_id'] ?? '', 'departamento');
    $municipio_id = enteroOpcional($_POST['municipio_id'] ?? '', 'municipio');
    $responsable_id = enteroOpcional($_POST['responsable_id'] ?? '', 'parentesco');
    $referido_id = enteroOpcional($_POST['referido_id'] ?? '', 'referido');

    $usuario = (int) $_SESSION['colaborador_id'];
    $estado = 1;
    $fecha = date('Y-m-d');
    $fecha_registro = date('Y-m-d H:i:s');

    $religion_id = 0;
    $profesion_id = 0;
    $estado_civil = 0;
    $escolaridad = 0;
    $red_apoyo = '';
    $terapeuta_actual = '';
    $religion_texto = '';
    $profesion_texto = '';
    $estado_civil_texto = '';
    $escolaridad_texto = '';

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    if ($identidad === '0') {
        do {
            $identidad = (string) random_int(1, 99999999);

            $stmtIdentidad = $mysqli->prepare(
                'SELECT pacientes_id FROM pacientes WHERE identidad = ? LIMIT 1'
            );

            if (!$stmtIdentidad) {
                throw new Exception('No se pudo preparar la validación de identidad: ' . $mysqli->error);
            }

            $stmtIdentidad->bind_param('s', $identidad);

            if (!$stmtIdentidad->execute()) {
                throw new Exception('No se pudo validar la identidad: ' . $stmtIdentidad->error);
            }

            $stmtIdentidad->store_result();
            $identidadExiste = $stmtIdentidad->num_rows > 0;
            $stmtIdentidad->close();
            $stmtIdentidad = null;
        } while ($identidadExiste);
    }

    $stmtExiste = $mysqli->prepare(
        'SELECT pacientes_id
         FROM pacientes
         WHERE identidad = ? AND nombre = ? AND apellido = ? AND telefono1 = ?
         LIMIT 1'
    );

    if (!$stmtExiste) {
        throw new Exception('No se pudo preparar la validación del paciente: ' . $mysqli->error);
    }

    $stmtExiste->bind_param('ssss', $identidad, $nombre, $apellido, $telefono1);

    if (!$stmtExiste->execute()) {
        throw new Exception('No se pudo validar si el paciente ya existe: ' . $stmtExiste->error);
    }

    $stmtExiste->store_result();

    if ($stmtExiste->num_rows > 0) {
        responderPaciente(
            'warning',
            'Registro existente',
            'Lo sentimos, este paciente ya existe y no puede registrarse nuevamente.',
            'warning'
        );
    }

    $stmtExiste->close();
    $stmtExiste = null;

    $pacientes_id = correlativo('pacientes_id ', 'pacientes');
    $expediente = correlativo('expediente ', 'pacientes');

    if (!is_numeric($pacientes_id) || (int) $pacientes_id <= 0) {
        throw new Exception('No se pudo generar el correlativo del paciente.');
    }

    if (!is_numeric($expediente) || (int) $expediente <= 0) {
        throw new Exception('No se pudo generar el correlativo del expediente.');
    }

    $pacientes_id = (int) $pacientes_id;
    $expediente = (int) $expediente;

    $stmtInsert = $mysqli->prepare(
        'INSERT INTO pacientes (
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
            pais_id,
            departamento_id,
            municipio_id,
            localidad,
            religion_id,
            profesion_id,
            estado_civil,
            responsable,
            responsable_id,
            usuario,
            estado,
            fecha_registro,
            referido_id,
            escolaridad,
            red_apoyo,
            terapeuta_actual,
            religion_texto,
            profesion_texto,
            estado_civil_texto,
            escolaridad_texto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmtInsert) {
        throw new Exception('No se pudo preparar el registro del paciente: ' . $mysqli->error);
    }

    $stmtInsert->bind_param(
        'iisssssssssiiisiiisiiisiissssss',
        $pacientes_id,
        $expediente,
        $identidad,
        $nombre,
        $apellido,
        $sexo,
        $telefono1,
        $telefono2,
        $fecha_nacimiento,
        $correo,
        $fecha,
        $pais_id,
        $departamento_id,
        $municipio_id,
        $localidad,
        $religion_id,
        $profesion_id,
        $estado_civil,
        $responsable,
        $responsable_id,
        $usuario,
        $estado,
        $fecha_registro,
        $referido_id,
        $escolaridad,
        $red_apoyo,
        $terapeuta_actual,
        $religion_texto,
        $profesion_texto,
        $estado_civil_texto,
        $escolaridad_texto
    );

    if (!$stmtInsert->execute() || $stmtInsert->affected_rows !== 1) {
        throw new Exception('No se pudo registrar el paciente: ' . $stmtInsert->error);
    }

    responderPaciente(
        'success',
        'Almacenado',
        'Registro almacenado correctamente.',
        'success'
    );
} catch (Throwable $e) {
    error_log('Error al agregar paciente: ' . $e->getMessage());
    responderPaciente('error', 'Error', $e->getMessage(), 'error');
} finally {
    foreach (array($stmtIdentidad, $stmtExiste, $stmtInsert) as $stmt) {
        if ($stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}