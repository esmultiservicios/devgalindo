<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $mysqli = connect_mysqli();

    $colaborador_id = $_SESSION['colaborador_id'];
    $paginaActual = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
    $fechai = $_POST['fechai'] ?? '';
    $fechaf = $_POST['fechaf'] ?? '';
    $dato = $_POST['dato'] ?? '';
    $estado = $_POST['estado'] ?? '';

    if ($paginaActual <= 0) $paginaActual = 1;

    // CONSULTAR PUESTO_ID (si lo necesitás)
    $consultar_puesto = "SELECT puesto_id FROM colaboradores WHERE colaborador_id = '$colaborador_id'";
    $resultP = $mysqli->query($consultar_puesto);
    $puesto_id = '';
    if ($resultP && $resultP->num_rows > 0) {
        $puesto_id = $resultP->fetch_assoc()['puesto_id'];
    }

    $dato_esc = $mysqli->real_escape_string($dato);

    $where = "WHERE CAST(a.fecha_cita AS DATE) BETWEEN '$fechai' AND '$fechaf'
              AND a.status = '$estado'
              AND a.colaborador_id = '$colaborador_id'
              AND a.preclinica = 1
              AND (
                    p.expediente LIKE '%$dato_esc%' OR
                    CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato_esc%' OR
                    p.identidad LIKE '$dato_esc%' OR
                    p.apellido LIKE '$dato_esc%'
                  )";

    // CONTAR TOTAL (IMPORTANTE: para paginación real)
    $countSql = "SELECT COUNT(*) AS total
                 FROM agenda AS a
                 INNER JOIN pacientes AS p ON a.pacientes_id = p.pacientes_id
                 INNER JOIN servicios AS s ON a.servicio_id = s.servicio_id
                 INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
                 $where";

    $resultCount = $mysqli->query($countSql);
    if (!$resultCount) throw new Exception($mysqli->error);
    $totalRows = (int)($resultCount->fetch_assoc()['total'] ?? 0);

    $nroLotes = 25;
    $nroPaginas = ($totalRows > 0) ? (int)ceil($totalRows / $nroLotes) : 1;

    $lista = '';
    if ($paginaActual > 1) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');">Anterior ' . ($paginaActual - 1) . '</a></li>';
    }
    if ($paginaActual < $nroPaginas) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');">Última</a></li>';
    }

    $limit = ($paginaActual - 1) * $nroLotes;

    $sql = "SELECT p.pacientes_id AS pacientes_id,
                   a.agenda_id AS agenda_id,
                   p.identidad AS identidad,
                   CONCAT(p.apellido,' ',p.nombre) AS paciente,
                   p.telefono1 AS telefono,
                   DATE_FORMAT(CAST(a.fecha_cita AS DATE), '%d/%m/%Y') AS fecha_cita,
                   a.hora AS hora,
                   a.paciente AS tipo_paciente,
                   CONCAT(c.apellido,' ',c.nombre) AS colaborador,
                   s.nombre AS servicio,
                   a.observacion AS observacion,
                   a.comentario AS comentario,
                   (CASE WHEN a.status = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS estatus,
                   CAST(a.fecha_cita AS DATE) AS fecha,
                   c.colaborador_id,
                   s.servicio_id
            FROM agenda AS a
            INNER JOIN pacientes AS p ON a.pacientes_id = p.pacientes_id
            INNER JOIN servicios AS s ON a.servicio_id = s.servicio_id
            INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
            $where
            ORDER BY a.hora, a.pacientes_id ASC
            LIMIT $limit, $nroLotes";

    $result = $mysqli->query($sql);
    if (!$result) throw new Exception($mysqli->error);

    // TABLA HTML (esto es lo que tu JS espera meter con .html())
    $tabla = '<table class="table table-striped table-condensed table-hover">
                <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Hora</th>
                  <th>Paciente</th>
                  <th>Identidad</th>
                  <th>Teléfono</th>
                  <th>Servicio</th>
                  <th>Estatus</th>
                  <th>Observación</th>
                  <th>Comentario</th>
                </tr>
                </thead>
                <tbody>';

    if ($result->num_rows == 0) {
        $tabla .= '<tr><td colspan="9" style="color:#C7030D">No se encontraron resultados</td></tr>';
    } else {
        while ($r = $result->fetch_assoc()) {
            $tabla .= '<tr>
              <td>' . ($r['fecha_cita'] ?? '') . '</td>
              <td>' . ($r['hora'] ?? '') . '</td>
              <td>' . ($r['paciente'] ?? '') . '</td>
              <td>' . ($r['identidad'] ?? '') . '</td>
              <td>' . ($r['telefono'] ?? '') . '</td>
              <td>' . ($r['servicio'] ?? '') . '</td>
              <td>' . ($r['estatus'] ?? '') . '</td>
              <td>' . ($r['observacion'] ?? '') . '</td>
              <td>' . ($r['comentario'] ?? '') . '</td>
            </tr>';
        }

        $tabla .= '<tr><td colspan="9"><b><p align="center">Total de Registros Encontrados ' . $totalRows . '</p></b></td></tr>';
    }

    $tabla .= '</tbody></table>';

    echo json_encode([0 => $tabla, 1 => $lista, 2 => $totalRows], JSON_UNESCAPED_UNICODE);

    $result->free();
    $resultCount->free();
    $mysqli->close();

} catch (Throwable $e) {
    // para que nunca reviente el JS
    echo json_encode([0 => '', 1 => '', 2 => 0, 'error' => true, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}