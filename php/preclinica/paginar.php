<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function escaparHtml($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function enlazarParametros($stmt, $tipos, array &$parametros)
{
    if ($tipos === '') return;
    $referencias = array($tipos);
    foreach ($parametros as $indice => $valor) {
        $referencias[] = &$parametros[$indice];
    }
    call_user_func_array(array($stmt, 'bind_param'), $referencias);
}

$mysqli = null;
$stmtCount = null;
$stmtData = null;

try {
    $paginaActual = max(1, (int)($_POST['partida'] ?? 1));
    $fechai = trim((string)($_POST['fechai'] ?? date('Y-m-d')));
    $fechaf = trim((string)($_POST['fechaf'] ?? date('Y-m-d')));
    $dato = trim((string)($_POST['dato'] ?? ''));
    $unidad = isset($_POST['unidad']) && $_POST['unidad'] !== '' ? (int)$_POST['unidad'] : 0;
    $colaborador = isset($_POST['colaborador']) && $_POST['colaborador'] !== '' ? (int)$_POST['colaborador'] : 0;

    $fechaInicial = DateTime::createFromFormat('Y-m-d', $fechai);
    $fechaFinal = DateTime::createFromFormat('Y-m-d', $fechaf);
    if (!$fechaInicial || $fechaInicial->format('Y-m-d') !== $fechai || !$fechaFinal || $fechaFinal->format('Y-m-d') !== $fechaf) {
        throw new Exception('El rango de fechas no es válido.');
    }
    if ($fechaInicial > $fechaFinal) throw new Exception('La fecha inicial no puede ser mayor que la fecha final.');

    $fechaFinalExclusiva = clone $fechaFinal;
    $fechaFinalExclusiva->modify('+1 day');
    $desde = $fechaInicial->format('Y-m-d 00:00:00');
    $hasta = $fechaFinalExclusiva->format('Y-m-d 00:00:00');

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $baseFrom = "
        FROM agenda AS ag
        INNER JOIN pacientes AS p ON ag.pacientes_id = p.pacientes_id
        INNER JOIN colaboradores AS c ON ag.colaborador_id = c.colaborador_id
        INNER JOIN servicios AS s ON ag.servicio_id = s.servicio_id
        INNER JOIN puesto_colaboradores AS pc ON c.puesto_id = pc.puesto_id
    ";

    $condiciones = array('ag.preclinica = 0', 'ag.fecha_cita >= ?', 'ag.fecha_cita < ?');
    $tipos = 'ss';
    $parametros = array($desde, $hasta);

    if ($colaborador > 0) {
        $condiciones[] = 'ag.colaborador_id = ?';
        $tipos .= 'i';
        $parametros[] = $colaborador;
    }
    if ($unidad > 0) {
        $condiciones[] = 'pc.puesto_id = ?';
        $tipos .= 'i';
        $parametros[] = $unidad;
    }
    if ($dato !== '') {
        $condiciones[] = "(CAST(p.expediente AS CHAR) LIKE ? OR CONCAT(p.nombre, ' ', p.apellido) LIKE ? OR CONCAT(p.apellido, ' ', p.nombre) LIKE ? OR p.apellido LIKE ? OR p.identidad LIKE ?)";
        $contiene = '%' . $dato . '%';
        $inicia = $dato . '%';
        $tipos .= 'sssss';
        array_push($parametros, $contiene, $contiene, $contiene, $inicia, $inicia);
    }

    $where = ' WHERE ' . implode(' AND ', $condiciones);
    $stmtCount = $mysqli->prepare('SELECT COUNT(*) AS total ' . $baseFrom . $where);
    if (!$stmtCount) throw new Exception('No se pudo preparar el conteo: ' . $mysqli->error);
    $parametrosCount = $parametros;
    enlazarParametros($stmtCount, $tipos, $parametrosCount);
    if (!$stmtCount->execute()) throw new Exception('No se pudo contar los registros: ' . $stmtCount->error);
    $totalRegistros = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);

    $registrosPorPagina = 25;
    $totalPaginas = max(1, (int)ceil($totalRegistros / $registrosPorPagina));
    if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;
    $offset = ($paginaActual - 1) * $registrosPorPagina;

    $paginacion = '';
    if ($paginaActual > 1) {
        $paginacion .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
        $paginacion .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
    }
    if ($paginaActual < $totalPaginas) {
        $paginacion .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $totalPaginas . '</a></li>';
        $paginacion .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $totalPaginas . ');void(0);">Última</a></li>';
    }

    $sql = "SELECT ag.pacientes_id, ag.agenda_id, p.expediente, p.identidad,
            CONCAT(p.apellido, ' ', p.nombre) AS paciente,
            DATE_FORMAT(ag.fecha_cita, '%d/%m/%Y') AS fecha_cita,
            ag.hora, CONCAT(c.nombre, ' ', c.apellido) AS colaborador,
            s.nombre AS servicio, ag.observacion, ag.comentario,
            pc.puesto_id, ag.servicio_id
            " . $baseFrom . $where . "
            ORDER BY ag.fecha_cita ASC, ag.hora ASC
            LIMIT ?, ?";
    $stmtData = $mysqli->prepare($sql);
    if (!$stmtData) throw new Exception('No se pudo preparar la consulta: ' . $mysqli->error);
    $tiposData = $tipos . 'ii';
    $parametrosData = array_merge($parametros, array($offset, $registrosPorPagina));
    enlazarParametros($stmtData, $tiposData, $parametrosData);
    if (!$stmtData->execute()) throw new Exception('No se pudieron consultar los registros: ' . $stmtData->error);
    $resultado = $stmtData->get_result();

    $tabla = '<table class="table table-striped table-condensed table-hover"><tr>
        <th width="3.33%">No.</th><th width="8.33%">Expediente</th><th width="8.33%">Identidad</th>
        <th width="11.33%">Nombre</th><th width="5.33%">Fecha Cita</th><th width="3.33%">Hora</th>
        <th width="8.33%">Profesional</th><th width="10.33%">Servicio</th><th width="10.33%">Observación</th>
        <th width="10.33%">Comentario</th><th width="8.33%">Registrar</th><th width="8.33%">Ausencias</th></tr>';

    $numeroFila = $offset + 1;
    while ($fila = $resultado->fetch_assoc()) {
        $expediente = (int)$fila['expediente'] === 0 ? 'TEMP' : (int)$fila['expediente'];
        $agendaId = (int)$fila['agenda_id'];
        $pacienteId = (int)$fila['pacientes_id'];
        $expedienteNumero = (int)$fila['expediente'];
        $tabla .= '<tr><td>' . $numeroFila . '</td><td>' . escaparHtml($expediente) . '</td><td>' . escaparHtml($fila['identidad']) . '</td><td>' . escaparHtml($fila['paciente']) . '</td><td>' . escaparHtml($fila['fecha_cita']) . '</td><td>' . escaparHtml($fila['hora']) . '</td><td>' . escaparHtml($fila['colaborador']) . '</td><td>' . escaparHtml($fila['servicio']) . '</td><td>' . escaparHtml($fila['observacion']) . '</td><td>' . escaparHtml($fila['comentario']) . '</td><td><a class="btn btn-secondary ml-2" href="javascript:editarRegistro(' . $agendaId . ',' . $expedienteNumero . ');void(0);" title="Agregar Preclínica"><i class="fas fa-notes-medical fa-lg"></i> Preclínica</a></td><td><a class="btn btn-secondary ml-2" href="javascript:nosePresentoRegistro(' . $agendaId . ',' . $pacienteId . ');void(0);" title="Usuario no se presentó a su cita"><i class="fas fa-times-circle fa-lg"></i> Ausencia</a></td></tr>';
        $numeroFila++;
    }
    if ($totalRegistros === 0) {
        $tabla .= '<tr><td colspan="12" style="color:#C7030D">No se encontraron resultados</td></tr>';
    } else {
        $tabla .= '<tr><td colspan="12"><b><p align="center">Total de registros encontrados ' . $totalRegistros . '</p></b></td></tr>';
    }
    $tabla .= '</table>';
    echo json_encode(array($tabla, $paginacion), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error paginar.php: ' . $e->getMessage());
    echo json_encode(array('<div class="alert alert-danger">No se pudieron consultar los registros.</div>', ''), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmtCount instanceof mysqli_stmt) $stmtCount->close();
    if ($stmtData instanceof mysqli_stmt) $stmtData->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}