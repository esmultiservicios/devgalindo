<?php
session_start();
include '../funtions.php';

if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
    http_response_code(401);
    exit('Sesión no válida. Inicie sesión nuevamente.');
}

$pacientes_id = filter_input(INPUT_GET, 'paciente_id', FILTER_VALIDATE_INT);
if (!$pacientes_id || $pacientes_id <= 0) {
    http_response_code(400);
    exit('Paciente no válido.');
}

$colaborador_id = (int) $_SESSION['colaborador_id'];
$mysqli = connect_mysqli();
if (!$mysqli || $mysqli->connect_errno) {
    http_response_code(500);
    exit('No se pudo establecer conexión con la base de datos.');
}
$mysqli->set_charset('utf8mb4');

function valorSeguro($valor)
{
    $valor = trim((string) $valor);
    return $valor === '' ? 'No registrado' : $valor;
}

function fechaLegible($fecha)
{
    if (!$fecha) return 'No registrada';
    try {
        $dt = new DateTime($fecha);
        return $dt->format('d/m/Y');
    } catch (Exception $e) {
        return (string) $fecha;
    }
}

function fechaHoraLegible($fecha)
{
    if (!$fecha) return 'No registrada';
    try {
        $dt = new DateTime($fecha);
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        return (string) $fecha;
    }
}

function nombreArchivoSeguro($texto)
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = preg_replace('/[^A-Za-z0-9_-]+/', '_', $texto);
    return trim($texto, '_');
}

function etiquetaGet($clave)
{
    if (!isset($_GET[$clave])) return '';
    $valor = trim((string) $_GET[$clave]);
    if ($valor === '' || mb_strlen($valor, 'UTF-8') > 180) return '';
    return preg_replace('/[\x00-\x1F\x7F]/u', '', $valor);
}

