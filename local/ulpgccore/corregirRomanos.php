<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../config.php');
global $USER;
// Se ejecuta desde linea de comandos
if (isset($_SERVER['REMOTE_ADDR'])) {
    error_log("should not be called from web server!");
    exit();
}
// Hay cursos definidos
if (! $site = get_site()) {
    echo ('No se ha completado la instalacion');
}
if (! $USER = get_admin()) {
    echo ('No existe administrador en el sistema');
}
$romanos = array('I' , 'II' , 'III' , 'IV' , 'V' , 'VI' , 'VII' , 'VIII' , 'IX' , 'X' , 'XI' , 'XII' , 'XII' , 'XIV' , 'XV' , 'XVI' , 'XVII' , 'XVIII' , 'XIX' , 'XX');
$siglas = array('RRHH');
$acronimos = array_merge($romanos, $siglas);
$preposiciones = array('a','ante','bajo','cabe','con','contra','de','desde','en','entre','hacia','hasta','para','por','según','sin','so','sobre','tras',/* no es prepo*/'del','al');
$articulos = array('el', 'la', 'las', 'los', 'un', 'unos', 'un', 'una', 'unas');
$nexos = array('y', 'u', 'o', 'e');
$conectores = array_merge($preposiciones, $articulos, $nexos);
$cursos = get_courses();
foreach ($cursos as $curso) {
    $curso->fullname = ucwords(mb_strtolower($curso->fullname, 'UTF-8'));
    $palabras = explode(" ", $curso->fullname);
    $nombre = '';
    foreach ($palabras as $key => $palabra) {
        if (in_array(strtoupper($palabra), $acronimos))
            $palabras[$key] = strtoupper($palabra);
        if (in_array(strtolower($palabra), $conectores) && $key != 0) {
        	$palabras[$key] = strtolower($palabra);
        }
    }
    $nombre = implode(" ", $palabras);
    // TODO Crear el objeto y actualizar el curso
    $cursoActualizado = new stdClass();
    $cursoActualizado->id = $curso->id;
    $cursoActualizado->fullname = $nombre;
    
     if (! $DB->update_record('course', $cursoActualizado)) {
        echo "Fallo actualizando el curso con código " . $cursoActualizado->id . "\n";
    } else {
        echo "Curso con código " . $cursoActualizado->id . " actualizado.\n";
    } 
}
?>
