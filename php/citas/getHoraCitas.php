<?php
// Definir el intervalo en minutos
$intervalo = 30;

// Hora de inicio y fin
$horaInicio = '07:00';
$horaFin = '23:30';

// Convertir las horas a minutos
list($hora, $minuto) = explode(':', $horaInicio);
$horaInicioEnMinutos = $hora * 60 + $minuto;

list($hora, $minuto) = explode(':', $horaFin);
$horaFinEnMinutos = $hora * 60 + $minuto;

// Generar las opciones de tiempo
$options = '';
for ($i = $horaInicioEnMinutos; $i <= $horaFinEnMinutos; $i += $intervalo) {
	$horaFormateada = sprintf('%02d:%02d', floor($i / 60), $i % 60);
	$options .= "<option value='$horaFormateada'>$horaFormateada</option>\n";
}

echo $options;