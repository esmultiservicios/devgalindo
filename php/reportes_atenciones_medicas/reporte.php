<?php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

// Incluye la librería PHPExcel
include "../../PHPExcel/Classes/PHPExcel.php";

// Variables de entrada
$desde = $_GET['desde'];
$hasta = $_GET['hasta'];
$colaborador = $_GET['colaborador'];
$usuario = $_SESSION['colaborador_id'];

$mes = nombremes(date("m", strtotime($desde)));
$mes1 = nombremes(date("m", strtotime($hasta)));
$año = date("Y", strtotime($desde));
$año2 = date("Y", strtotime($hasta));

// Construir la consulta SQL
$where = $colaborador ? "WHERE am.colaborador_id = '$colaborador' AND am.fecha BETWEEN '$desde' AND '$hasta'" : "WHERE am.fecha BETWEEN '$desde' AND '$hasta'";
$query = "
    SELECT am.atencion_id AS 'atencion_id', DATE_FORMAT(am.fecha, '%d/%m/%Y') AS 'fecha',
           CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad AS 'identidad', am.antecedentes AS 'antecedentes',
           am.historia_clinica AS 'historia_clinica', am.examen_fisico AS 'examen_fisico', am.seguimiento AS 'seguimiento',
           CONCAT(c.nombre,' ',c.apellido) AS 'colaborador', s.nombre AS 'servicio', am.pacientes_id As 'pacientes_id',
           am.servicio_id AS 'servicio_id', am.colaborador_id AS 'colaborador_id', am.fecha AS 'fecha_consulta',
           (CASE WHEN p.genero = 'H' THEN 'X' ELSE '' END) AS 'h',
           (CASE WHEN p.genero = 'M' THEN 'X' ELSE '' END) AS 'm',
           (CASE WHEN am.paciente = 'N' THEN 'X' ELSE '' END) AS 'n',
           (CASE WHEN am.paciente = 'S' THEN 'X' ELSE '' END) AS 's',
           d.nombre AS 'departamento', m.nombre AS 'municipio', p.localidad AS 'localidad'
    FROM atenciones_medicas AS am
    INNER JOIN pacientes AS p ON am.pacientes_id = p.pacientes_id
    INNER JOIN colaboradores AS c ON am.colaborador_id = c.colaborador_id
    INNER JOIN servicios AS s ON am.servicio_id = s.servicio_id
    LEFT JOIN departamentos AS d ON p.departamento_id = d.departamento_id
    LEFT JOIN municipios AS m ON p.municipio_id = m.municipio_id
    $where
    ORDER BY am.fecha ASC
";
$result = $mysqli->query($query);

// Obtener nombre de empresa
$query_empresa = "
    SELECT e.nombre AS 'empresa'
    FROM users AS u
    INNER JOIN empresa AS e ON u.empresa_id = e.empresa_id
    WHERE u.colaborador_id = '$usuario'
";
$result_empresa = $mysqli->query($query_empresa) or die($mysqli->error);
$empresa_nombre = $result_empresa->num_rows > 0 ? $result_empresa->fetch_assoc()['empresa'] : '';

// Crear nueva instancia de PHPExcel
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("ING. EDWIN VELASQUEZ")->setTitle("Reporte Atenciones");

// Estilos
function createStyle($bold = false, $size = 10, $align = PHPExcel_Style_Alignment::HORIZONTAL_CENTER, $fillColor = null, $borders = true) {
    return (new PHPExcel_Style())->applyFromArray(array(
        'alignment' => array(
            'wrap' => true,
            'horizontal' => $align
        ),
        'font' => array(
            'bold' => $bold,
            'size' => $size
        ),
        'fill' => $fillColor ? array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => $fillColor)
        ) : array(),
        'borders' => $borders ? array(
            'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ) : array()
    ));
}

$titulo = createStyle(true, 12);
$subtitulo = createStyle(true, 11, PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'bfbfbf');
$texto = createStyle(true, 10);
$bordes = createStyle(false, 10, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Crear hoja de Excel
$objPHPExcel->createSheet(0);
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle("Reporte Atenciones");

// Configurar página
$objPHPExcel->getActiveSheet()->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER)
    ->setFitToPage(true)
    ->setFitToWidth(1)
    ->setFitToHeight(0);
$objPHPExcel->getActiveSheet()->freezePane('D6');
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.5 / 2.54)
    ->setBottom(1.2 / 2.54)
    ->setLeft(0.5 / 2.54)
    ->setRight(0.5 / 2.54);

