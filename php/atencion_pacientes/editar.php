<?php
session_start();
include '../funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];
$agenda_id = $_POST['agenda_id'];

// CONSULTAR LOS DATOS DEL PACIENTE
$sql = "SELECT p.identidad AS 'identidad', p.fecha_nacimiento 'fecha_nacimiento', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', p.localidad AS 'localidad', p.religion_id AS 'religion', p.profesion_id AS 'profesion', CAST(a.fecha_cita AS DATE) AS 'fecha', a.servicio_id AS 'servicio_id', p.estado_civil AS 'estado_civil', p.escolaridad, p.red_apoyo, p.terapeuta_actual, p.telefono1 AS 'telefono'
   FROM agenda AS a
   INNER JOIN pacientes AS p
   ON a.pacientes_id = p.pacientes_id
   WHERE a.agenda_id = '$agenda_id'";
$result = $mysqli->query($sql) or die($mysqli->error);

$identidad = '';
$nombre = '';
$fecha_nacimiento = '';
$edad = '';
$profesion = '';
$religion = '';
$servicio_id = '';
$fecha_cita = '';
$palabra_anos = '';
$palabra_mes = '';
$palabra_dia = '';
$estado_civil = '';
$escolaridad = '';
$red_apoyo = '';
$terapeuta_actual = '';
$telefono = '';

// OBTENEMOS LOS VALORES DEL REGISTRO
if ($result->num_rows > 0) {
	$consulta_registro = $result->fetch_assoc();

	$identidad = $consulta_registro['identidad'];
	$fecha_nacimiento = $consulta_registro['fecha_nacimiento'];
	$paciente = $consulta_registro['paciente'];
	$localidad = $consulta_registro['localidad'];
	$religion = $consulta_registro['religion'];
	$profesion = $consulta_registro['profesion'];
	$fecha_cita = $consulta_registro['fecha'];
	$servicio_id = $consulta_registro['servicio_id'];
	$estado_civil = $consulta_registro['estado_civil'];
	$escolaridad = $consulta_registro['escolaridad'];
	$red_apoyo = $consulta_registro['red_apoyo'];
	$terapeuta_actual = $consulta_registro['terapeuta_actual'];
	$telefono = $consulta_registro['telefono'];

	// CONSULTA AÑO, MES y DIA DEL PACIENTE
	$valores_array = getEdad($fecha_nacimiento);
	$anos = $valores_array['anos'];
	$meses = $valores_array['meses'];
	$dias = $valores_array['dias'];
	/*********************************************************************************/

	if ($anos > 1) {
		$palabra_anos = 'Años';
	} else {
		$palabra_anos = 'Año';
	}

	if ($meses > 1) {
		$palabra_mes = 'Meses';
	} else {
		$palabra_mes = 'Mes';
	}

	if ($dias > 1) {
		$palabra_dia = 'Días';
	} else {
		$palabra_dia = 'Día';
	}
}

// OBTENER HISTORIA CLINICA
$query_historia = "SELECT pacientes_id, antecedentes_medicos_no_psiquiatricos, hospitalizaciones, cirugias, alergias, antecedentes_medicos_psiquiatricos, historia_gineco_obstetrica, medicamentos_previos, medicamentos_actuales, legal, sustancias, rasgos_personalidad, informacion_adicional, pendientes, diagnostico, seguimiento, num_hijos
	FROM atenciones_medicas
	WHERE pacientes_id = '$pacientes_id'
	ORDER BY atencion_id DESC limit 1";
$result_historia = $mysqli->query($query_historia) or die($mysqli->error);

$antecedentes_medicos_no_psiquiatricos = '';
$hospitalizaciones = '';
$cirugias = '';
$alergias = '';
$antecedentes_medicos_psiquiatricos = '';
$historia_gineco_obstetrica = '';
$medicamentos_previos = '';
$medicamentos_actuales = '';
$legal = '';
$sustancias = '';
$rasgos_personalidad = '';
$informacion_adicional = '';
$pendientes = '';
$diagnostico = '';
$seguimiento = '';

$num_hijos = 0;

if ($result_historia->num_rows > 0) {
	$consulta_historia = $result_historia->fetch_assoc();

	$antecedentes_medicos_no_psiquiatricos = $consulta_historia['antecedentes_medicos_no_psiquiatricos'];
	$hospitalizaciones = $consulta_historia['hospitalizaciones'];
	$cirugias = $consulta_historia['cirugias'];
	$alergias = $consulta_historia['alergias'];
	$antecedentes_medicos_psiquiatricos = $consulta_historia['antecedentes_medicos_psiquiatricos'];
	$historia_gineco_obstetrica = $consulta_historia['historia_gineco_obstetrica'];
	$medicamentos_previos = $consulta_historia['medicamentos_previos'];
	$medicamentos_actuales = $consulta_historia['medicamentos_actuales'];
	$legal = $consulta_historia['legal'];
	$sustancias = $consulta_historia['sustancias'];
	$rasgos_personalidad = $consulta_historia['rasgos_personalidad'];
	$informacion_adicional = $consulta_historia['informacion_adicional'];
	$pendientes = $consulta_historia['pendientes'];
	$diagnostico = $consulta_historia['diagnostico'];
	$seguimiento = $consulta_historia['seguimiento'];

	$num_hijos = $consulta_historia['num_hijos'];
}

// OBTENER SEGUIMIENTO
$query_seguimiento = "SELECT fecha, seguimiento
	FROM atenciones_medicas
	WHERE pacientes_id = '$pacientes_id'";
$result_seguimiento = $mysqli->query($query_seguimiento) or die($mysqli->error);

$seguimiento_consulta = '';

while ($registro_seguimiento = $result_seguimiento->fetch_assoc()) {
	$fecha = $registro_seguimiento['fecha'];
	$fecha_formateada = formatear_fecha($fecha);  // Formatear la fecha
	$seguimiento = $registro_seguimiento['seguimiento'];

	$seguimiento_consulta .= 'Fecha: ' . $fecha_formateada . "\n" . $seguimiento . "\n\n";
}

$datos = array(
	0 => $identidad,
	1 => $paciente,
	2 => $anos . ' ' . $palabra_anos . ', ' . $meses . ' ' . $palabra_mes . ' y ' . $dias . ' ' . $palabra_dia,
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
	31 => $telefono,
);

echo json_encode($datos);

$result->free();  // LIMPIAR RESULTADO
$mysqli->close();  // CERRAR CONEXIÓN