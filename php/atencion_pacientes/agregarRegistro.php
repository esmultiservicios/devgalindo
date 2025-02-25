<?php
session_start();
include '../funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

$agenda_id = $_POST['agenda_id'];
$pacientes_id = $_POST['pacientes_id'];
$fecha = $_POST['fecha'];
$localidad = cleanStringStrtolower($_POST['procedencia']);

$red_apoyo = cleanStringStrtolower($_POST['red_apoyo']);
$terapeuta_actual = cleanStringStrtolower($_POST['terapeuta_actual']);

$identidad = $_POST['identidad'];
$fecha_nac = $_POST['fecha_nac'];
$telefono1 = $_POST['telefono1'];

$num_hijos = isset($_POST['num_hijos']) && $_POST['num_hijos'] !== '' ? intval($_POST['num_hijos']) : 0;

$colaborador_id = $_SESSION['colaborador_id'];
$hora = date('H:i', strtotime('00:00'));
$fecha_cita = date('Y-m-d H:i:s', strtotime($fecha));
$fecha_cita_end = date('Y-m-d H:i:s', strtotime($fecha));
$fecha_registro = date('Y-m-d H:i:s');
$status = 1;  // ESTADO PARA LA AGENDA DEL PACIENTE
$estado = 1;  // ESTADO DE LA ATENCION DEL PACIENTE PARA LA FACTURACION 1. PENDIENTE 2. PAGADA

$antecedentes_medicos_no_psiquiatricos = cleanStringStrtolower($_POST['antecedentes_medicos_no_psiquiatricos']);
$hospitalizaciones = cleanStringStrtolower($_POST['hospitalizaciones']);
$cirugias = cleanStringStrtolower($_POST['cirugias']);
$alergias = cleanStringStrtolower($_POST['alergias']);
$antecedentes_medicos_psiquiatricos = cleanStringStrtolower($_POST['antecedentes_medicos_psiquiatricos']);
$historia_gineco_obstetrica = cleanStringStrtolower($_POST['historia_gineco_obstetrica']);
$medicamentos_previos = cleanStringStrtolower($_POST['medicamentos_previos']);
$medicamentos_actuales = cleanStringStrtolower($_POST['medicamentos_actuales']);
$legal = cleanStringStrtolower($_POST['legal']);
$sustancias = cleanStringStrtolower($_POST['sustancias']);
$rasgos_personalidad = cleanStringStrtolower($_POST['rasgos_personalidad']);
$informacion_adicional = cleanStringStrtolower($_POST['informacion_adicional']);
$pendientes = cleanStringStrtolower($_POST['pendientes']);
$diagnostico = cleanStringStrtolower($_POST['diagnostico']);
$seguimiento = cleanStringStrtolower($_POST['seguimiento']);

$religion = cleanStringStrtolower($_POST['religion_id']);
$profesion = cleanStringStrtolower($_POST['profesion']);
$estado_civil = cleanStringStrtolower($_POST['estado_civil']);
$escolaridad = cleanStringStrtolower($_POST['escolaridad']);

// CONSULTAR SERVICIO_ID
$query_servicio = "SELECT servicio_id
	FROM agenda
	WHERE pacientes_id = '$pacientes_id' AND CAST(fecha_cita AS DATE) = '$fecha' AND status = 0";
$result_servicio = $mysqli->query($query_servicio) or die($mysqli->error);
$consultar_servicio = $result_servicio->fetch_assoc();

$servicio_id = '';

if ($result_servicio->num_rows >= 0) {
	$servicio_id = $consultar_servicio['servicio_id'];
}

/* ############################################################################################################################################################################################## */
// ACTUALIZAMOS LOS DATOS DEL PACIENTE
$update = "UPDATE pacientes 
	SET 
		estado_civil_texto = '$estado_civil',
		religion_texto = '$religion', 
		profesion_texto = '$profesion',
		localidad = '$localidad',
		escolaridad_texto = '$escolaridad',
		red_apoyo = '$red_apoyo',
		terapeuta_actual = '$terapeuta_actual',
		telefono1 = '$telefono1',
		identidad = '$identidad',
		fecha_nacimiento = '$fecha_nac'
	WHERE pacientes_id = '$pacientes_id'";
$mysqli->query($update) or die($mysqli->error);
/* ############################################################################################################################################################################################## */
$query_fecha_nac = "SELECT fecha_nacimiento
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result_fecha_nacimiento = $mysqli->query($query_fecha_nac);

$fecha_nacimiento = date('Y-m-d');

if ($result_fecha_nacimiento->num_rows > 0) {
	$consulta_expediente1 = $result_fecha_nacimiento->fetch_assoc();
	$fecha_nacimiento = $consulta_expediente1['fecha_nacimiento'];
}

// CONSULTA AÑO, MES y DIA DEL PACIENTE
$valores_array = getEdad($fecha_nacimiento);
$anos = $valores_array['anos'];
$meses = $valores_array['meses'];
$dias = $valores_array['dias'];
/*********************************************************************************/

