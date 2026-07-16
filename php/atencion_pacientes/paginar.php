<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

function responderPaginacion($status, $title, $message, $html = '', $pagination = '', $total = 0)
{
    echo json_encode(array(
        'status' => $status,
        'title' => $title,
        'message' => $message,
        'type' => $status === 'success' ? 'success' : 'error',
        'html' => $html,
        'pagination' => $pagination,
        'total' => (int) $total
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = null;
$stmtCount = null;
$stmtData = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !is_numeric($_SESSION['colaborador_id'])) {
        throw new Exception('La sesión del usuario no es válida. Inicie sesión nuevamente.');
    }

    $colaborador_id = (int) $_SESSION['colaborador_id'];

    // Libera inmediatamente el bloqueo de la sesión. De esta manera las
    // demás peticiones AJAX pueden ejecutarse en paralelo y no quedan en cola.
    session_write_close();

    $paginaActual = isset($_POST['partida']) ? (int) $_POST['partida'] : 1;
    $paginaActual = $paginaActual > 0 ? $paginaActual : 1;

    $fechai = isset($_POST['fechai']) ? trim((string) $_POST['fechai']) : '';
    $fechaf = isset($_POST['fechaf']) ? trim((string) $_POST['fechaf']) : '';
    $dato = isset($_POST['dato']) ? trim((string) $_POST['dato']) : '';
    $estado = isset($_POST['estado']) && $_POST['estado'] !== '' ? (int) $_POST['estado'] : 0;

    $validarFecha = static function ($fecha) {
        $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
        $errores = DateTime::getLastErrors();

        return $objeto !== false
            && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
            && $objeto->format('Y-m-d') === $fecha;
    };

    if (!$validarFecha($fechai) || !$validarFecha($fechaf)) {
        throw new Exception('El rango de fechas no es válido.');
    }

    if ($fechai > $fechaf) {
        throw new Exception('La fecha inicial no puede ser mayor que la fecha final.');
    }

    $fechaDesde = $fechai . ' 00:00:00';
    $fechaHastaObj = new DateTime($fechaf);
    $fechaHastaObj->modify('+1 day');
    $fechaHasta = $fechaHastaObj->format('Y-m-d 00:00:00');

    $mysqli = connect_mysqli();

    if (!$mysqli || $mysqli->connect_errno) {
        throw new Exception('No se pudo establecer conexión con la base de datos.');
    }

    $mysqli->set_charset('utf8mb4');

    $busqueda = '%' . $dato . '%';

    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM agenda AS a
        INNER JOIN pacientes AS p ON a.pacientes_id = p.pacientes_id
        INNER JOIN servicios AS s ON a.servicio_id = s.servicio_id
        INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
        WHERE a.fecha_cita >= ? AND a.fecha_cita < ?
          AND a.status = ?
          AND a.colaborador_id = ?
          AND a.preclinica = 1
          AND (
                CAST(p.expediente AS CHAR) LIKE ?
                OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
                OR CONCAT(p.apellido, ' ', p.nombre) LIKE ?
                OR p.identidad LIKE ?
                OR p.apellido LIKE ?
              )";

    $stmtCount = $mysqli->prepare($sqlCount);

    if (!$stmtCount) {
        throw new Exception('No se pudo preparar el conteo de atenciones: ' . $mysqli->error);
    }

    $stmtCount->bind_param(
        'ssiisssss',
        $fechaDesde,
        $fechaHasta,
        $estado,
        $colaborador_id,
        $busqueda,
        $busqueda,
        $busqueda,
        $busqueda,
        $busqueda
    );

    if (!$stmtCount->execute()) {
        throw new Exception('No se pudo contar las atenciones: ' . $stmtCount->error);
    }

    $resultadoCount = $stmtCount->get_result();
    $totalRows = (int) ($resultadoCount->fetch_assoc()['total'] ?? 0);

    $nroLotes = 25;
    $nroPaginas = max(1, (int) ceil($totalRows / $nroLotes));

    if ($paginaActual > $nroPaginas) {
        $paginaActual = $nroPaginas;
    }

    $offset = ($paginaActual - 1) * $nroLotes;

    $sqlData = "
        SELECT
            p.pacientes_id,
            a.agenda_id,
            p.identidad,
            CONCAT(p.apellido, ' ', p.nombre) AS paciente,
            p.telefono1 AS telefono,
            DATE_FORMAT(a.fecha_cita, '%d/%m/%Y') AS fecha_cita,
            a.hora,
            a.paciente AS tipo_paciente,
            CONCAT(c.apellido, ' ', c.nombre) AS colaborador,
            s.nombre AS servicio,
            a.observacion,
            a.comentario,
            CASE
                WHEN a.status = 0 THEN 'Pendiente'
                WHEN a.status = 1 THEN 'Atendido'
                WHEN a.status = 2 THEN 'Ausencia'
                WHEN a.status = 3 THEN 'Seguimiento'
                WHEN a.status = 4 THEN 'Eliminado'
                ELSE 'Desconocido'
            END AS estatus,
            a.status,
            DATE(a.fecha_cita) AS fecha,
            c.colaborador_id,
            s.servicio_id
        FROM agenda AS a
        INNER JOIN pacientes AS p ON a.pacientes_id = p.pacientes_id
        INNER JOIN servicios AS s ON a.servicio_id = s.servicio_id
        INNER JOIN colaboradores AS c ON a.colaborador_id = c.colaborador_id
        WHERE a.fecha_cita >= ? AND a.fecha_cita < ?
          AND a.status = ?
          AND a.colaborador_id = ?
          AND a.preclinica = 1
          AND (
                CAST(p.expediente AS CHAR) LIKE ?
                OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
                OR CONCAT(p.apellido, ' ', p.nombre) LIKE ?
                OR p.identidad LIKE ?
                OR p.apellido LIKE ?
              )
        ORDER BY a.fecha_cita ASC, a.hora ASC, a.pacientes_id ASC
        LIMIT ?, ?";

    $stmtData = $mysqli->prepare($sqlData);

    if (!$stmtData) {
        throw new Exception('No se pudo preparar la consulta de atenciones: ' . $mysqli->error);
    }

    $stmtData->bind_param(
        'ssiisssssii',
        $fechaDesde,
        $fechaHasta,
        $estado,
        $colaborador_id,
        $busqueda,
        $busqueda,
        $busqueda,
        $busqueda,
        $busqueda,
        $offset,
        $nroLotes
    );

    if (!$stmtData->execute()) {
        throw new Exception('No se pudieron consultar las atenciones: ' . $stmtData->error);
    }

    $resultado = $stmtData->get_result();

    $html = '
        <table class="table table-striped table-condensed table-hover">
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

    if ($resultado->num_rows === 0) {
        $html .= '
            <tr>
                <td colspan="10" class="text-center text-danger">
                    No se encontraron resultados
                </td>
            </tr>';
    } else {
        while ($fila = $resultado->fetch_assoc()) {
            $pacientes_id = (int) $fila['pacientes_id'];
            $agenda_id = (int) $fila['agenda_id'];
            $statusAgenda = (int) $fila['status'];

            $acciones = '<span class="text-muted">Sin acciones</span>';

            if ($statusAgenda === 0) {
                $acciones = '
                    <button type="button"
                        class="btn btn-primary btn-sm"
                        onclick="editarRegistro(' . $pacientes_id . ', ' . $agenda_id . ');">
                        <i class="fas fa-notes-medical"></i> Generar atención
                    </button>';
            }

            $html .= '
                <tr>
                    <td>' . htmlspecialchars((string) $fila['fecha_cita'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['hora'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['paciente'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['identidad'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['telefono'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['servicio'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['estatus'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['observacion'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . htmlspecialchars((string) $fila['comentario'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . $acciones . '</td>
                </tr>';
        }

        $html .= '
            <tr>
                <td colspan="10" class="text-center">
                    <strong>Total de registros encontrados: ' . $totalRows . '</strong>
                </td>
            </tr>';
    }

    $html .= '</tbody></table>';

    $pagination = '';

    if ($paginaActual > 1) {
        $pagination .= '
            <li class="page-item">
                <a class="page-link" href="#" onclick="pagination(1); return false;">Inicio</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#" onclick="pagination(' . ($paginaActual - 1) . '); return false;">
                    Anterior
                </a>
            </li>';
    }

    $pagination .= '
        <li class="page-item active">
            <span class="page-link">' . $paginaActual . ' de ' . $nroPaginas . '</span>
        </li>';

    if ($paginaActual < $nroPaginas) {
        $pagination .= '
            <li class="page-item">
                <a class="page-link" href="#" onclick="pagination(' . ($paginaActual + 1) . '); return false;">
                    Siguiente
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#" onclick="pagination(' . $nroPaginas . '); return false;">
                    Última
                </a>
            </li>';
    }

    responderPaginacion(
        'success',
        'Consulta completada',
        $totalRows > 0 ? 'Atenciones encontradas.' : 'No se encontraron atenciones.',
        $html,
        $pagination,
        $totalRows
    );
} catch (Throwable $e) {
    error_log('Error al paginar atenciones: ' . $e->getMessage());

    responderPaginacion(
        'error',
        'Error al consultar',
        $e->getMessage(),
        '<div class="alert alert-danger">' .
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
        '</div>',
        '',
        0
    );
} finally {
    if ($stmtCount instanceof mysqli_stmt) {
        $stmtCount->close();
    }

    if ($stmtData instanceof mysqli_stmt) {
        $stmtData->close();
    }

    if ($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}