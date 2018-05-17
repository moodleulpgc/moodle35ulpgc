<?php
/**
 * IMPRESCINDIBLE ANTES DE EJECUTAR QUE TODOS LOS GRUPOS TENGAN DEFINIDO EL CAMPO IDNUMBER
 *
 * SOLO SE MODIFICAN LOS GRUPO CON CAMPO ENROL='miulpgc'
 *
 * PERMITIR QUE SE INVOQUE CON LOS PARÁMETROS USUARIO, CURSO.
 * - si no se pasa ninguno, actualiza todos los grupos
 * - si se la pasa el usuario, se actualizan todos los grupos del usuario
 * - si se pasa el curso, se actualizan todos los grupos del curso
 * - si se pasan tanto el usuario como el curso, se actualizan todos los grupos del usuario en el curso
 */
if (! defined ( 'CLI_SCRIPT' )) {
	define ( 'CLI_SCRIPT', true );
}
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ('class.ficheroULPGC.php');
require_once ('libulpgc.php');
require_once ($CFG->dirroot . '/group/lib.php');

/**
 *
 *
 * Redefinición en local de la función lib/grouplib/groups_get_user_groups para
 * que devuelva
 * los código de grupo en lugar de los id, ignorando los agrupamientos y
 * considerando solo los grupos
 * con campo enrol = 'miulpgc'
 *
 * @param int $courseid
 *        	Id del curso
 * @param int $userid
 *        	Id del usuario
 */
function groups_get_user_groupcodes($courseid, $userid = 0) {
	global $CFG, $USER, $DB;

	if (empty ( $userid )) {
		$userid = $USER->id;
	}

	$sql = "SELECT g.id, g.idnumber
              FROM {groups} g
              JOIN {groups_members} gm        ON gm.groupid = g.id
             WHERE gm.userid = ? AND g.courseid = ? AND g.enrol = 'miulpgc' and gm.enrol = 'miulpgc'";
	$params = array (
			$userid,
			$courseid
	);

	$rs = $DB->get_recordset_sql ( $sql, $params );

	if (! $rs->valid ()) {
		$rs->close (); // Not going to iterate (but exit), close rs
		return array ();
	}

	$result = array ();
	$allgroups = array ();

	foreach ($rs as $group) {
		$allgroups [$group->idnumber] = $group->id;
	}
	$rs->close ( $rs );

	$result = array_keys ( $allgroups ); // all groups

	return $result;
}

$log = new filelog ( 'grupos_actualizar.log' );

// Se ejecuta desde linea de comandos
if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
	error_log ( "should not be called from web server!" );
	exit ();
}

// Hay cursos definidos
if (! $site = get_site ()) {
	$log->add ( 'No se ha completado la instalacion', 1 );
}

if (! $USER = get_admin ()) {
	$log->add ( 'No existe administrador en el sistema', 1 );
}

$log->add ( '*************************************************************************************' );
$log->add ( '********' . $site->shortname . ': Actualización de grupos masiva (script local/ulpgccore/actualizaGruposMasivo.php)********' );
$log->add ( 'Fecha: ' . userdate ( time () ) );
$log->add ( '*************************************************************************************' );

if (isset ( $argv [1] )) {
	$user_file = $argv [1];
} else {
	$user_file = $_GET ['fichero'];
}

// Determina el archivo a leer
$filename = "$CFG->ulpgcdata/$user_file"; // Default location

// Obtener datos del fichero
$fd = fopen ( $filename, 'rb' );
// Captura la cabecera
$cabecera = fgets ( $fd, 4096 );
$campos = explode ( '|', $cabecera, - 1 );
$cont = 0;

