<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$mysqli = null;
$stmtCount = null;
$stmtData = null;

try {
    $paginaActual = max(1, (int)($_POST['partida'] ?? 1));
    $fechai = trim((string)($_POST['fechai'] ?? date('Y-m-d')));
    $fechaf = trim((string)($_POST['fechaf'] ?? date('Y-m-d')));
    $dato = trim((string)($_POST['dato'] ?? ''));
    $colaborador = isset($_POST['colaborador']) && $_POST['colaborador'] !== '' ? (int)$_POST['colaborador'] : 0;

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

    $where = " WHERE ag.preclinica = 0 ";
    $types = '';
    $params = array();

    if ($colaborador > 0) {
        $where .= " AND CAST(ag.fecha_cita AS DATE) BETWEEN ? AND ? AND ag.colaborador_id = ? ";
        $types = 'ssi';
        $params = array($fechai, $fechaf, $colaborador);
    } elseif ($dato !== '') {
        $where .= " AND (
            CAST(p.expediente AS CHAR) LIKE ?
            OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
            OR p.apellido LIKE ?
            OR p.identidad LIKE ?
        ) ";
        $like = '%' . $dato . '%';
        $inicio = $dato . '%';
        $types = 'ssss';
        $params = array($like, $like, $inicio, $inicio);
    } else {
        $where .= " AND CAST(ag.fecha_cita AS DATE) BETWEEN ? AND ? ";
        $types = 'ss';
        $params = array($fechai, $fechaf);
    }

    $stmtCount = $mysqli->prepare("SELECT COUNT(*) AS total " . $baseFrom . $where);
    if (!$stmtCount) throw new Exception($mysqli->error);
    $stmtCount->bind_param($types, ...$params);
    if (!$stmtCount->execute()) throw new Exception($stmtCount->error);
    $nroProductos = (int)$stmtCount->get_result()->fetch_assoc()['total'];

    $nroLotes = 25;
    $nroPaginas = max(1, (int)ceil($nroProductos / $nroLotes));
    if ($paginaActual > $nroPaginas) $paginaActual = $nroPaginas;
    $limit = $nroLotes * ($paginaActual - 1);

    $lista = '';
    if ($paginaActual > 1) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
    }
    if ($paginaActual < $nroPaginas) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
    }

    $sql = "SELECT
                ag.pacientes_id, ag.agenda_id, p.expediente, p.identidad,
                CONCAT(p.apellido, ' ', p.nombre) AS paciente,
                DATE_FORMAT(CAST(ag.fecha_cita AS DATE), '%d/%m/%Y') AS fecha_cita,
                ag.hora, CONCAT(c.nombre, ' ', c.apellido) AS colaborador,
                s.nombre AS servicio, ag.observacion, ag.comentario,
                CAST(ag.fecha_cita AS DATE) AS fecha,
                pc.puesto_id, ag.servicio_id
            " . $baseFrom . $where . "
            ORDER BY CAST(ag.fecha_cita AS DATE), ag.hora ASC
            LIMIT ?, ?";

    $stmtData = $mysqli->prepare($sql);
    if (!$stmtData) throw new Exception($mysqli->error);

    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, array($limit, $nroLotes));
    $stmtData->bind_param($dataTypes, ...$dataParams);
    if (!$stmtData->execute()) throw new Exception($stmtData->error);

    $resultado = $stmtData->get_result();

    $tabla = '<table class="table table-striped table-condensed table-hover">
        <tr>
            <th width="3.33%">No.</th>
            <th width="8.33%">Expediente</th>
            <th width="8.33%">Identidad</th>
            <th width="11.33%">Nombre</th>
            <th width="5.33%">Fecha Cita</th>
            <th width="3.33%">Hora</th>
            <th width="8.33%">Profesional</th>
            <th width="10.33%">Servicio</th>
            <th width="10.33%">Observación</th>
            <th width="10.33%">Comentario</th>
            <th width="8.33%">Registrar</th>
            <th width="8.33%">Ausencias</th>
        </tr>';

    $i = $limit + 1;

    while ($fila = $resultado->fetch_assoc()) {
        $expediente = (int)$fila['expediente'] === 0 ? 'TEMP' : (int)$fila['expediente'];
        $agendaId = (int)$fila['agenda_id'];
        $pacienteId = (int)$fila['pacientes_id'];
        $expedienteNum = (int)$fila['expediente'];

        $tabla .= '<tr>
            <td>' . $i . '</td>
            <td>' . h($expediente) . '</td>
            <td>' . h($fila['identidad']) . '</td>
            <td>' . h($fila['paciente']) . '</td>
            <td>' . h($fila['fecha_cita']) . '</td>
            <td>' . h($fila['hora']) . '</td>
            <td>' . h($fila['colaborador']) . '</td>
            <td>' . h($fila['servicio']) . '</td>
            <td>' . h($fila['observacion']) . '</td>
            <td>' . h($fila['comentario']) . '</td>
            <td>
                <a class="btn btn-secondary ml-2" href="javascript:editarRegistro(' . $agendaId . ',' . $expedienteNum . ');void(0);" title="Agregar Preclínica">
                    <i class="fas fa-notes-medical fa-lg"></i> Preclínica
                </a>
            </td>
            <td>
                <a class="btn btn-secondary ml-2" href="javascript:nosePresentoRegistro(' . $agendaId . ',' . $pacienteId . ');void(0);" title="Usuario no se presentó a su cita">
                    <i class="fas fa-times-circle fa-lg"></i> Ausencia
                </a>
            </td>
        </tr>';
        $i++;
    }

    if ($nroProductos === 0) {
        $tabla .= '<tr><td colspan="12" style="color:#C7030D">No se encontraron resultados</td></tr>';
    } else {
        $tabla .= '<tr><td colspan="12"><b><p align="center">Total de registros encontrados ' . $nroProductos . '</p></b></td></tr>';
    }

    $tabla .= '</table>';

    echo json_encode(array($tabla, $lista), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error paginar.php: ' . $e->getMessage());
    echo json_encode(array(
        '<div class="alert alert-danger">No se pudieron consultar los registros.</div>',
        ''
    ), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmtCount instanceof mysqli_stmt) $stmtCount->close();
    if ($stmtData instanceof mysqli_stmt) $stmtData->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}