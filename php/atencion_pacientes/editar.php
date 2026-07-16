<?php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

function responderError($mensaje, $codigoHttp = 400)
{
    http_response_code($codigoHttp);
    echo json_encode(array(
        'status' => 'error',
        'title' => 'Error',
        'message' => $mensaje,
        'type' => 'error'
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmtPaciente = null;
$stmtHistoria = null;
$stmtSeguimiento = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        responderError('La sesión del usuario no es válida. Inicie sesión nuevamente.', 401);
    }

    /*
     * Libera el bloqueo de la sesión cuanto antes.
     * Este endpoint solo necesita validar que la sesión exista.
     */
    session_write_close();

    $pacientes_id = isset($_POST['pacientes_id']) ? (int) $_POST['pacientes_id'] : 0;
    $agenda_id = isset($_POST['agenda_id']) ? (int) $_POST['agenda_id'] : 0;

    if ($pacientes_id <= 0 || $agenda_id <= 0) {
        responderError('El paciente o la agenda no son válidos.');
    }

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    /*
     * Consulta principal:
     * - Usa agenda_id y pacientes_id para evitar cargar datos de otra agenda.
     * - No usa CAST() en el WHERE.
     * - Solo devuelve los campos utilizados por el JavaScript.
     */
    $stmtPaciente = $mysqli->prepare(
        "SELECT
            p.identidad,
            p.fecha_nacimiento,
            CONCAT(p.nombre, ' ', p.apellido) AS paciente,
            p.localidad,
            p.religion_texto AS religion,
            p.profesion_texto AS profesion,
            DATE(a.fecha_cita) AS fecha,
            a.servicio_id,
            p.estado_civil_texto AS estado_civil,
            p.escolaridad_texto AS escolaridad,
            p.red_apoyo,
            p.terapeuta_actual,
            p.telefono1 AS telefono
         FROM agenda AS a
         INNER JOIN pacientes AS p
            ON a.pacientes_id = p.pacientes_id
         WHERE a.agenda_id = ?
           AND a.pacientes_id = ?
         LIMIT 1"
    );

    if (!$stmtPaciente) {
        throw new Exception('No se pudo preparar la consulta del paciente: ' . $mysqli->error);
    }

    $stmtPaciente->bind_param('ii', $agenda_id, $pacientes_id);

    if (!$stmtPaciente->execute()) {
        throw new Exception('No se pudo consultar el paciente: ' . $stmtPaciente->error);
    }

    $resultadoPaciente = $stmtPaciente->get_result();

    if ($resultadoPaciente->num_rows !== 1) {
        responderError('La agenda seleccionada no corresponde al paciente.');
    }

    $pacienteDatos = $resultadoPaciente->fetch_assoc();

    $identidad = (string) ($pacienteDatos['identidad'] ?? '');
    $fecha_nacimiento = (string) ($pacienteDatos['fecha_nacimiento'] ?? '');
    $paciente = (string) ($pacienteDatos['paciente'] ?? '');
    $localidad = (string) ($pacienteDatos['localidad'] ?? '');
    $religion = (string) ($pacienteDatos['religion'] ?? '');
    $profesion = (string) ($pacienteDatos['profesion'] ?? '');
    $fecha_cita = (string) ($pacienteDatos['fecha'] ?? '');
    $servicio_id = (int) ($pacienteDatos['servicio_id'] ?? 0);
    $estado_civil = (string) ($pacienteDatos['estado_civil'] ?? '');
    $escolaridad = (string) ($pacienteDatos['escolaridad'] ?? '');
    $red_apoyo = (string) ($pacienteDatos['red_apoyo'] ?? '');
    $terapeuta_actual = (string) ($pacienteDatos['terapeuta_actual'] ?? '');
    $telefono = (string) ($pacienteDatos['telefono'] ?? '');

    $anos = 0;
    $meses = 0;
    $dias = 0;

    if ($fecha_nacimiento !== '' && $fecha_nacimiento !== '0000-00-00') {
        $edad = getEdad($fecha_nacimiento);
        $anos = isset($edad['anos']) ? (int) $edad['anos'] : 0;
        $meses = isset($edad['meses']) ? (int) $edad['meses'] : 0;
        $dias = isset($edad['dias']) ? (int) $edad['dias'] : 0;
    }

    $palabra_anos = $anos === 1 ? 'Año' : 'Años';
    $palabra_mes = $meses === 1 ? 'Mes' : 'Meses';
    $palabra_dia = $dias === 1 ? 'Día' : 'Días';

    /*
     * Última historia clínica.
     * LIMIT 1 evita traer más columnas y filas de las necesarias.
     */
    $stmtHistoria = $mysqli->prepare(
        "SELECT
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
            seguimiento,
            num_hijos
         FROM atenciones_medicas
         WHERE pacientes_id = ?
         ORDER BY atencion_id DESC
         LIMIT 1"
    );

    if (!$stmtHistoria) {
        throw new Exception('No se pudo preparar la historia clínica: ' . $mysqli->error);
    }

    $stmtHistoria->bind_param('i', $pacientes_id);

    if (!$stmtHistoria->execute()) {
        throw new Exception('No se pudo consultar la historia clínica: ' . $stmtHistoria->error);
    }

    $resultadoHistoria = $stmtHistoria->get_result();
    $historia = $resultadoHistoria->num_rows === 1
        ? $resultadoHistoria->fetch_assoc()
        : array();

    $antecedentes_medicos_no_psiquiatricos = (string) ($historia['antecedentes_medicos_no_psiquiatricos'] ?? '');
    $hospitalizaciones = (string) ($historia['hospitalizaciones'] ?? '');
    $cirugias = (string) ($historia['cirugias'] ?? '');
    $alergias = (string) ($historia['alergias'] ?? '');
    $antecedentes_medicos_psiquiatricos = (string) ($historia['antecedentes_medicos_psiquiatricos'] ?? '');
    $historia_gineco_obstetrica = (string) ($historia['historia_gineco_obstetrica'] ?? '');
    $medicamentos_previos = (string) ($historia['medicamentos_previos'] ?? '');
    $medicamentos_actuales = (string) ($historia['medicamentos_actuales'] ?? '');
    $legal = (string) ($historia['legal'] ?? '');
    $sustancias = (string) ($historia['sustancias'] ?? '');
    $rasgos_personalidad = (string) ($historia['rasgos_personalidad'] ?? '');
    $informacion_adicional = (string) ($historia['informacion_adicional'] ?? '');
    $pendientes = (string) ($historia['pendientes'] ?? '');
    $diagnostico = (string) ($historia['diagnostico'] ?? '');
    $seguimiento = (string) ($historia['seguimiento'] ?? '');
    $num_hijos = isset($historia['num_hijos']) ? (int) $historia['num_hijos'] : 0;

    /*
     * Historial de seguimiento.
     * Solo trae filas que realmente contienen seguimiento.
     * Se mantiene todo el historial para no cambiar la lógica del sistema.
     */
    $stmtSeguimiento = $mysqli->prepare(
        "SELECT fecha, seguimiento
         FROM atenciones_medicas
         WHERE pacientes_id = ?
           AND seguimiento IS NOT NULL
           AND seguimiento <> ''
         ORDER BY fecha DESC, atencion_id DESC"
    );

    if (!$stmtSeguimiento) {
        throw new Exception('No se pudo preparar el seguimiento: ' . $mysqli->error);
    }

    $stmtSeguimiento->bind_param('i', $pacientes_id);

    if (!$stmtSeguimiento->execute()) {
        throw new Exception('No se pudo consultar el seguimiento: ' . $stmtSeguimiento->error);
    }

    $resultadoSeguimiento = $stmtSeguimiento->get_result();
    $seguimiento_consulta = '';

    while ($filaSeguimiento = $resultadoSeguimiento->fetch_assoc()) {
        $fechaSeguimiento = (string) ($filaSeguimiento['fecha'] ?? '');
        $textoSeguimiento = (string) ($filaSeguimiento['seguimiento'] ?? '');

        if ($fechaSeguimiento === '' || $textoSeguimiento === '') {
            continue;
        }

        $seguimiento_consulta .=
            'Fecha: ' . formatear_fecha($fechaSeguimiento) . "\n" .
            $textoSeguimiento . "\n\n";
    }

    /*
     * Se conserva exactamente el arreglo posicional que consume el JS.
     */
    $datos = array(
        0 => $identidad,
        1 => $paciente,
        2 => $anos . ' ' . $palabra_anos . ', ' .
             $meses . ' ' . $palabra_mes . ' y ' .
             $dias . ' ' . $palabra_dia,
        3 => $localidad,
        4 => $religion,
        5 => $profesion,
        6 => $pacientes_id,
        7 => $fecha_cita,
        8 => $fecha_nacimiento,
        9 => $antecedentes_medicos_no_psiquiatricos,
        10 => $hospitalizaciones,
        11 => $cirugias,
        12 => $alergias,
        13 => $seguimiento_consulta,
        14 => $servicio_id,
        15 => $estado_civil,
        16 => $num_hijos,
        17 => $escolaridad,
        18 => $red_apoyo,
        19 => $terapeuta_actual,
        20 => $antecedentes_medicos_psiquiatricos,
        21 => $historia_gineco_obstetrica,
        22 => $medicamentos_previos,
        23 => $medicamentos_actuales,
        24 => $legal,
        25 => $sustancias,
        26 => $rasgos_personalidad,
        27 => $informacion_adicional,
        28 => $pendientes,
        29 => $diagnostico,
        30 => $seguimiento,
        31 => $telefono
    );

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('Error editar.php: ' . $e->getMessage());

    responderError(
        'No se pudo cargar la información de la atención.',
        500
    );
} finally {
    if ($stmtPaciente instanceof mysqli_stmt) {
        $stmtPaciente->close();
    }

    if ($stmtHistoria instanceof mysqli_stmt) {
        $stmtHistoria->close();
    }

    if ($stmtSeguimiento instanceof mysqli_stmt) {
        $stmtSeguimiento->close();
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
