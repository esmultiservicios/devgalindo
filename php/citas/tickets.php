<?php
session_start();
include "../funtions.php";

set_include_path('../../fpdf/font');
require('../../fpdf/fpdf.php');

function textoPdf($texto)
{
    $texto = (string) $texto;

    if ($texto === '') {
        return '';
    }

    $convertido = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $texto);

    return $convertido !== false ? $convertido : $texto;
}

function cerrarSentencia($stmt)
{
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

$mysqli = null;
$stmtAgenda = null;
$stmtEmpresa = null;
$stmtPaciente = null;
$stmtMedico = null;
$stmtPuesto = null;
$stmtServicio = null;
$stmtUsuarioSistema = null;
$stmtTipoUsuario = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    if (!isset($_GET['agenda_id']) || trim((string) $_GET['agenda_id']) === '') {
        throw new Exception('No se recibió el identificador de la cita.');
    }

    $agenda_id = filter_var($_GET['agenda_id'], FILTER_VALIDATE_INT);

    if ($agenda_id === false || $agenda_id <= 0) {
        throw new Exception('El identificador de la cita no es válido.');
    }

    $usuario_actual = (int) $_SESSION['colaborador_id'];

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    /* ============================================================
       CONSULTA DE LA AGENDA
    ============================================================ */
    $stmtAgenda = $mysqli->prepare(
        "SELECT
            usuario,
            DATE_FORMAT(CAST(fecha_cita AS DATE), '%d/%m/%Y') AS fecha_cita,
            CAST(fecha_cita AS DATE) AS fecha1,
            hora,
            DATE_FORMAT(fecha_registro, '%d/%m/%Y %h:%i:%s %p') AS fecha_registro,
            pacientes_id,
            colaborador_id,
            expediente,
            servicio_id,
            reprogramo
         FROM agenda
         WHERE agenda_id = ?
         LIMIT 1"
    );

    if (!$stmtAgenda) {
        throw new Exception('No se pudo preparar la consulta de la cita: ' . $mysqli->error);
    }

    $stmtAgenda->bind_param('i', $agenda_id);

    if (!$stmtAgenda->execute()) {
        throw new Exception('No se pudo consultar la cita: ' . $stmtAgenda->error);
    }

    $resultadoAgenda = $stmtAgenda->get_result();

    if ($resultadoAgenda->num_rows !== 1) {
        throw new Exception('La cita solicitada no existe.');
    }

    $agenda = $resultadoAgenda->fetch_assoc();

    $pacientes_id = (int) $agenda['pacientes_id'];
    $colaborador_id = (int) $agenda['colaborador_id'];
    $expediente = (int) $agenda['expediente'];
    $servicio_id = (int) $agenda['servicio_id'];
    $usuario_sistema = (int) $agenda['usuario'];
    $fecha_registro = (string) $agenda['fecha_registro'];
    $reprogramo = (int) $agenda['reprogramo'];
    $fecha_cita = (string) $agenda['fecha_cita'];
    $hora_cita = (string) $agenda['hora'];

    $reprogramo_cita = $reprogramo === 1 ? '(Reprogramación)' : '';
    $exp = $expediente === 0 ? 'TEMP' : (string) $expediente;

    /* ============================================================
       DATOS DE LA EMPRESA
    ============================================================ */
    $stmtEmpresa = $mysqli->prepare(
        "SELECT
            e.telefono,
            e.celular,
            e.correo,
            e.eslogan,
            e.horario
         FROM users AS u
         INNER JOIN empresa AS e
            ON u.empresa_id = e.empresa_id
         WHERE u.colaborador_id = ?
         LIMIT 1"
    );

    if (!$stmtEmpresa) {
        throw new Exception('No se pudo preparar la consulta de la empresa: ' . $mysqli->error);
    }

    $stmtEmpresa->bind_param('i', $usuario_actual);

    if (!$stmtEmpresa->execute()) {
        throw new Exception('No se pudo consultar la empresa: ' . $stmtEmpresa->error);
    }

    $resultadoEmpresa = $stmtEmpresa->get_result();

    $telefono = '';
    $celular = '';
    $correo_empresa = 'admision@mentesanahn.com';
    $horario = '';
    $eslogan = '';

    if ($resultadoEmpresa->num_rows > 0) {
        $empresa = $resultadoEmpresa->fetch_assoc();

        $telefono = (string) ($empresa['telefono'] ?? '');
        $celular = (string) ($empresa['celular'] ?? '');
        $correo_empresa = (string) ($empresa['correo'] ?? $correo_empresa);
        $horario = (string) ($empresa['horario'] ?? '');
        $eslogan = (string) ($empresa['eslogan'] ?? '');
    }

    /* ============================================================
       DATOS DEL PACIENTE
    ============================================================ */
    $stmtPaciente = $mysqli->prepare(
        "SELECT
            CONCAT(nombre, ' ', apellido) AS nombre,
            identidad
         FROM pacientes
         WHERE pacientes_id = ?
         LIMIT 1"
    );

    if (!$stmtPaciente) {
        throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    }

    $stmtPaciente->bind_param('i', $pacientes_id);

    if (!$stmtPaciente->execute()) {
        throw new Exception('No se pudo consultar el paciente: ' . $stmtPaciente->error);
    }

    $resultadoPaciente = $stmtPaciente->get_result();

    $nombre_usuario = '';
    $identidad_usuario = '';

    if ($resultadoPaciente->num_rows > 0) {
        $paciente = $resultadoPaciente->fetch_assoc();

        $nombre_usuario = (string) ($paciente['nombre'] ?? '');
        $identidad_usuario = (string) ($paciente['identidad'] ?? '');
    }

    /* ============================================================
       DATOS DEL PROFESIONAL
    ============================================================ */
    $stmtMedico = $mysqli->prepare(
        "SELECT
            CONCAT(nombre, ' ', apellido) AS nombre,
            puesto_id
         FROM colaboradores
         WHERE colaborador_id = ?
         LIMIT 1"
    );

    if (!$stmtMedico) {
        throw new Exception('No se pudo preparar la consulta del profesional: ' . $mysqli->error);
    }

    $stmtMedico->bind_param('i', $colaborador_id);

    if (!$stmtMedico->execute()) {
        throw new Exception('No se pudo consultar el profesional: ' . $stmtMedico->error);
    }

    $resultadoMedico = $stmtMedico->get_result();

    $puesto_id = 0;
    $nombre_medico = '';

    if ($resultadoMedico->num_rows > 0) {
        $medico = $resultadoMedico->fetch_assoc();

        $puesto_id = (int) ($medico['puesto_id'] ?? 0);
        $nombre_medico = (string) ($medico['nombre'] ?? '');
    }

    /* ============================================================
       TIPO DE PROFESIONAL
    ============================================================ */
    $stmtPuesto = $mysqli->prepare(
        "SELECT nombre, puesto_id
         FROM puesto_colaboradores
         WHERE puesto_id = ?
         LIMIT 1"
    );

    if (!$stmtPuesto) {
        throw new Exception('No se pudo preparar la consulta del puesto: ' . $mysqli->error);
    }

    $stmtPuesto->bind_param('i', $puesto_id);

    if (!$stmtPuesto->execute()) {
        throw new Exception('No se pudo consultar el puesto: ' . $stmtPuesto->error);
    }

    $resultadoPuesto = $stmtPuesto->get_result();

    $puesto = '';
    $consultar_colaborador = 0;

    if ($resultadoPuesto->num_rows > 0) {
        $puestoDatos = $resultadoPuesto->fetch_assoc();

        $puesto = trim((string) ($puestoDatos['nombre'] ?? ''));
        $consultar_colaborador = (int) ($puestoDatos['puesto_id'] ?? 0);
    }

    /* ============================================================
       SERVICIO
    ============================================================ */
    $stmtServicio = $mysqli->prepare(
        "SELECT nombre
         FROM servicios
         WHERE servicio_id = ?
         LIMIT 1"
    );

    if (!$stmtServicio) {
        throw new Exception('No se pudo preparar la consulta del servicio: ' . $mysqli->error);
    }

    $stmtServicio->bind_param('i', $servicio_id);

    if (!$stmtServicio->execute()) {
        throw new Exception('No se pudo consultar el servicio: ' . $stmtServicio->error);
    }

    $resultadoServicio = $stmtServicio->get_result();

    $servicio = '';

    if ($resultadoServicio->num_rows > 0) {
        $servicioDatos = $resultadoServicio->fetch_assoc();
        $servicio = trim((string) ($servicioDatos['nombre'] ?? ''));
    }

    /* ============================================================
       USUARIO QUE REGISTRÓ LA CITA
    ============================================================ */
    $stmtUsuarioSistema = $mysqli->prepare(
        "SELECT CONCAT(nombre, ' ', apellido) AS nombre
         FROM colaboradores
         WHERE colaborador_id = ?
         LIMIT 1"
    );

    if (!$stmtUsuarioSistema) {
        throw new Exception('No se pudo preparar la consulta del usuario: ' . $mysqli->error);
    }

    $stmtUsuarioSistema->bind_param('i', $usuario_sistema);

    if (!$stmtUsuarioSistema->execute()) {
        throw new Exception('No se pudo consultar el usuario: ' . $stmtUsuarioSistema->error);
    }

    $resultadoUsuarioSistema = $stmtUsuarioSistema->get_result();

    $usuario_sistema_nombre = '';

    if ($resultadoUsuarioSistema->num_rows > 0) {
        $usuarioSistemaDatos = $resultadoUsuarioSistema->fetch_assoc();
        $usuario_sistema_nombre = trim((string) ($usuarioSistemaDatos['nombre'] ?? ''));
    }

    /* ============================================================
       DETERMINAR SI ES NUEVO O SUBSIGUIENTE
    ============================================================ */
    $stmtTipoUsuario = $mysqli->prepare(
        "SELECT a.agenda_id
         FROM agenda AS a
         INNER JOIN colaboradores AS c
            ON a.colaborador_id = c.colaborador_id
         WHERE a.pacientes_id = ?
           AND a.servicio_id = ?
           AND c.puesto_id = ?
           AND a.status = 1
         LIMIT 1"
    );

    if (!$stmtTipoUsuario) {
        throw new Exception('No se pudo preparar la validación del tipo de paciente: ' . $mysqli->error);
    }

    $stmtTipoUsuario->bind_param(
        'iii',
        $pacientes_id,
        $servicio_id,
        $consultar_colaborador
    );

    if (!$stmtTipoUsuario->execute()) {
        throw new Exception('No se pudo determinar el tipo de paciente: ' . $stmtTipoUsuario->error);
    }

    $resultadoTipoUsuario = $stmtTipoUsuario->get_result();
    $tipo_usuario = $resultadoTipoUsuario->num_rows > 0 ? 'Subsiguiente' : 'Nuevo';

    $hora = date('g:i a', strtotime($hora_cita));

    /* ============================================================
       CREACIÓN DEL PDF
    ============================================================ */
    $pdf = new FPDF('P', 'mm', array(80, 170));
    $pdf->SetMargins(6, 0.3, 6);
    $pdf->SetAutoPageBreak(true, 0.5);
    $pdf->AddPage();

    $rutaLogo = '../../img/logo.png';

    if (is_file($rutaLogo)) {
        $pdf->Image($rutaLogo, 11, 2, 45, 10, 'PNG');
    }

    $pdf->Ln(12);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(68, 5, textoPdf('Cita N°: ' . $agenda_id), 0, 1);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(68, 5, textoPdf('Fecha Cita: ' . $fecha_cita . ' Hora: ' . $hora), 0, 1);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(68, 5, textoPdf('Tipo de Cita: ' . $tipo_usuario . ' ' . $reprogramo_cita), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Nombre: ' . $nombre_usuario), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Identidad: ' . $identidad_usuario . '  Exp: ' . $exp), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Profesional: ' . $nombre_medico), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Servicio: ' . $servicio), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Especialidad: ' . $puesto), 0, 1);
    $pdf->Cell(68, 5, textoPdf('Usuario: ' . $usuario_sistema_nombre), 0, 1);

    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(68, 5, textoPdf('Nota:'), 0, 1);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(
        68,
        4,
        textoPdf(
            "Por favor estar 15 minutos antes de su cita.\n" .
            "Tomando las medidas de bioseguridad.\n" .
            $eslogan
        ),
        0,
        'L'
    );

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(68, 4, '__________________________', 0, 1, 'C');
    $pdf->Cell(68, 4, textoPdf('Firma y Sello'), 0, 1, 'C');

    $pdf->Ln(3);
    $pdf->Cell(68, 4, textoPdf('Nos puede llamar al siguiente número'), 0, 1);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(68, 4, textoPdf('PBX: ' . $telefono), 0, 1);

    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(
        68,
        4,
        textoPdf('Fecha Registro: ' . $fecha_registro),
        0,
        'L'
    );

    cerrarSentencia($stmtAgenda);
    cerrarSentencia($stmtEmpresa);
    cerrarSentencia($stmtPaciente);
    cerrarSentencia($stmtMedico);
    cerrarSentencia($stmtPuesto);
    cerrarSentencia($stmtServicio);
    cerrarSentencia($stmtUsuarioSistema);
    cerrarSentencia($stmtTipoUsuario);

    $mysqli->close();
    $mysqli = null;

    /*
     * No debe existir ninguna salida antes de FPDF.
     * Se limpia cualquier búfer accidental para evitar:
     * "Some data has already been output, can't send PDF file".
     */
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $pdf->Output('I', 'Citas.pdf');
    exit;
} catch (Throwable $e) {
    cerrarSentencia($stmtAgenda);
    cerrarSentencia($stmtEmpresa);
    cerrarSentencia($stmtPaciente);
    cerrarSentencia($stmtMedico);
    cerrarSentencia($stmtPuesto);
    cerrarSentencia($stmtServicio);
    cerrarSentencia($stmtUsuarioSistema);
    cerrarSentencia($stmtTipoUsuario);

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }

    error_log('Error al generar ticket de cita: ' . $e->getMessage());

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se pudo generar el ticket de la cita. ' . $e->getMessage();
    exit;
}