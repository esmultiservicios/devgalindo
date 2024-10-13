<?php
session_start();
include '../funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];

// CONSULTAR LOS DATOS DEL PACIENTE
$sql = "SELECT identidad AS 'identidad', fecha_nacimiento 'fecha_nacimiento', CONCAT(nombre, ' ', apellido) AS 'paciente', profesion_id AS 'profesion', 
   localidad AS 'localidad', religion_id AS 'religion', estado_civil, escolaridad, red_apoyo, terapeuta_actual, telefono1 AS 'telefono'
   FROM pacientes
   WHERE pacientes_id = '$pacientes_id'";

$result = $mysqli->query($sql) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();

$identidad = '';
$nombre = '';
$fecha_nacimiento = '';
$edad = '';
$profesion = '';
$religion = '';
$servicio_id = '';
$fecha_cita = '';
$estado_civil = '';
$escolaridad = '';
$red_apoyo = '';
$terapeuta_actual = '';
$telefono = '';

// OBTENEMOS LOS VALORES DEL REGISTRO
if ($result->num_rows > 0) {
	$identidad = $consulta_registro['identidad'];
	$fecha_nacimiento = $consulta_registro['fecha_nacimiento'];
	$paciente = $consulta_registro['paciente'];
	$localidad = $consulta_registro['localidad'];
	$religion = $consulta_registro['religion'];
	$profesion = $consulta_registro['profesion'];
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
$num_hijos = '';

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
	2 => $anos,
	3 => $localidad,
	4 => $religion,
	5 => $profesion,
	6 => $pacientes_id,
	7 => $antecedentes_medicos_no_psiquiatricos,
	8 => $hospitalizaciones,
	9 => $cirugias,
	10 => $seguimiento_consulta,
	11 => $diagnostico,
	12 => $fecha_nacimiento,
	13 => $estado_civil,
	14 => $alergias,
	15 => $antecedentes_medicos_psiquiatricos,
	16 => $historia_gineco_obstetrica,
	17 => $medicamentos_previos,
	18 => $medicamentos_actuales,
	19 => $legal,
	20 => $sustancias,
	21 => $rasgos_personalidad,
	22 => $informacion_adicional,
	23 => $pendientes,
	24 => $diagnostico,
	25 => $seguimiento,
	26 => $num_hijos,
	27 => $escolaridad,
	28 => $red_apoyo,
	29 => $terapeuta_actual,
	30 => $telefono
);

echo json_encode($datos);

$result->free();  // LIMPIAR RESULTADO
$mysqli->close();  // CERRAR CONEXIÓN