$stmt = $mysqli->prepare(
    'SELECT p.pacientes_id, p.nombre, p.apellido, p.identidad, p.expediente, p.fecha_nacimiento,
            p.localidad, p.red_apoyo, p.terapeuta_actual, p.telefono1,
            ec.nombre AS estado_civil_nombre,
            r.nombre AS religion_nombre,
            pr.nombre AS profesion_nombre,
            esc.nombre AS escolaridad_nombre
     FROM pacientes p
     LEFT JOIN estado_civil ec
            ON ec.estado_civil_id = CAST(NULLIF(p.estado_civil_texto, \'\') AS UNSIGNED)
     LEFT JOIN religion r
            ON r.religion_id = CAST(NULLIF(p.religion_texto, \'\') AS UNSIGNED)
     LEFT JOIN profesion pr
            ON pr.profesion_id = CAST(NULLIF(p.profesion_texto, \'\') AS UNSIGNED)
     LEFT JOIN escolaridad esc
            ON esc.escolaridad_id = CAST(NULLIF(p.escolaridad_texto, \'\') AS UNSIGNED)
     WHERE p.pacientes_id = ?
     LIMIT 1'
);
if (!$stmt) {
    http_response_code(500);
    exit('No se pudo preparar la consulta del paciente.');
}
$stmt->bind_param('i', $pacientes_id);
$stmt->execute();
$resultado = $stmt->get_result();
$paciente = $resultado->fetch_assoc();
$stmt->close();

if (!$paciente) {
    http_response_code(404);
    exit('El paciente no existe.');
}

$stmt = $mysqli->prepare(
    'SELECT atencion_id, edad, fecha,
            antecedentes_medicos_no_psiquiatricos, hospitalizaciones, cirugias, alergias,
            antecedentes_medicos_psiquiatricos, historia_gineco_obstetrica,
            medicamentos_previos, medicamentos_actuales, legal, sustancias,
            rasgos_personalidad, informacion_adicional, pendientes, diagnostico, seguimiento,
            num_hijos, servicio_id, estado, fecha_registro
     FROM atenciones_medicas
     WHERE pacientes_id = ? AND colaborador_id = ?
     ORDER BY fecha DESC, fecha_registro DESC, atencion_id DESC'
);
if (!$stmt) {
    http_response_code(500);
    exit('No se pudo preparar la consulta del expediente clínico.');
}
$stmt->bind_param('ii', $pacientes_id, $colaborador_id);
$stmt->execute();
$resultado = $stmt->get_result();
$atenciones = array();
while ($fila = $resultado->fetch_assoc()) {
    $atenciones[] = $fila;
}
$stmt->close();
$mysqli->close();

if (count($atenciones) === 0) {
    http_response_code(404);
    exit('El paciente no tiene atenciones registradas para este profesional.');
}

$ultima = $atenciones[0];
$nombreCompleto = trim($paciente['nombre'] . ' ' . $paciente['apellido']);

$profesionMostrada = valorSeguro($paciente['profesion_nombre']);
$religionMostrada = valorSeguro($paciente['religion_nombre']);
$estadoCivilMostrado = valorSeguro($paciente['estado_civil_nombre']);
$escolaridadMostrada = valorSeguro($paciente['escolaridad_nombre']);

$nombreClinica = defined('SERVEREMPRESA') ? SERVEREMPRESA : 'Clínica';
$direccionClinica = defined('SERVERDIRECCION') ? SERVERDIRECCION : '';
$telefonoClinica = defined('SERVERTELEFONO') ? SERVERTELEFONO : '';
$emailClinica = defined('SERVEREMAIL') ? SERVEREMAIL : '';

class PdfExpediente
{
    private $pages = array();
    private $content = '';
    private $pageNo = 0;
    private $x = 42;
    private $y = 742;
    private $left = 42;
    private $right = 570;
    private $top = 742;
    private $bottom = 54;
    private $fontSize = 9.5;
    private $clinicName;
    private $clinicMeta;
    private $patientName;
    private $generatedAt;

    public function __construct($clinicName, $clinicMeta, $patientName)
    {
        $this->clinicName = $clinicName;
        $this->clinicMeta = $clinicMeta;
        $this->patientName = $patientName;
        $this->generatedAt = date('d/m/Y H:i');
        $this->newPage();
    }

    private function enc($text)
    {
        $text = str_replace(array("\r\n", "\r"), "\n", (string) $text);
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        return str_replace(array('\\', '(', ')'), array('\\\\', '\\(', '\\)'), $text);
    }

    private function rawText($x, $y, $text, $size = 10, $bold = false, $color = array(0.12, 0.16, 0.20))
    {
        $font = $bold ? '/F2' : '/F1';
        $this->content .= sprintf('%.3F %.3F %.3F rg ', $color[0], $color[1], $color[2]);
        $this->content .= "BT {$font} {$size} Tf 1 0 0 1 " . round($x, 2) . ' ' . round($y, 2) . ' Tm (' . $this->enc($text) . ") Tj ET\n";
    }

    private function rect($x, $y, $w, $h, $fill = null, $stroke = null)
    {
        if ($fill !== null) {
            $this->content .= sprintf('%.3F %.3F %.3F rg ', $fill[0], $fill[1], $fill[2]);
        }
        if ($stroke !== null) {
            $this->content .= sprintf('%.3F %.3F %.3F RG ', $stroke[0], $stroke[1], $stroke[2]);
        }
        $op = $fill !== null && $stroke !== null ? 'B' : ($fill !== null ? 'f' : 'S');
        $this->content .= round($x, 2) . ' ' . round($y, 2) . ' ' . round($w, 2) . ' ' . round($h, 2) . " re {$op}\n";
    }

    private function line($x1, $y1, $x2, $y2, $gray = 0.85)
    {
        $this->content .= sprintf('%.3F %.3F %.3F RG ', $gray, $gray, $gray);
        $this->content .= round($x1, 2) . ' ' . round($y1, 2) . ' m ' . round($x2, 2) . ' ' . round($y2, 2) . " l S\n";
    }

    private function header()
    {
        $this->rect(0, 754, 612, 38, array(0.07, 0.39, 0.45), null);
        $white = array(1, 1, 1);
        $this->rawText(42, 770, $this->clinicName, 15, true, $white);
        $this->rawText(42, 758, 'Expediente clínico integral', 8.5, false, $white);
        $this->rawText(570 - min(170, strlen($this->patientName) * 4.2), 770, $this->patientName, 8.7, true, $white);
        if ($this->clinicMeta !== '') {
            $this->rawText(42, 739, $this->clinicMeta, 7.8, false);
        }
        $this->line(42, 729, 570, 729, 0.82);
        $this->y = 712;
    }

    private function footer()
    {
        $this->line(42, 40, 570, 40, 0.86);
        $this->rawText(42, 26, 'Documento generado desde el expediente clínico electrónico · ' . $this->generatedAt, 7.2, false);
        $this->rawText(520, 26, 'Página ' . $this->pageNo, 7.2, false);
    }

    public function newPage()
    {
        if ($this->pageNo > 0) {
            $this->footer();
            $this->pages[] = $this->content;
        }
        $this->pageNo++;
        $this->content = '';
        $this->header();
    }

    private function ensure($height)
    {
        if ($this->y - $height < $this->bottom) {
            $this->newPage();
        }
    }

    private function splitWords($text, $maxChars)
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') return array('');
        $words = preg_split('/\s+/u', $text);
        $lines = array();
        $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if (mb_strlen($candidate, 'UTF-8') <= $maxChars) {
                $line = $candidate;
            } else {
                if ($line !== '') $lines[] = $line;
                while (mb_strlen($word, 'UTF-8') > $maxChars) {
                    $lines[] = mb_substr($word, 0, $maxChars, 'UTF-8');
                    $word = mb_substr($word, $maxChars, null, 'UTF-8');
                }
                $line = $word;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines;
    }

    private function wrappedLines($text, $maxChars)
    {
        $parts = preg_split('/\r\n|\r|\n/u', (string) $text);
        $lines = array();
        foreach ($parts as $part) {
            $wrapped = $this->splitWords($part, $maxChars);
            foreach ($wrapped as $line) $lines[] = $line;
        }
        return count($lines) ? $lines : array('');
    }

    public function title($title, $subtitle = '')
    {
        $height = $subtitle !== '' ? 46 : 34;
        $this->ensure($height + 10);
        $this->rect(42, $this->y - $height + 8, 528, $height, array(0.95, 0.98, 0.985), array(0.82, 0.89, 0.91));
        $this->rect(42, $this->y - $height + 8, 4, $height, array(0.07, 0.55, 0.62), null);
        $this->rawText(55, $this->y - 8, $title, 12, true);
        if ($subtitle !== '') {
            $this->rawText(55, $this->y - 23, $subtitle, 7.8, false);
        }
        $this->y -= ($height + 6);
    }

    public function twoColumnRows($rows)
    {
        $colW = 258;
        $gap = 12;
        for ($i = 0; $i < count($rows); $i += 2) {
            $pair = array($rows[$i], isset($rows[$i + 1]) ? $rows[$i + 1] : null);
            $maxLines = 1;
            $prepared = array();
            foreach ($pair as $item) {
                if ($item === null) {
                    $prepared[] = null;
                    continue;
                }
                $lines = $this->wrappedLines($item[1], 40);
                $maxLines = max($maxLines, count($lines));
                $prepared[] = array($item[0], $lines);
            }
            $height = 30 + (($maxLines - 1) * 10);
            $this->ensure($height + 4);
            for ($c = 0; $c < 2; $c++) {
                if ($prepared[$c] === null) continue;
                $x = 42 + ($c * ($colW + $gap));
                $this->rect($x, $this->y - $height + 4, $colW, $height, array(0.985, 0.99, 0.993), array(0.89, 0.92, 0.94));
                $this->rawText($x + 9, $this->y - 9, $prepared[$c][0], 7.4, true);
                $ty = $this->y - 21;
                foreach ($prepared[$c][1] as $line) {
                    $this->rawText($x + 9, $ty, $line, 8.6, false);
                    $ty -= 10;
                }
            }
            $this->y -= ($height + 7);
        }
    }

    public function field($label, $value)
    {
        $value = trim((string) $value);
        if ($value === '') $value = 'No hay información registrada.';
        $lines = $this->wrappedLines($value, 92);
        $height = 31 + max(0, count($lines) - 1) * 11;
        $this->ensure($height + 5);
        $this->rawText(42, $this->y - 7, $label, 8, true);
        $ty = $this->y - 20;
        foreach ($lines as $line) {
            $this->rawText(42, $ty, $line, 9.2, false);
            $ty -= 11;
        }
        $this->line(42, $this->y - $height + 7, 570, $this->y - $height + 7, 0.91);
        $this->y -= $height;
    }

    public function historyItem($date, $diagnosis, $followup)
    {
        $diagLines = $this->wrappedLines($diagnosis === '' ? 'No hay información registrada.' : $diagnosis, 88);
        $followLines = $this->wrappedLines($followup === '' ? 'No hay información registrada.' : $followup, 88);
        $height = 45 + count($diagLines) * 10 + count($followLines) * 10;
        if ($height > 610) $height = 610;
        $this->ensure(min($height, 160));

        $this->rect(42, $this->y - 22, 528, 22, array(0.965, 0.985, 0.99), array(0.85, 0.91, 0.93));
        $this->rawText(51, $this->y - 14, $date, 8.4, true);
        $this->rawText(455, $this->y - 14, 'Evolución clínica', 7.8, false);
        $this->y -= 31;

        $this->field('Diagnóstico', $diagnosis);
        $this->field('Seguimiento / evolución registrada', $followup);
        $this->y -= 3;
    }

    public function output($filename, $inline = true)
    {
        $this->footer();
        $this->pages[] = $this->content;

        $objects = array();
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = array();
        $pageObjectBase = 5;
        $contentObjectBase = $pageObjectBase + count($this->pages);
        for ($i = 0; $i < count($this->pages); $i++) {
            $kids[] = ($pageObjectBase + $i) . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($this->pages) . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        foreach ($this->pages as $i => $stream) {
            $pageObj = $pageObjectBase + $i;
            $contentObj = $contentObjectBase + $i;
            $objects[$pageObj] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentObj . ' 0 R >>';
            $objects[$contentObj] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = array(0 => 0);
        foreach ($objects as $num => $obj) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $obj . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $maxObj = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObj; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", isset($offsets[$i]) ? $offsets[$i] : 0);
        }
        $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        header('Content-Type: application/pdf');
        $disposition = $inline ? 'inline' : 'attachment';
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
        exit;
    }
}

$metaClinica = implode('  ·  ', array_filter(array($direccionClinica, $telefonoClinica, $emailClinica)));
$pdf = new PdfExpediente($nombreClinica, $metaClinica, $nombreCompleto);

$pdf->title('1. Datos personales del paciente', 'Información vigente registrada en el expediente.');
$pdf->twoColumnRows(array(
    array('Nombre completo', valorSeguro($nombreCompleto)),
    array('Expediente', valorSeguro($paciente['expediente'])),
    array('Identidad', valorSeguro($paciente['identidad'])),
    array('Fecha de nacimiento', fechaLegible($paciente['fecha_nacimiento'])),
    array('Edad última atención', valorSeguro($ultima['edad']) . ' años'),
    array('Teléfono', valorSeguro($paciente['telefono1'])),
    array('Dirección / procedencia', valorSeguro($paciente['localidad'])),
    array('Estado civil', $estadoCivilMostrado),
    array('Religión', $religionMostrada),
    array('Profesión', $profesionMostrada),
    array('Escolaridad', $escolaridadMostrada),
    array('Número de hijos', valorSeguro($ultima['num_hijos'])),
    array('Red de apoyo', valorSeguro($paciente['red_apoyo'])),
    array('Terapeuta actual', valorSeguro($paciente['terapeuta_actual']))
));

$pdf->title('2. Historia clínica actualizada', 'Contenido consolidado de la última atención registrada: ' . fechaHoraLegible($ultima['fecha_registro']) . '.');
$camposClinicos = array(
    'Antecedentes médicos no psiquiátricos' => $ultima['antecedentes_medicos_no_psiquiatricos'],
    'Hospitalizaciones' => $ultima['hospitalizaciones'],
    'Cirugías' => $ultima['cirugias'],
    'Alergias' => $ultima['alergias'],
    'Antecedentes médicos psiquiátricos' => $ultima['antecedentes_medicos_psiquiatricos'],
    'Historia gineco-obstétrica' => $ultima['historia_gineco_obstetrica'],
    'Medicamentos previos' => $ultima['medicamentos_previos'],
    'Medicamentos actuales' => $ultima['medicamentos_actuales'],
    'Información legal' => $ultima['legal'],
    'Sustancias' => $ultima['sustancias'],
    'Rasgos de personalidad relevantes' => $ultima['rasgos_personalidad'],
    'Información adicional' => $ultima['informacion_adicional'],
    'Pendientes' => $ultima['pendientes']
);
foreach ($camposClinicos as $label => $value) {
    $pdf->field($label, $value);
}

$pdf->title('3. Evolución diagnóstica y seguimiento', 'Atenciones ordenadas por fecha, de la más reciente a la más antigua. Total: ' . count($atenciones) . '.');
foreach ($atenciones as $atencion) {
    $pdf->historyItem(
        fechaHoraLegible($atencion['fecha_registro'] ?: $atencion['fecha']),
        trim((string) $atencion['diagnostico']),
        trim((string) $atencion['seguimiento'])
    );
}

$archivo = 'Expediente_' . nombreArchivoSeguro($nombreCompleto) . '_' . date('Ymd_His') . '.pdf';
$modoDescarga = isset($_GET['download']) && $_GET['download'] === '1';
$pdf->output($archivo, !$modoDescarga);