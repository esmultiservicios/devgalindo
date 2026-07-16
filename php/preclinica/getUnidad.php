<?php
session_start();
include "../funtions.php";

header('Content-Type: text/html; charset=utf-8');

$mysqli = null;
$stmt = null;

try {
    $servicio = isset($_POST['servicio']) ? (int)$_POST['servicio'] : 0;

    echo '<option value="">Seleccione</option>';

    if ($servicio <= 0) exit;

    $mysqli = connect_mysqli();
    if (!$mysqli || $mysqli->connect_errno) throw new Exception('No se pudo conectar con la base de datos.');
    $mysqli->set_charset('utf8mb4');

    $stmt = $mysqli->prepare(
        "SELECT pc.puesto_id, pc.nombre AS puesto
         FROM servicios_puestos AS sp
         INNER JOIN colaboradores AS c ON sp.colaborador_id = c.colaborador_id
         INNER JOIN puesto_colaboradores AS pc ON c.puesto_id = pc.puesto_id
         WHERE sp.servicio_id = ?
           AND pc.puesto_id IN (2, 4)
         GROUP BY pc.puesto_id, pc.nombre
         ORDER BY pc.nombre"
    );
    if (!$stmt) throw new Exception($mysqli->error);
    $stmt->bind_param('i', $servicio);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "<optgroup label='Unidad'>";
        while ($fila = $resultado->fetch_assoc()) {
            echo '<option value="' . (int)$fila['puesto_id'] . '">' .
                 htmlspecialchars((string)$fila['puesto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
                 '</option>';
        }
        echo '</optgroup>';
    }
} catch (Throwable $e) {
    error_log('Error getUnidad.php: ' . $e->getMessage());
    echo '<option value="">No se pudo cargar la unidad</option>';
} finally {
    if ($stmt instanceof mysqli_stmt) $stmt->close();
    if ($mysqli instanceof mysqli) $mysqli->close();
}
