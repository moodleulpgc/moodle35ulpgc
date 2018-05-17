<?php

require_once ('../config.php');
require_once ($CFG->libdir . '/ulpgc/class.ficheroULPGC.php');
require_once ($CFG->libdir . '/dmllib.php');
require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->dirroot . '/group/lib.php');

global $USER;

// Se ejecuta desde linea de comandos
if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
	error_log ( "should not be called from web server!" );
	exit ();
}

// Hay cursos definidos
if (! $site = get_site ()) {
	uclog_actualiza ( 'No se ha completado la instalacion', 1 );
}

if (! $USER = get_admin ()) {
	uclog_actualiza ( 'No existe administrador en el sistema', 1 );
}

uclog_actualiza ( '********' . $site->shortname . ': Log de la actualización de depatartamento de cursos (script actualizaCourseDepartamento.php)********' );
uclog_actualiza ( 'Fecha: ' . userdate ( time () ) );

set_time_limit ( 3600 ); // Tiempo maximo para ejecutar el script
$time_start = microtime (); // Para controlar el tiempo de ejecucion


$user_file = $argv [1];

// Determina el archivo a leer
$filename = "$CFG->dataroot/ulpgcdata/$user_file"; // Default location


// Instancia el fichero. Si el fichero no existe se finaliza la ejecucion
$fichero = new ficheroULPGC ( $filename );
if (! $fichero->get_existe ()) {
	uclog_actualiza ( "No se pudo acceder al fichero $filename\n", 1 );
}

$cursos = $fichero->get_registros ( 'idnumber' );

if ((! isset ( $cursos )) or (count ( $cursos ) == 0)) {
	uclog ( 'No hay cursos definidos', 1 );
}

// array de cursos en moodle indexados por idnumber
$db_courses_array = get_records ( "course", "", "", "", "idnumber, shortname, fullname, visible,id" );
$cursos_moodle = array ();
foreach ( $db_courses_array as $dbcourse ) {
	$cursos_moodle [$dbcourse->idnumber] = $dbcourse;
}
$courselistmoodle = array_keys ( $cursos_moodle );
unset ( $db_courses_array );

/// listado de cursos externos
$courselistulpgc = $fichero->get_ids ( 'idnumber' );

// cursos a modificar (están en la plataforma)
$addcourses = array_intersect ( $courselistulpgc, $courselistmoodle );

if (! empty ( $addcourses )) {
	uclog_actualiza ( 'Cursos a modificar: ' . count ( $addcourses ) );
	begin_sql ();
	foreach ( $addcourses as $ekcurso ) {
		$curso = $fichero->registro2objeto ( $ekcurso );
		
		// Si no existe el curso se ignora
		if (! $course = get_record ( "course", "idnumber", $curso->idnumber )) {
			uclog_actualiza ( "El curso " . $ekcurso . " no existe en la plataforma\n" );
			continue;
		}
		
		if (isset ( $curso->departamento )) {
			$cursoCred->id = $course->id;
			// Asignarle id del departamento al curso
			$departamento = get_field_select ( 'departamentos', 'id', 'codigo = \'' . $curso->departamento . '\'' );
			if (! $departamento) { // Si no existe la categoría se asigna departamento 0
				$departamento = 0;
			}
			$cursoCred->departamento = $departamento;
			
			if (! (update_record ( 'course', $cursoCred ))) {
				uclog_actualiza ( "Fallo al actualizar el curso con idnumber $ekcurso" );
			}
		}
	}
	commit_sql ();
	unset ( $addcourses ); // free mem
} else {
	uclog_actualiza ( 'No hay cursos a modificar' );
}

/**
 * Almacena en el log los mensajes del script
 *
 * @param unknown_type $mensaje
 */
function uclog_actualiza($mensaje, $terminal = 0) {
	global $log;
	global $CFG;
	
	$mensaje = clean_text ( $mensaje );
	$log .= $mensaje . "\n";
	
	file_put_contents ( $CFG->dataroot . '/1/log_uploadcourse.txt', $log );
	if ($terminal == 1) {
		die ();
	}
}

?>