$consultar_tipo_paciente = "SELECT atencion_id 
FROM atenciones_medicas AS am
INNER JOIN colaboradores AS c
ON am.colaborador_id = c.colaborador_id
WHERE am.pacientes_id = '$pacientes_id' AND am.colaborador_id = '$colaborador_id' AND am.servicio_id = '$servicio_id'";
$result_tipo_paciente = $mysqli->query($consultar_tipo_paciente) or die($mysqli->error);

$tipo_paciente = '';

if ($result_tipo_paciente->num_rows == 0) {
	$tipo_paciente = 'N';
} else {
	$tipo_paciente = 'S';
}

// CONSULTA DATOS DEL PACIENTE
$query = "SELECT CONCAT(nombre, ' ', apellido) AS 'paciente', identidad, expediente AS 'expediente'
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();

$paciente = 0;
$identidad = 0;
$expediente = 0;

if ($result->num_rows > 0) {
	$paciente = $consulta_registro['paciente'];
	$identidad = $consulta_registro['identidad'];
	$expediente = $consulta_registro['expediente'];
}

// CONSULTAMOS SI EXITE LA ATENCION
$query = "SELECT atencion_id 
   FROM atenciones_medicas
   WHERE pacientes_id = '$pacientes_id' AND fecha = '$fecha' AND servicio_id = '$servicio_id'";
$result_existencia = $mysqli->query($query) or die($mysqli->error);

// OBTENER CORRELATIVO
$atencion_id = correlativo('atencion_id', 'atenciones_medicas');

if ($pacientes_id != 0) {
	if ($servicio_id != 0) {
		if ($result_existencia->num_rows < 3) {
			$insert = "INSERT INTO atenciones_medicas (
				atencion_id,
				pacientes_id,
				edad,
				fecha,
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
				paciente,
				servicio_id,
				colaborador_id,
				num_hijos,
				estado,
				fecha_registro
				) VALUES (
				'$atencion_id',
				'$pacientes_id',
				'$anos',
				'$fecha',
				'$antecedentes_medicos_no_psiquiatricos',
				'$hospitalizaciones',
				'$cirugias',
				'$alergias',
				'$antecedentes_medicos_psiquiatricos',
				'$historia_gineco_obstetrica',
				'$medicamentos_previos',
				'$medicamentos_actuales',
				'$legal',
				'$sustancias',
				'$rasgos_personalidad',
				'$informacion_adicional',
				'$pendientes',
				'$diagnostico',
				'$seguimiento',
				'$tipo_paciente',
				'$servicio_id',
				'$colaborador_id',
				'$num_hijos',
				'$estado',
				'$fecha_registro'
				)";

			$query = $mysqli->query($insert) or die($mysqli->error);

			if ($query) {
				$datos = [
					'status' => 'success',
					'title' => 'Success',
					'message' => 'Registro Almacenado Correctamente',
					'type' => 'success',
					'buttonClass' => 'btn-primary',
					'atencion_id' => $atencion_id
				];

				// ACTUALIZAMOS EL ESTADO DE LA AGENDA
				$update = "UPDATE agenda SET status = '$status'
					WHERE agenda_id = '$agenda_id'";
				$mysqli->query($update) or die($mysqli->error);

				// INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
				$historial_numero = historial();
				$estado_historial = 'Agregar';
				$observacion_historial = "Se ha agregado una nueva atención para este paciente: $paciente con identidad n° $identidad";
				$modulo = 'Atención Pacientes';
				$insert = "INSERT INTO historial 
					VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$atencion_id','$colaborador_id','$servicio_id','$fecha','$estado_historial','$observacion_historial','$colaborador_id','$fecha_registro')";

				$mysqli->query($insert) or die($mysqli->error);
				/********************************************/
			} else {
				$datos = [
					'status' => 'error',
					'title' => 'error',
					'message' => 'No se puedo almacenar este registro, los datos son incorrectos por favor corregir',
					'type' => 'error',
					'buttonClass' => 'btn-danger'
				];
			}
		} else {
			$datos = [
				'status' => 'error',
				'title' => 'error',
				'message' => 'Lo sentimos este registro ya existe no se puede almacenar',
				'type' => 'error',
				'buttonClass' => 'btn-danger'
			];
		}
	} else {
		$datos = [
			'status' => 'error',
			'title' => 'error',
			'message' => 'Lo sentimos, debe seleccionar un consultorio antes de continuar, por favor corregir',
			'type' => 'error',
			'buttonClass' => 'btn-danger'
		];
	}
} else {
	$datos = [
		'status' => 'error',
		'title' => 'error',
		'message' => 'Lo sentimos, debe seleccionar un paciente antes de continuar, por favor corregir',
		'type' => 'error',
		'buttonClass' => 'btn-danger'
	];
}

echo json_encode($datos);

$mysqli->close();  // CERRAR CONEXIÓN
