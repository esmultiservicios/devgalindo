<?php
session_start();
include "../funtions.php";

header('Content-Type: text/plain; charset=utf-8');

$fecha = trim((string)($_POST['fecha'] ?? ''));
$obj = DateTime::createFromFormat('Y-m-d', $fecha);

if (!$obj || $obj->format('Y-m-d') !== $fecha) {
    echo 2;
    exit;
}

echo $obj->format('Y-m') === date('Y-m') ? 1 : 2;