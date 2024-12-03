<?php
session_start();
include '../funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

if (isset($_POST['paciente_consulta'])) {  // COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if ($_POST['paciente_consulta'] == '') {
		$pacientes_id = 0;
	} else {
		$pacientes_id = $_POST['paciente_consulta'];
	}
} else {
	$pacientes_id = 0;
}

$fecha = $_POST['fecha'];
$servicio_id = $_POST['servicio_id'];

$num_hijos = isset($_POST['num_hijos']) && $_POST['num_hijos'] !== '' ? intval($_POST['num_hijos']) : 0;

$localidad = cleanStringStrtolower($_POST['procedencia']);

$identidad = $_POST['identidad'];
$fecha_nac = $_POST['fecha_nac'];
$telefono1 = $_POST['telefono1'];

$red_apoyo = cleanStringStrtolower($_POST['red_apoyo']);
$terapeuta_actual = cleanStringStrtolower($_POST['terapeuta_actual']);

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

$colaborador_id = $_SESSION['colaborador_id'];
$hora = date('H:i', strtotime('00:00'));
$fecha_cita = date('Y-m-d H:i:s', strtotime($fecha));
$fecha_cita_end = date('Y-m-d H:i:s', strtotime($fecha));
$fecha_registro = date('Y-m-d H:i:s');
$status = 1;  // ESTADO PARA LA AGENDA DEL PACIENTE
$estado = 1;  // ESTADO DE LA ATENCION DEL PACIENTE PARA LA FACTURACION 1. PENDIENTE 2. PAGADA

/*********************************************************************************************************************************************************************/
// CONSULTAMOS SI EL PACIENTE ES NUEVO O SUBSIGUIENTE
$consultar_expediente = "SELECT a.agenda_id
	FROM agenda AS a 
	INNER JOIN colaboradores AS c
	ON a.colaborador_id = c.colaborador_id
	WHERE a.pacientes_id = '$pacientes_id' AND c.colaborador_id = '$colaborador_id' AND a.status = 1";
$result = $mysqli->query($consultar_expediente) or die($mysqli->error);

$tipo_paciente = 'S';
$color = '#0071c5';  // AZUL;

if ($result_fecha_nacimiento->num_rows > 0) {
	$tipo_paciente = 'N';
	$color = '#008000';  // VERDE;
}
/*********************************************************************************************************************************************************************/

// CONSULTA DATOS DEL PACIENTE
$query = "SELECT CONCAT(nombre, ' ', apellido) AS 'paciente', identidad, expediente AS 'expediente'
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();

$paciente_nombre = '';
$identidad = '';
$expediente = '';

if ($result->num_rows > 0) {
	$paciente_nombre = $consulta_registro['paciente'];
	$identidad = $consulta_registro['identidad'];
	$expediente = $consulta_registro['expediente'];
}

// CONSULTAMOS SI EXITE LA ATENCION
$query = "SELECT atencion_id 
   FROM atenciones_medicas
   WHERE pacientes_id = '$pacientes_id' AND fecha = '$fecha' AND servicio_id = '$servicio_id'";
$result_existencia = $mysqli->query($query) or die($mysqli->error);

// VALIDAMOS SI EXISTE AGENDA PARA ESTE USUARIO
$query_agenda = "SELECT agenda_id
   FROM agenda
   WHERE CAST(fecha_cita AS DATE) = '$fecha' AND servicio_id = '$servicio_id' AND colaborador_id = '$colaborador_id' AND pacientes_id = '$pacientes_id'";
$result_agenda = $mysqli->query($query_agenda) or die($mysqli->error);

// OBTENER CORRELATIVO
$atencion_id = correlativo('atencion_id', 'atenciones_medicas');

if ($pacientes_id != 0) {
	if ($servicio_id != 0) {
		if ($result_agenda->num_rows < 3) {
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

					$observacion = 'Usuario agregado de forma manual';
					$comentario = '';
					$preclinica = 1;
					$postclinica = 0;
					$reprogramo = 2;  // 1. Sí 2. No
					$status_id = 0;

					/*********************************************************************************************************************************************************************/
					// AGREGAMOS LA AGENDA DEL PACINETE
					$correlativo_agenda = correlativo('agenda_id', 'agenda');
					$insert_agenda = "INSERT INTO agenda VALUES ('$correlativo_agenda','$pacientes_id','$expediente','$colaborador_id','$hora','$fecha_cita','$fecha_cita_end','$fecha_registro','$status','$color','$observacion','$colaborador_id','$servicio_id','$comentario','$preclinica','$postclinica','$reprogramo','$tipo_paciente','$status_id')";

					$mysqli->query($insert_agenda) or die($mysqli->error);
					/*********************************************************************************************************************************************************************/
					/*********************************************************************************************************************************************************************/
					// INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
					$historial_numero = historial();
					$estado_historial = 'Agregar';
					$observacion_historial = "Se ha agregado una nueva atención para este paciente: $paciente_nombre con identidad n° $identidad";
					$modulo = 'Atención Pacientes';
					$insert = "INSERT INTO historial 
						VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$correlativo_agenda','$colaborador_id','$servicio_id','$fecha','$estado_historial','$observacion_historial','$colaborador_id','$fecha_registro')";
					$mysqli->query($insert) or die($mysqli->error);
					/*********************************************************************************************************************************************************************/
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
				'message' => 'Lo sentimos, este paciente ya cuenta con agenda almacenada para este día, por favor, revise sus registros pendientes',
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
