<?php
require_once (dirname(__FILE__) . '/../../config.php');
require_once (dirname(__FILE__) . '/../sinculpgc/locallib.php');

$sitecontext = context_system::instance();
$site = get_site();
    if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
        error('You do not have the required permission to edit/delete users.');
    }

$ruta = '/telepresencial_2016_replica1/backup1516/';
$prefijoarchivo = 'copia_de_seguridad-moodle2-course-';
$sufijoarchivo = '-nu.mbz';
$longitudprefijo = strlen($prefijoarchivo);
$longitudsufijo = strlen($sufijoarchivo);
$fields = 'id,idnumber';

$courses = $DB->get_records_menu('course', array(), '',$fields);
if (!$handle = opendir($ruta)) {
    die("No se pudo acceder al directorio $ruta");
}
?>
<table border="1">
	<tr>
	    <th>Fila</th>
		<th>Idnumber copia</th>
		<th>Asignatura archivo</th>
		<th>Idnumber en plataforma</th>
		<th>Vinculada a</th>
		<th>Se imparte</th>
	</tr>
<?php
$i=1;
$copias = array();
$extdb = db_init();
while ($archivo = readdir($handle)) //obtenemos un archivo y luego otro sucesivamente
{
    if (is_dir($ruta.$archivo))//verificamos si es o no un directorio
    {
        continue;
    }
    else
    {
        $archivoidnumber = substr(substr($archivo, strpos($archivo,'-',$longitudprefijo)+1), 0, -$longitudsufijo);
        $archivocampos = explode( '_', $archivoidnumber);
        $vinculo = '';
        $match = '';
        $imparte = '';


        if (count($archivocampos) == 7) { // el shortname está en la plataforma: cambio de plan/especialidad
            //echo "<tr><td>$archivoidnumber</td>";
            $archivonasign = $archivocampos[5];
            //echo "<td>$archivonasign</td><td>";
            $matches = array_filter($courses, function($var) use ($archivonasign) { return preg_match("/_$archivonasign/i", $var); });
            if ($matches) {
                $idnumberexist[] = $archivoidnumber;
                $match = implode(',', $matches);
            } else { // vinculadas
                $sqlcursosulpgc = "SELECT c.idnumber as vinculada, c2.idnumber as maestra from tmocursos c, tmocursos c2 where c.idnumber = '$archivoidnumber' and c2.id = c.vinculo";
                $cursosulpgc = get_rows_external_db($extdb, $sqlcursosulpgc, 'vinculada');
                if (isset ($cursosulpgc[$archivoidnumber]->maestra) ) {
                    $vinculo = $cursosulpgc[$archivoidnumber]->maestra;
                }
                $coursesvinculada[] = $archivonasign;
                $idnumbersvinculada[] = $archivoidnumber;

            if  ($vinculo == '') { // no se imparte
                $archivonasign = $archivocampos[5];
                /*$archivoacesea = $archivocampos[0];
                $archivoplan = $archivocampos[1];
                $archivoespecialidad = $archivocampos[2];*/
                $sqlcursosulpgc = "select idnumber
                                     from vmocursos c
                                    where c.nasign = '$archivonasign'";
                $cursosulpgc = get_rows_external_db($extdb, $sqlcursosulpgc, 'nasign');
                if (!isset($cursosulpgc[$archivonasign]->nasign)) {
                    $imparte = 'N';
                }
            }



        }
        $copias[] = array ('idnumber'=>$archivoidnumber,'nasign'=>$archivonasign,'idnumberplataforma'=>$match, 'vinculo'=>$vinculo, 'imparte'=>$imparte);
    }
}
}
foreach ($copias as $copia) {
echo "<tr><td>".$i++."</td><td>".$copia['idnumber']."</td><td>".$copia['nasign']."</td><td>".$copia['idnumberplataforma']."</td><td>".$copia['vinculo']."</td><td>".$copia['imparte']."</td></tr>";
}
echo "</table>";
db_close($extdb);
/*
echo '<h2>Idnumber no en la plataforma</h2>';

foreach ($idnumbersnotexist as $idnumbernotexist) {
    echo $idnumbernotexist."','";
}

echo '<h2>Nasign no en la plataforma</h2>';
foreach ($coursesnotexist as $coursenotexist) {
    echo $coursenotexist."','";
}
*/

?>