while ( $buffer = fgets ( $fd, 4096 ) ) {
	$linea = explode ( '|', addslashes ( $buffer ), - 1 );
	$cont ++;
	foreach ( $campos as $position => $value ) {
		// Convierte la línea de texto en un objeto
		$matricula [$value] = utf8_encode ( $linea [$position] );
	}

	// Verificar existencia de alumno y curso
	$conditions = array (
			"idnumber" => $matricula ['user_key']
	);
	if (! $user = $DB->get_record ( "user", $conditions, "*" )) {
		$log->add ( "El usuario " . $matricula ['user_key'] . " no existe en la plataforma" );
		continue;
	}

	$conditions = array (
			"idnumber" => $matricula ['idnumber']
	);
	if (! $course = $DB->get_record ( "course", $conditions, "*" )) {
		$log->add ( "El curso " . $matricula ['idnumber'] . " no existe en la plataforma" );
		continue;
	}

	// Crea/recupera un objeto de contexto
	//
	if (! $context = context_course::instance ( $course->id )) {
		$log->add ( "El contexto para el curso " . $course->id . " no existe en la plataforma" );
		continue;
	}

	// Solo se tratan los alumnos que se tienen la matrícula activa en la ULPGC
	if ($matricula ['action'] == 'I') {
		// Grupos en los que está el usuario en el curso especificado en Moodle
		$gruposEnMoodle = groups_get_user_groupcodes ( $course->id, $user->id );

		// Grupos en los que está el usuario en el curso especificado en la
		// ULPGC
		// Se añade esta condición para que no cree grupos vacíos
		if (strlen ( $matricula ['grupo'] ) > 0) {
			$grupos = explode ( ',', $matricula ['grupo'] );
		} else {
			$grupos = array ();
		}

		// Grupos en los que hay que añadir al usuario (está en la ULPGC pero no
		// está en Moodle)
		//echo "Grupos: ";print_object($grupos);echo "En moodle: ";print_object($gruposEnMoodle);
		$addGrupos = array_diff ( $grupos, $gruposEnMoodle );
		/*echo "Grupos añadir:";
		print_r ( $addGrupos );*/
		// Grupos en los que hay que dar de baja al alumno (está en Moodle pero
		// no está en la ULPGC)
		$delGrupos = array_diff ( $gruposEnMoodle, $grupos );
		/*echo "Grupos borrar:";
		print_r ( $delGrupos );
		echo "OK";
		echo count($addGrupos);
		echo count($delGrupos);
		continue;*/

		// Hay grupos a los que asignar
		if (count ( $addGrupos )) {
			foreach ( $addGrupos as $grupo ) {
				// Al intentar añadir, puede que el usuario esté asignado al
				// grupo con enrol 'manual'.
				// Actualizar a enrol 'miulpgc'.
				$conditions = array (
						"courseid" => $course->id,
						"idnumber" => $grupo
				);
				if (! $grupoid = $DB->get_field ( "groups", "id", $conditions )) {
					$log->add ( "No existe el grupo con código " . $grupo . " en el curso " . $course->shortname . " para el usuario " . $user->username );
					/*
					 * echo "grupos\n"; print_object ( $grupos ); echo "grupos
					 * en Moodle\n"; print_object ( $gruposEnMoodle ); echo
					 * "addgrupos\n"; print_object ( $addGrupos ); echo
					 * "delgrupos\n"; print_object ( $delGrupos ); echo "No
					 * existe el grupo con código " . $grupo . " en el curso " .
					 * $course->shortname . " para el usuario " .
					 * $user->username; exit ();
					 */
					continue;
				} else {
					$conditions = array (
							"groupid" => $grupoid,
							"userid" => $user->id
					);
					$miembro = $DB->get_record ( 'groups_members', $conditions, "*" );
					if ($miembro) {
						// Si ya está asignado con enrol manual, cambiar a
						// miulpgc
						if ($miembro->enrol != 'miulpgc') {
							$conditions = array (
									'id' => $miembro->id
							);
							if (! $DB->set_field ( 'groups_members', 'enrol', 'miulpgc', $conditions )) {
								$log->add ( "Actualizada asignación al grupo con código " . $grupo . " en el curso " . $course->shortname . " el usuario " . $user->username );
							}
						}
					} else {
						/*
						 * print_object ( $grupos ); print_object (
						 * $gruposEnMoodle ); print_object ( $addGrupos );
						 * print_object ( $delGrupos ); echo 'Hay grupos que
						 * añadir para el usuario ' . $user->id . ' y el curso '
						 * . $course->id; /*
						 */
						if (! groups_add_member ( $grupoid, $user->id, 'miulpgc' )) {
							$log->add ( "No se pudo asignar al grupo con código " . $grupo . " en el curso " . $course->shortname . " al usuario " . $user->username );
						} else {
							$log->add ( "Asignado al grupo con código " . $grupo . " en el curso " . $course->shortname . " el usuario " . $user->username );
						}
					}
				}
			}
		} // Fin de la asignación de grupos

		// Hay grupos de los que desasignar
		if (count ( $delGrupos )) {
			foreach ( $delGrupos as $grupo ) {
				$conditions = array (
						"courseid" => $course->id,
						"idnumber" => $grupo
				);
				if (! $grupoid = $DB->get_field ( "groups", "id", $conditions )) {
					$log->add ( "No existe el grupo " . $grupoid . " en el curso " . $course->shortname );
					continue;
				} else {
					if (groups_remove_member ( $grupoid, $user->id, 'miulpgc' )) {
						$log->add ( "Desasignado del grupo con código " . $grupo . " en el curso " . $course->shortname . " el usuario " . $user->username );
					} else {
						$log->add ( "No se pudo desasignar del grupo con código " . $grupo . " en el curso " . $course->shortname . " al usuario " . $user->username );
					}
				}
			}
		} // Fin de la desasignación de grupos
	}
}
?>