// Incluir imagen
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('../../img/logo.png')
    ->setWidth(200)
    ->setHeight(60)
    ->setCoordinates('A1')
    ->setWorksheet($objPHPExcel->getActiveSheet());

// Establecer títulos de impresión
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

// Agregar encabezados
$fila = 1;
$objPHPExcel->getActiveSheet()->setSharedStyle($bordes, "A3:R3");
$objPHPExcel->getActiveSheet()->SetCellValue("A$fila", $empresa_nombre);
$objPHPExcel->getActiveSheet()->mergeCells("A$fila:R$fila");
$objPHPExcel->getActiveSheet()->setSharedStyle($titulo, "A$fila:R$fila");

$fila++;
$objPHPExcel->getActiveSheet()->setSharedStyle($bordes, "A$fila:R$fila");
$objPHPExcel->getActiveSheet()->SetCellValue("A$fila", "Reporte de Atenciones");
$objPHPExcel->getActiveSheet()->mergeCells("A$fila:R$fila");
$objPHPExcel->getActiveSheet()->setSharedStyle($titulo, "A$fila:R$fila");

$fila++;
$objPHPExcel->getActiveSheet()->setSharedStyle($bordes, "A$fila:R$fila");
$objPHPExcel->getActiveSheet()->SetCellValue("A$fila", "Desde: $mes $año Hasta: $mes1 $año2");
$objPHPExcel->getActiveSheet()->mergeCells("A$fila:R$fila");
$objPHPExcel->getActiveSheet()->setSharedStyle($titulo, "A$fila:R$fila");

// Encabezado de columnas
$headers = [
    'N°' => 5, 'Fecha' => 13, 'Nombre del Usuario' => 45, 'Identidad' => 25, 'Genero' => 8,
    'Paciente' => 22, 'Procedencia' => 70, 'Antecedentes' => 70, 'Historia Clinica' => 70,
    'Examen Físico' => 70, 'Seguimiento' => 70, 'Colaborador' => 20, 'Servicio' => 20, 'Atención' => 30,
    'Departamento' => 20, 'Municipio' => 20, 'Localidad' => 30
];
$fila++;
foreach ($headers as $header => $width) {
    $col = chr(65 + array_search($header, array_keys($headers)));
    $objPHPExcel->getActiveSheet()->SetCellValue("$col$fila", $header);
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width);
}
$objPHPExcel->getActiveSheet()->setSharedStyle($subtitulo, "A$fila:R$fila");

// Rellenar datos
$fila++;
$contador = 1;
while ($row = $result->fetch_assoc()) {
    $objPHPExcel->getActiveSheet()->SetCellValue("A$fila", $contador++);
    $objPHPExcel->getActiveSheet()->SetCellValue("B$fila", $row['fecha']);
    $objPHPExcel->getActiveSheet()->SetCellValue("C$fila", $row['paciente']);
    $objPHPExcel->getActiveSheet()->SetCellValue("D$fila", $row['identidad']);
    $objPHPExcel->getActiveSheet()->SetCellValue("E$fila", $row['h'] ? "X" : ($row['m'] ? "X" : ""));
    $objPHPExcel->getActiveSheet()->SetCellValue("F$fila", $row['n'] ? "X" : ($row['s'] ? "X" : ""));
    $objPHPExcel->getActiveSheet()->SetCellValue("G$fila", $row['antecedentes']);
    $objPHPExcel->getActiveSheet()->SetCellValue("H$fila", $row['historia_clinica']);
    $objPHPExcel->getActiveSheet()->SetCellValue("I$fila", $row['examen_fisico']);
    $objPHPExcel->getActiveSheet()->SetCellValue("J$fila", $row['seguimiento']);
    $objPHPExcel->getActiveSheet()->SetCellValue("K$fila", $row['colaborador']);
    $objPHPExcel->getActiveSheet()->SetCellValue("L$fila", $row['servicio']);
    $objPHPExcel->getActiveSheet()->SetCellValue("M$fila", $row['pacientes_id']);
    $objPHPExcel->getActiveSheet()->SetCellValue("N$fila", $row['departamento']);
    $objPHPExcel->getActiveSheet()->SetCellValue("O$fila", $row['municipio']);
    $objPHPExcel->getActiveSheet()->SetCellValue("P$fila", $row['localidad']);
    $fila++;
}

// Guardar archivo Excel
$filename = "Reporte_Atenciones_" . date("Ymd_His") . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

$mysqli->close();