<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

try {
    // CONEXIÓN A DB
    $mysqli = connect_mysqli();

    // POST
    $paginaActual = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
    $desde        = isset($_POST['desde']) ? $_POST['desde'] : '';
    $hasta        = isset($_POST['hasta']) ? $_POST['hasta'] : '';
    $dato         = isset($_POST['dato']) ? trim($_POST['dato']) : '';
    $colaborador  = isset($_POST['colaborador']) ? trim($_POST['colaborador']) : '';

    if ($paginaActual <= 0) $paginaActual = 1;

    // WHERE base
    $where = "WHERE am.fecha BETWEEN '$desde' AND '$hasta'";

    // ✅ FILTRO CORRECTO: usar el colaborador seleccionado
    if ($colaborador !== "") {
        $where .= " AND am.colaborador_id = '$colaborador'";
    } elseif ($dato !== "") {
        $dato_esc = $mysqli->real_escape_string($dato);
        $where .= " AND (CONCAT(p.nombre, ' ', p.apellido) LIKE '%$dato_esc%' 
                    OR p.apellido LIKE '%$dato_esc%' 
                    OR p.identidad LIKE '%$dato_esc%')";
    }

    // Contar total
    $countQuery = "SELECT COUNT(*) AS total
                   FROM atenciones_medicas AS am
                   INNER JOIN pacientes AS p ON am.pacientes_id = p.pacientes_id
                   $where";

    $resultCount = $mysqli->query($countQuery);
    if (!$resultCount) throw new Exception($mysqli->error);

    $totalRows = (int)($resultCount->fetch_assoc()['total'] ?? 0);

    $nroLotes   = 15;
    $nroPaginas = ($totalRows > 0) ? (int)ceil($totalRows / $nroLotes) : 1;
    $limit      = ($paginaActual - 1) * $nroLotes;

    // ✅ Query principal (columna corregida: hospitalizaciones)
    $query = "SELECT am.atencion_id AS atencion_id,
                     DATE_FORMAT(am.fecha, '%d/%m/%Y') AS fecha,
                     CONCAT(p.nombre, ' ', p.apellido) AS paciente,
                     p.identidad AS identidad,
                     am.antecedentes_medicos_no_psiquiatricos AS antecedentes_medicos_no_psiquiatricos,
                     am.hospitalizaciones AS hospitalizaciones,
                     am.cirugias AS cirugias,
                     am.alergias AS alergias,
                     am.antecedentes_medicos_psiquiatricos AS antecedentes_medicos_psiquiatricos,
                     am.historia_gineco_obstetrica AS historia_gineco_obstetrica,
                     am.medicamentos_previos AS medicamentos_previos,
                     am.medicamentos_actuales AS medicamentos_actuales,
                     am.legal AS legal,
                     am.sustancias AS sustancias,
                     am.rasgos_personalidad AS rasgos_personalidad,
                     am.informacion_adicional AS informacion_adicional,
                     am.pendientes AS pendientes,
                     am.diagnostico AS diagnostico,
                     am.seguimiento AS seguimiento,
                     (CASE WHEN p.genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS sexo,
                     (CASE WHEN am.paciente = 'N' THEN 'Nuevo' ELSE 'Subsiguiente' END) AS paciente_tipo
              FROM atenciones_medicas AS am
              INNER JOIN pacientes AS p ON am.pacientes_id = p.pacientes_id
              $where
              ORDER BY am.fecha DESC
              LIMIT $limit, $nroLotes";

    $result = $mysqli->query($query);
    if (!$result) throw new Exception($mysqli->error);

    // Tabla HTML
    $tabla = '<table class="table table-striped table-condensed table-hover">
                <tr>
                  <th width="6.5%">Fecha</th>
                  <th width="15%">Paciente</th>
                  <th width="10%">Identidad</th>
                  <th width="8%">Sexo</th>
                  <th width="10%">Tipo Paciente</th>
                  <th width="15%">Antecedentes Médicos No Psiquiátricos</th>
                  <th width="15%">Hospitalizaciones</th>
                  <th width="15%">Cirugías</th>
                  <th width="15%">Alergias</th>
                  <th width="15%">Antecedentes Médicos Psiquiátricos</th>
                  <th width="15%">Historia Gineco-Obstétrica</th>
                  <th width="15%">Medicamentos Previos</th>
                  <th width="15%">Medicamentos Actuales</th>
                  <th width="15%">Legal</th>
                  <th width="15%">Sustancias</th>
                  <th width="15%">Rasgos de Personalidad</th>
                  <th width="15%">Información Adicional</th>
                  <th width="15%">Pendientes</th>
                  <th width="15%">Diagnóstico</th>
                  <th width="15%">Seguimiento</th>
                </tr>';

    while ($registro2 = $result->fetch_assoc()) {
        $tabla .= '<tr>
          <td>' . ($registro2['fecha'] ?? '') . '</td>
          <td>' . ($registro2['paciente'] ?? '') . '</td>
          <td>' . ($registro2['identidad'] ?? '') . '</td>
          <td>' . ($registro2['sexo'] ?? '') . '</td>
          <td>' . ($registro2['paciente_tipo'] ?? '') . '</td>
          <td>' . ($registro2['antecedentes_medicos_no_psiquiatricos'] ?? '') . '</td>
          <td>' . ($registro2['hospitalizaciones'] ?? '') . '</td>
          <td>' . ($registro2['cirugias'] ?? '') . '</td>
          <td>' . ($registro2['alergias'] ?? '') . '</td>
          <td>' . ($registro2['antecedentes_medicos_psiquiatricos'] ?? '') . '</td>
          <td>' . ($registro2['historia_gineco_obstetrica'] ?? '') . '</td>
          <td>' . ($registro2['medicamentos_previos'] ?? '') . '</td>
          <td>' . ($registro2['medicamentos_actuales'] ?? '') . '</td>
          <td>' . ($registro2['legal'] ?? '') . '</td>
          <td>' . ($registro2['sustancias'] ?? '') . '</td>
          <td>' . ($registro2['rasgos_personalidad'] ?? '') . '</td>
          <td>' . ($registro2['informacion_adicional'] ?? '') . '</td>
          <td>' . ($registro2['pendientes'] ?? '') . '</td>
          <td>' . ($registro2['diagnostico'] ?? '') . '</td>
          <td>' . ($registro2['seguimiento'] ?? '') . '</td>
        </tr>';
    }

    if ($totalRows == 0) {
        $tabla .= '<tr><td colspan="20" style="color:#C7030D">No se encontraron resultados</td></tr>';
    } else {
        $tabla .= '<tr><td colspan="20"><b><p ALIGN="center">Total de Registros Encontrados ' . $totalRows . '</p></b></td></tr>';
    }

    $tabla .= '</table>';

    // Paginación HTML
    $lista = '';

    if ($paginaActual > 1) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');">Anterior ' . ($paginaActual - 1) . '</a></li>';
    }

    if ($paginaActual < $nroPaginas) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($nroPaginas) . ');">Última</a></li>';
    }

    echo json_encode([0 => $tabla, 1 => $lista], JSON_UNESCAPED_UNICODE);

    $result->free();
    $resultCount->free();
    $mysqli->close();

} catch (Throwable $e) {
    // ✅ SIEMPRE devolver JSON para que el frontend no reviente
    echo json_encode([
        0 => '',
        1 => '',
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}