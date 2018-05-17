<?php
require_once ("NuSOAP/nusoap.php");
// Recorre todos los usuarios y los mete en el LDAP
function gestion_ldap($entidad) {
	// Toma el fichero de usuarios user_online.txt y user_fundacion.txt y los va recorriendo
	// metiendolos en el LDAP
	

	if ($entidad == "ULPGC") {
		$file = fopen ( "/var/www/html/moodleulpgc/granja/user_online_utf-8.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			if ($correo == "") {
				$correo = $dni . "@teleformacion.es";
			}
			$nueva_plataforma = 1;
			if ($dni != "dni") {
				echo "<\n>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
		fclose ( $file );
	} 

	else if ($entidad == "FULP") {
		$file = fopen ( "/var/www/html/moodleulpgc/granja/am/users_online_fulp_utf-8.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			$nueva_plataforma = 1;
			if ($dni != "dni") {
				echo "<br><hr><br>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
		fclose ( $file );
	} 

	else if ($entidad == "actualizar_ldap_teleformacion") {
		$file = fopen ( "/var/www/html/moodleulpgc/granja/users_teleformacion_14.10.2005.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			$nueva_plataforma = 1;
			if ($dni != "dni") {
				echo "<br><hr><br>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
	} 

	else if ($entidad == "EVT") {
		$file = fopen ( "/var/www/html/moodleevt/granja/evt_gabi.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			$nueva_plataforma = 4;
			if ($dni != "dni") {
				echo "<br><hr><br>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
		fclose ( $file );
	} 

	else if ($entidad == "actualizar_ldap_teletrabajo") {
		$file = fopen ( "/var/www/html/moodleulpgc/granja/users_teletrabajo_13.10.2005.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			$nueva_plataforma = 4;
			if ($dni != "dni") {
				echo "<br><hr><br>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
		fclose ( $file );
	} else if ($entidad == "VARIOS") {
		$file = fopen ( "/var/www/html/moodleulpgc/granja/users_online_varios.txt", "r" );
		while ( ! feof ( $file ) ) {
			if (! ($linea = fgets ( $file ))) {
				exit ();
			}
			$cadena = explode ( "|", $linea );
			$dni = $cadena [0];
			$nombre = formalizar ( $cadena [3] );
			$apellidos = formalizar ( $cadena [4] );
			$correo = $cadena [5];
			$nueva_plataforma = 1;
			if ($dni != "dni") {
				echo "<br><hr><br>Vamos a llamar a alta_ldap con dni=" . $dni . " nombre=" . $nombre . " apellidos=" . $apellidos . "correo=" . $correo . "nueva_plataforma=" . $nueva_plataforma;
				if (! alta_ldap ( $dni, $nombre, $apellidos, $correo, $nueva_plataforma )) {
					echo "<br>->Error al gestionar el LDAP el usuario:" . $dni;
				}
			}
		}
		fclose ( $file );
	}
	
	return true;
}

// Toma los datos de un usuario (dni, nombre, apellidos, correo, plataforma) y gestiona su entrada en el LDAP
function alta_ldap($dni, $nombre, $apellidos, $correo, $nueva_plataforma) {
	
	// No se está utilizando la información relativa a las plataformas
	// Se mantiene el parámetro $nueva_plataforma por compatibilidad/futuras actualizaciones
	

	global $log;
	
	if ($dni == "") {
		return false;
	}
	if ($nombre == "") {
		return false;
	}
	if ($apellidos == "") {
		return false;
	}
	if ($correo == "") {
		return false;
	}
	if ($nueva_plataforma == "") {
		return false;
	}

	cliente4 ( $dni, $dni, $nombre, $apellidos, $nueva_plataforma, $correo );

	return true;
}

// Obtiene la plataforma a la que pertenece el cliente
function cliente2($dni) {
	
	$oSoapClient = new soapclient ( 'https://autorizacion.ulpgc.es/indextel.php', true );
	$aParametros = array ("usuario" => $dni, "credencial" => "forpretra" );
	$aRespuesta = $oSoapClient->call ( "ObtenerPlataformas", $aParametros );
	/*  $aRespuesta = $oSoapClient->call("ObtenerPlataformasPersonal", $aParametros);
	if ($aRespuesta <= 0)
	{
	$aRespuesta = $oSoapClient->call("ObtenerPlataformasEstudiante", $aParametros);
	if ($aRespuesta <= 0)
	{
	$aRespuesta = $oSoapClient->call("ObtenerPlataformasExternos", $aParametros);
	}
	}
	*/
	return utf8_encode ( $aRespuesta );
}

// Se utiliza para modificar las plataformas a la que pertenece el usuario.
function cliente3($dni, $nueva_plataforma) {
	$oSoapClient = new soapclient ( 'https://autorizacion.ulpgc.es/indextel.php', true );
	$aParametros = array ("usuario" => $dni, "credencial" => "forpretra", "nueva_plataforma" => $nueva_plataforma );
	
	$resultado = false;
	
	if ($aRespuesta = $oSoapClient->call ( "CambioPlataformasPersonal", $aParametros )) {
		$resultado = true;
	}
	if ($aRespuesta1 = $oSoapClient->call ( "CambioPlataformasEstudiante", $aParametros )) {
		$resultado = true;
	}
	if ($aRespuesta1 = $oSoapClient->call ( "CambioPlataformasExternos", $aParametros )) {
		$resultado = true;
	}
	
	if (! $resultado) {
		echo "<br><b>Error al cambiar la plataforma del usuario" . $dni . "</b><br><br>";
	}
	
	return $resultado;
}

function cliente4($dni, $clave, $nombre, $apellidos, $nueva_plataforma, $email) {
	// Se utiliza para agregar un nuevo usuario al LDAP de la rama de externos, de gente que no
	// pertence a la ULPGC
	global $log;
	
	$usuario ['rama'] = 'Externos';
	$usuario ['dni'] = $dni;
	$usuario ['credencial'] = $clave;
	$usuario ['nombre'] = $nombre;
	$usuario ['apellidos'] = $apellidos;
	$usuario ['mail'] = $email;
	$usuario ['comentario'] = 'Creado desde el campus virtual';
	
	$oSoapClient = new soapclient ( 'https://ldapws.ulpgc.es/index.php', true );
	$aParametros = array ("usuario" => $usuario, 'binddn' => 'cn=UsersOracleAdmin,dc=ulpgc,dc=es', 'bindpw' => 'UsOrAd.Ldap' );
	$aRespuesta = $oSoapClient->call ( "AltaUsuario", $aParametros );
	
	if ($aRespuesta == 0) {
		$log->add ( 'Alta del usuario '.$dni.' realizada satisfactoriamente en la rama de Externos' );
	} elseif ($aRespuesta == - 10) {
		$log->add ( 'El usuario ' . $dni . ' ya existe en LDAP.' );
	} else {
		$log->add ( 'Han habido problemas con el alta del usuario '.$dni.':' . $aRespuesta );
	}
	return $aRespuesta;
}

function probar_dni_ldap($dni) {
	$oSoapClient = new soapclient ( 'https://ldapws.ulpgc.es/index.php', true );
	$err = $oSoapClient->getError ();
	$aParametros = array ('usuario' => $dni, 'colectivo' => 'Todos', 'inicio' => 0 );
	$aRespuesta = $oSoapClient->call ( 'BusquedaUsuarios', $aParametros );
	if ($aRespuesta ['TotalResult']) {
		foreach ($aRespuesta['FichasLDAP'] as $ficha) {
			$id_usr=$ficha['dn'];
			$aParametros = array('identificador'=>$ficha['dn']);
			$cficha = $oSoapClient->call('FichaLDAPUsuario', $aParametros);
			$mailLDAP[]= $cficha['mail'];
		}
		return $mailLDAP;
	}
	else
		return 0;
}

// Toma los datos de los usuarios y crea un fichero del tipo user_txt
function crear_fichero_usuarios() {
	$link_teleformacion = mysql_connect ( $CFG->dbhost, $CFG->dbuser, $CFG->dbpass );
	$link_teletrabajo = mysql_connect ( $CFG->dbhost, $CFG->dbuser, $CFG->dbpass );
	
	$file = fopen ( "/var/www/html/moodleulpgc/granja/users_teleformacion_13.10.2005.txt", "w+" );
	
	mysql_select_db ( "moodle2006", $link_teleformacion );
	$sql = "SELECT username, firstname, lastname, email FROM mdl_user";
	$result = mysql_query ( $sql, $link_teleformacion ) or die ( mysql_error () . "crear_fichero_usuarios().1" );
	
	while ( $row = mysql_fetch_array ( $result ) ) {
		$external_key = $row [0];
		$username = $row [0];
		$password = $row [0];
		$firstname = $row [1];
		$lastname = $row [2];
		$email = $row [3];
		$phone2 = "";
		$addres = "";
		if ((strlen ( $username ) > 6) and (strlen ( $username ) < 10)) {
			$linea = $external_key . "|" . $username . "|" . $password . "|" . $firstname . "|" . $lastname . "|" . $email . "|" . $phone2 . "|" . $addres . "|\n";
			if (fwrite ( $file, $linea ) === FALSE) {
				echo "<br>->Error al escribir el archivo";
				exit ();
			}
		}
	}
	fclose ( $file );
	
	$file = fopen ( "/var/www/html/moodleulpgc/granja/users_teletrabajo_13.10.2005.txt", "w+" );
	mysql_select_db ( "moodleevt", $link_teletrabajo );
	$sql = "SELECT username, firstname, lastname, email FROM mdl_user";
	$result = mysql_query ( $sql, $link_teletrabajo ) or die ( mysql_error () . "crear_fichero_usuarios().2" );
	
	while ( $row = mysql_fetch_array ( $result ) ) {
		$external_key = $row [0];
		$username = $row [0];
		$password = $row [0];
		$firstname = $row [1];
		$lastname = $row [2];
		$email = $row [3];
		$phone2 = "";
		$addres = "";
		if ((strlen ( $username ) > 6) and (strlen ( $username ) < 10)) {
			$linea = $external_key . "|" . $username . "|" . $password . "|" . $firstname . "|" . $lastname . "|" . $email . "|" . $phone2 . "|" . $addres . "|\n";
			if (fwrite ( $file, $linea ) === FALSE) {
				echo "<br>->Error al escribir el archivo";
				exit ();
			}
		}
	}
	fclose ( $file );
}

function formalizar($cadena) {
	return base64_encode ( $cadena );
}

// Obiene una cadena donde se tiene que devolver lo que est� antes del primer "<"
function obtiene_respuesta_cliente2($cadena) {
	$pos = strpos ( $cadena, "<" );
	if ($pos > 0) {
		$plat = substr ( $cadena, 0, $pos );
		return $plat;
	}
	return $cadena;
}

?>
