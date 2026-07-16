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
    $fecha = trim((string)($_POST['fecha'] ?? date('Y-m-d')));
    $dato = trim((string)($_POST['dato'] ?? ''));
    $servicio = isset($_POST['servicio']) && $_POST['servicio'] !== '' ? (int)$_POST['servicio'] : 0;
    $unidad = isset($_POST['unidad']) && $_POST['unidad'] !== '' ? (int)$_POST['unidad'] : 0;

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $from = "
        FROM agenda AS ag
        INNER JOIN pacientes AS p ON ag.pacientes_id = p.pacientes_id
        INNER JOIN colaboradores AS c ON ag.colaborador_id = c.colaborador_id
        INNER JOIN servicios AS s ON ag.servicio_id = s.servicio_id
        INNER JOIN puesto_colaboradores AS pc ON c.puesto_id = pc.puesto_id
    ";

    $where = " WHERE CAST(ag.fecha_cita AS DATE) = ? ";
    $types = 's';
    $params = array($fecha);

    if ($servicio > 0) {
        $where .= " AND s.servicio_id = ? ";
        $types .= 'i';
        $params[] = $servicio;
    }
    if ($unidad > 0) {
        $where .= " AND pc.puesto_id = ? ";
        $types .= 'i';
        $params[] = $unidad;
    }
    if ($dato !== '') {
        $where .= " AND (
            CAST(p.expediente AS CHAR) LIKE ?
            OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
        ) ";
        $like = '%' . $dato . '%';
        $types .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }

    $stmtCount = $mysqli->prepare("SELECT COUNT(*) AS total " . $from . $where);
    if (!$stmtCount) throw new Exception($mysqli->error);
    $stmtCount->bind_param($types, ...$params);
    if (!$stmtCount->execute()) throw new Exception($stmtCount->error);
    $total = (int)$stmtCount->get_result()->fetch_assoc()['total'];

    $porPagina = 10;
    $paginas = max(1, (int)ceil($total / $porPagina));
    if ($paginaActual > $paginas) $paginaActual = $paginas;
    $offset = ($paginaActual - 1) * $porPagina;

    $lista = '';
    if ($paginaActual > 1) {
        $lista .= '<li><a href="javascript:pagination(' . ($paginaActual - 1) . ');">Anterior</a></li>';
    }
    for ($i = 1; $i <= $paginas; $i++) {
        $lista .= $i === $paginaActual
            ? '<li class="active"><a href="javascript:pagination(' . $i . ');">' . $i . '</a></li>'
            : '<li><a href="javascript:pagination(' . $i . ');">' . $i . '</a></li>';
    }
    if ($paginaActual < $paginas) {
        $lista .= '<li><a href="javascript:pagination(' . ($paginaActual + 1) . ');">Siguiente</a></li>';
    }

    $sql = "SELECT
                p.expediente,
                CONCAT(p.nombre, ' ', p.apellido) AS paciente,
                CAST(ag.fecha_cita AS DATE) AS fecha_cita,
                ag.hora,
                CONCAT(c.nombre, ' ', c.apellido) AS colaborador,
                s.nombre AS servicio,
                ag.agenda_id,
                ag.pacientes_id
            " . $from . $where . "
            ORDER BY ag.servicio_id, ag.hora ASC
            LIMIT ?, ?";

    $stmtData = $mysqli->prepare($sql);
    if (!$stmtData) throw new Exception($mysqli->error);
    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, array($offset, $porPagina));
    $stmtData->bind_param($dataTypes, ...$dataParams);
    if (!$stmtData->execute()) throw new Exception($stmtData->error);

    $tabla = '<table class="table table-striped table-condensed table-hover">
        <tr>
            <th>No.</th><th>Expediente</th><th>Paciente</th><th>Fecha Cita</th>
            <th>Hora Cita</th><th>Profesional</th><th>Servicio</th>
        </tr>';

    $i = $offset + 1;
    $resultado = $stmtData->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $exp = (int)$fila['expediente'] === 0 ? 'TEMP' : (int)$fila['expediente'];
        $tabla .= '<tr>
            <td>' . $i . '</td>
            <td>' . h($exp) . '</td>
            <td>' . h($fila['paciente']) . '</td>
            <td>' . h($fila['fecha_cita']) . '</td>
            <td>' . h($fila['hora']) . '</td>
            <td>' . h($fila['colaborador']) . '</td>
            <td>' . h($fila['servicio']) . '</td>
        </tr>';
        $i++;
    }

    if ($total === 0) {
        $tabla .= '<tr><td colspan="7" style="color:#C7030D">No se encontraron resultados</td></tr>';
    }

    $tabla .= '</table>';

    echo json_encode(array($tabla, $lista), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error paginar_busqueda.php: ' . $e->getMessage());
    echo json_encode(array(
        '<div class="alert alert-danger">No se pudieron consultar los registros.</div>',
        ''
    ), JSON_UNESCAPED_UNICODE);
} finally {
    if ($stmtCount instanceof mysqli_stmt) $stmtCount->close();
    if ($stmtData instanceof mysqli_stmt) $stmtData->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}