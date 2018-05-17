<?php
/**
 * Carga las fotos de la plataforma 201617 en la plataforma 201718
 *
 * Busca los usuarios con foto del curso anterior por username (dni),
 * y si tenía foto la recupera para este curso
 *
 */

define ( 'CLI_SCRIPT', true );
$dir = dirname ( __FILE__ );
require_once ($dir . '/../../config.php');
// require_once ("$CFG->dirroot/lib/dmllib.php");
require_once ($CFG->libdir . '/gdlib.php');

// Obtenemos todos los usuarios sin foto
$usrs = $DB->get_records_sql ( 'SELECT id, username, picture FROM {user} WHERE picture=0' );
$cont = '';

// Vamos seleccionando los usuarios
foreach ( $usrs as $user ) {
	// Obtenemos la foto desde la plataforma del año anterior
	if (! ($file = pathFotoPasada ( $user->username ))) {
		echo 'no hay foto para ' . $user->username . "\n";
		continue;
	}
	// Copia la IMAGENES
	// Adaptado del script /admin/tool/uploaduser/picture.php
	if ($newrev = my_save_profile_image ( $user->id, $file )) {
		$DB->set_field ( 'user', 'picture', $newrev, array (
				'id' => $user->id
		) );
		echo 'foto cargada para el usuario ' . $user->username . "\n";
	} else {
		echo 'fallo al cargar la foto para el usuario ' . $user->username . "\n";
	}
}

function pathFotoPasada($username) {
	global $CFG;

	if ($CFG->plataforma == 'tf') {
		$ant_dbhost = 'bdcv2.ulpgc.es';
		$ant_dbname = 'teleformacion_1617';
		$ant_dataroot = '/teleformacion_2017';
		$ant_prefix = 'ulp_';
		$ant_dbuser    = 'bdcv';
		$ant_dbpass    = 'bb_dd.19.cv';

	}
	if ($CFG->plataforma == 'tp') {
		$ant_dbhost = 'bdcv1.ulpgc.es';
		$ant_dbname = 'telepresencial_1617';
		$ant_dataroot = '/telepresencial_2017_replica1';
		$ant_prefix = 'ulp_';
		$ant_dbuser    = 'bdcv';
		$ant_dbpass    = 'bb_dd.19.cv';
	}

	$link = mysqli_connect ( $ant_dbhost, $ant_dbuser, $ant_dbpass, $ant_dbname ) or die ( 'No se pudo acceder al SGBD' );
	//mysql_select_db ( $ant_dbname, $link ) or die ( 'No se pudo acceder a la base de datos' );

	// Datos usuario en la plataforma anterior
	$sql = "SELECT id, username, picture FROM ${ant_prefix}user where username ='$username'";
	$datosPasado = mysqli_query ( $link, $sql );

	$dp = mysqli_fetch_row ( $datosPasado );
	if ($dp [2] != 0) {
		/*
		 * Esta consulta es más sencilla, pero depende de que la asignación al
		 * campo picture de la tabla user coincida con el id de la imagen en la
		 * tabla files $sql2 = "SELECT contenthash FROM ${ant_prefix}files c WHERE c.id =
		 * ".$dp[2];
		 */
		$sql2 = "SELECT contenthash
                 FROM ${ant_prefix}context a, ${ant_prefix}user b, ${ant_prefix}files c
                 WHERE b.username = '$username' AND a.contextlevel = '30' AND b.id = a.instanceid AND c.filearea = 'icon' AND c.contextid = a.id AND c.component = 'user' AND c.filename LIKE 'f1%'";
		$contenthashquery = mysqli_query ( $link, $sql2 );
		$contenthashrow = mysqli_fetch_row ( $contenthashquery );
		$contenthash = $contenthashrow [0];
		return $ant_dataroot . '/filedir/' . substr ( $contenthash, 0, 2 ) . '/' . substr ( $contenthash, 2, 2 ) . '/' . $contenthash;
	} else { // No tenía foto
		return false;
	}
}

/**
 * Función tomada del script /admin/tool/uploaduser/picture.php
 *
 * Try to save the given file (specified by its full path) as the
 * picture for the user with the given id.
 *
 * @param integer $id
 *        	the internal id of the user to assign the
 *        	picture file to.
 * @param string $originalfile
 *        	the full path of the picture file.
 *
 * @return mixed new unique revision number or false if not saved
 */
function my_save_profile_image($id, $originalfile) {
	$context = context_user::instance ( $id );
	return process_new_icon ( $context, 'user', 'icon', 0, $originalfile );
}

?>
