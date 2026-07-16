<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

// CONEXIÓN A DB
$mysqli = connect_mysqli();

$datos = array(
    0 => "Error",
    1 => "No se pudo editar este registro, los datos son incorrectos. Por favor, verifique la información.",
    2 => "error",
    3 => "btn-danger",
    4 => "",
    5 => ""
);

try {
    // VALIDAR SESIÓN
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception("La sesión del usuario no es válida. Inicie sesión nuevamente.");
    }

    // VALIDAR CAMPOS OBLIGATORIOS
    $campos_obligatorios = array(
        'pacientes_id',
        'name',
        'lastname',
        'sexo',
        'telefono1',
        'fecha_nac',
        'direccion'
    );

    foreach ($campos_obligatorios as $campo) {
        if (!isset($_POST[$campo]) || trim((string)$_POST[$campo]) === '') {
            throw new Exception("Hay campos obligatorios vacíos. Por favor, complete toda la información requerida.");
        }
    }

    $pacientes_id = filter_var($_POST['pacientes_id'], FILTER_VALIDATE_INT);

    if ($pacientes_id === false || $pacientes_id <= 0) {
        throw new Exception("El identificador del paciente no es válido.");
    }

    $usuario = (int)$_SESSION['colaborador_id'];
    $estado = 1; // 1. Activo 2. Inactivo
    $fecha_registro = date("Y-m-d H:i:s");

    // LOS TEXTOS SE CONSERVAN COMO FUERON ESCRITOS POR EL USUARIO
    $nombre = trim((string)$_POST['name']);
    $apellido = trim((string)$_POST['lastname']);
    $sexo = trim((string)$_POST['sexo']);
    $telefono1 = trim((string)$_POST['telefono1']);
    $telefono2 = isset($_POST['telefono2']) ? trim((string)$_POST['telefono2']) : '';
    $fecha_nacimiento = trim((string)$_POST['fecha_nac']);
    $correo = isset($_POST['correo']) ? strtolower(trim((string)$_POST['correo'])) : '';
    $localidad = trim((string)$_POST['direccion']);
    $responsable = isset($_POST['responsable']) ? trim((string)$_POST['responsable']) : '';

    // VALIDAR FECHA DE NACIMIENTO
    $fecha_objeto = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $errores_fecha = DateTime::getLastErrors();

    if (
        $fecha_objeto === false ||
        ($errores_fecha !== false && ($errores_fecha['warning_count'] > 0 || $errores_fecha['error_count'] > 0)) ||
        $fecha_objeto->format('Y-m-d') !== $fecha_nacimiento
    ) {
        throw new Exception("La fecha de nacimiento no es válida.");
    }

    // VALIDAR CORREO SOLO CUANDO SE INGRESE
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El correo electrónico ingresado no es válido.");
    }

    // CAMPOS NUMÉRICOS OPCIONALES
    $pais_id = (isset($_POST['pais_id']) && $_POST['pais_id'] !== '')
        ? filter_var($_POST['pais_id'], FILTER_VALIDATE_INT)
        : 0;

    $departamento_id = (isset($_POST['departamento_id']) && $_POST['departamento_id'] !== '')
        ? filter_var($_POST['departamento_id'], FILTER_VALIDATE_INT)
        : 0;

    $municipio_id = (isset($_POST['municipio_id']) && $_POST['municipio_id'] !== '')
        ? filter_var($_POST['municipio_id'], FILTER_VALIDATE_INT)
        : 0;

    $responsable_id = (isset($_POST['responsable_id']) && $_POST['responsable_id'] !== '')
        ? filter_var($_POST['responsable_id'], FILTER_VALIDATE_INT)
        : 0;

    $referido_id = (isset($_POST['referido_id']) && $_POST['referido_id'] !== '')
        ? filter_var($_POST['referido_id'], FILTER_VALIDATE_INT)
        : 0;

    $pais_id = ($pais_id === false || $pais_id < 0) ? 0 : (int)$pais_id;
    $departamento_id = ($departamento_id === false || $departamento_id < 0) ? 0 : (int)$departamento_id;
    $municipio_id = ($municipio_id === false || $municipio_id < 0) ? 0 : (int)$municipio_id;
    $responsable_id = ($responsable_id === false || $responsable_id < 0) ? 0 : (int)$responsable_id;
    $referido_id = ($referido_id === false || $referido_id < 0) ? 0 : (int)$referido_id;

    // CONFIRMAR QUE EL PACIENTE EXISTA
    $consulta_paciente = $mysqli->prepare(
        "SELECT pacientes_id
         FROM pacientes
         WHERE pacientes_id = ?
         LIMIT 1"
    );

    if (!$consulta_paciente) {
        throw new Exception("No se pudo preparar la validación del paciente.");
    }

    $consulta_paciente->bind_param("i", $pacientes_id);

    if (!$consulta_paciente->execute()) {
        throw new Exception("No se pudo validar el paciente.");
    }

    $resultado_paciente = $consulta_paciente->get_result();

    if ($resultado_paciente->num_rows === 0) {
        $consulta_paciente->close();
        throw new Exception("El paciente que intenta editar no existe.");
    }

    $consulta_paciente->close();

    // ACTUALIZAR PACIENTE MEDIANTE CONSULTA PREPARADA
    $update = $mysqli->prepare(
        "UPDATE pacientes
         SET nombre = ?,
             apellido = ?,
             genero = ?,
             telefono1 = ?,
             telefono2 = ?,
             email = ?,
             fecha_nacimiento = ?,
             pais_id = ?,
             departamento_id = ?,
             municipio_id = ?,
             responsable = ?,
             responsable_id = ?,
             localidad = ?,
             referido_id = ?
         WHERE pacientes_id = ?"
    );

    if (!$update) {
        throw new Exception("No se pudo preparar la actualización del paciente.");
    }

    $update->bind_param(
        "sssssssiiisisii",
        $nombre,
        $apellido,
        $sexo,
        $telefono1,
        $telefono2,
        $correo,
        $fecha_nacimiento,
        $pais_id,
        $departamento_id,
        $municipio_id,
        $responsable,
        $responsable_id,
        $localidad,
        $referido_id,
        $pacientes_id
    );

    if (!$update->execute()) {
        throw new Exception("No se pudo actualizar el registro del paciente.");
    }

    $update->close();

    $datos = array(
        0 => "Editado",
        1 => "Registro Editado Correctamente",
        2 => "success",
        3 => "btn-primary",
        4 => "",
        5 => "Editar",
        6 => "formPacientes",
        7 => "modal_pacientes"
    );
} catch (Throwable $e) {
    $datos = array(
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => ""
    );
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);