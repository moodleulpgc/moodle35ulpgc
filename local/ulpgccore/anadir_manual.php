<?php
    // Jose Luis, ecastro ULPGC
  require_once (dirname ( __FILE__ ) . '/../../config.php');
  require_once $CFG->dirroot.'/local/ulpgccore/lib.php';
  require_once $CFG->libdir.'/adminlib.php';

  require_login();

  if (!$site = get_site()) {
    redirect('index.php');
  }

    $systemcontext = context_system::instance();
    require_capability('local/ulpgccore:upload', $systemcontext);

    if (!confirm_sesskey($USER->sesskey)) {
        print_error('confirmsesskeybad', 'error');
    }

    // repositorio donde están los manuales
    $manuales = get_config('local_ulpgccore','manuales');
    $manuales = $CFG->dataroot . $manuales . '/';

    //$titulaciones_validas = '('.$config->validcategories.')';
    $a_titulaciones_validas = $DB->get_records_select('course_categories','idnumber like "111_%"');
    $titulaciones_validas = '0';
    foreach ($a_titulaciones_validas as $t=>$tit) {
    $titulaciones_validas .= ','.$tit->id;
    }
    $titulaciones_validas = '('.$titulaciones_validas.')';

//  $config = get_config('block_examsulpgc');
//  $aacada = $config->annuality;
  $aacada = $CFG->aacada;

  $strexamcalls = 'Añadir Manual de una asignatura';
  $PAGE->set_context($systemcontext);
  $PAGE->set_url(new moodle_url("$CFG->wwwroot/local/ulpgccore/anadir_manual.php"));
  $PAGE->navbar->add(get_string('administration'), new moodle_url("$CFG->wwwroot/$CFG->admin/index.php"));
  $PAGE->navbar->add($strexamcalls);
  $PAGE->set_title($strexamcalls);
  echo $OUTPUT->header();


  $manfile = $_FILES['manfile']['tmp_name'];

  // para el desplegable de titulaciones
  //$titulaciones = $DB->get_records_select('course_categories','id in '.$titulaciones_validas);
  
  $titulaciones = local_ulpgccore_load_categories_details(array_keys($a_titulaciones_validas), 'c.*, uc.faculty, uc.degree ');
  foreach ($titulaciones as $t=>$tit) {
    $titulaciones[$t]->faculty_degree = $tit->faculty .'_'.$tit->degree.'_00_00';
  }

  $data = data_submitted();
  if (!isset($data->titu)) $data->titu = $t;  // inicialmente, coger la última titulación -más rápido-, en vez de TODAS (%)
//print_object($data);

  if ($data->titu=='%') {
    $asignaturas = array_keys($a_titulaciones_validas);
  } else {
    $asignaturas = array($data->titu);
  }
  list($insql, $params) = $DB->get_in_or_equal($asignaturas);
  $sql = "SELECT c.id, c.shortname, c.idnumber, c.fullname, c.category, uc.credits, uc.term, LEFT(c.shortname, 5) AS shortname5
            FROM {course} c 
            LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
            WHERE uc.credits > 0 AND c.category $insql ";
  $asignaturas = $DB->get_records_sql($sql, $params);

/*
  if ($data->titu=='%') {  // coger asignaturas de todas las titulaciones válidas
      $asignaturas = $DB->get_records_select('course','category in '.$titulaciones_validas.' and credits>0');
  } else {  // coger sólo asignaturas de la titulación elegida
      $asignaturas = $DB->get_records_select('course','category='.$data->titu.' and credits>0');
  }
  
  foreach ($asignaturas as $asignatura) {
    $asignatura->shortname5 = substr($asignatura->shortname,0,5);
  }
*/

  if (isset($data->rechazar)) {
    if (!isset($data->curso)) echo '<h3>Para borrar un examen debe marcar de qué asignatura</h3>';
    else {
      // borrar físicamente el fichero
      $course = $DB->get_record('course',array(id => $data->curso));
      $tit = $titulaciones[$course->category]->faculty_degree;
      $nombre_manual = $manuales."$tit/M-$tit".'-'.substr($course->shortname,0,5).'-'.$aacada.'.pdf';
      unlink($nombre_manual);
      echo '<div align="center"><h3>Se ha eliminado el manual '.$nombre_manual.'</h3></div>';
    }
  }

  if (isset($data->enviar)) {
    $fichero_OK = 0;
    if ( isset($manfile) && ($manfile != '') ){
      $fichero_OK = 1;  // suponemos que el fichero se ha subido bien, a menos que...
    }
    if ($fichero_OK) {	// preparar el directorio donde se guardan los pdf
      $course=$DB->get_record('course',array(id => $data->curso));
      $almacen = $manuales. $titulaciones[$course->category]->faculty_degree .'/';
      mkdir($almacen);
    }
    if ($fichero_OK) {	// Ver si el fichero se puede subir físicamente
      $f1=almacena_fichero('manfile');
    }
  }

//echo '<PRE>'; print_r($titulaciones); echo '</PRE>';
//echo $manuales.$titulaciones[$asignatura->category]->faculty_degree.'/';
  echo '
<h3>Añadir manual</h3>
<form name="filtro" method="post" action="anadir_manual.php" enctype="multipart/form-data">
<p>Filtrar por titulación:
<select name="titu" onChange="submit()">
    <option value="%">--Todas--</option>';

  foreach ($titulaciones as $tit) {
    $sel = ($tit->id == $data->titu) ? 'selected':'';
    $codtit = substr($tit->faculty_degree,4,4);
    echo "<option value='$tit->id' $sel>$codtit - $tit->name</option>";
  }

  echo '
</select>

Año académico: '.$aacada.'

</p>
  <HR>
<p>Seleccione a cuál de las siguientes asignaturas pertenece el manual que desea subir.</p>
<table border cellpadding="3">
	<tr align="center" style="text-align:center">';
  if ($data->titu=='%') echo '<th>Titulación</th>';
  echo '<th>Asignatura</th>
      <th>Fichero</th>
    </tr>';

  foreach ($asignaturas as $asignatura) {
    $checked = (isset($curso) && ($asignatura->id == $curso)) ? 'checked' : '';
    echo '<tr>';
    if ($data->titu=='%') echo '<td>'.$titulaciones[$asignatura->category]->name.'</TD>';
    echo '<td><label><input name="curso" type="radio" value="'.$asignatura->id.'" '.$checked.'>';
    echo $asignatura->shortname.' - '.$asignatura->fullname.'</TD>';
    echo '<TD align="center">';

    // Ver si existía manual para esta asignatura
    $almacen = $manuales.$titulaciones[$asignatura->category]->faculty_degree.'/';
    if ($ficheros=glob($almacen.'M-'.$titulaciones[$asignatura->category]->faculty_degree.'-'.$asignatura->shortname5.'*.pdf')) {
      foreach ($ficheros as $fichero) {
        $pathinfo=pathinfo($fichero);
        echo '<a href="sitefile.php?file='.$asignatura->idnumber.'&year='.$aacada.'">'.$pathinfo['basename'].'</a><BR>';
      }
    } else {
      echo '---';
    }

    echo "</td></tr>\n";

  }  // foreach asignaturas

  echo '</table>
<p>Sólo se admiten manuales en formato PDF.</p>
<table cellpadding="3" align="center" style="text-align:center">
<tr><td>Fichero:</td><td><input name="manfile" type="file"></td></tr>
<tr><td colspan="2"><input name="enviar" type="submit" value="Enviar manual" /></td></tr>
<tr><td colspan="2"><input name="rechazar" type="submit" value="Borrar manual existente" /></td></tr>
</table>
</form>';


//print_footer();
$OUTPUT->footer();

function almacena_fichero ($fichero)
{  // devuelve el nombre de fichero almacenado, o '' si hay algún problema
  global $almacen, $titulaciones, $asignaturas, $data, $aacada, $CFG, $ficheros_OK;

  // Verificar que el fichero tiene extensión pdf
  $nombre_archivo = $_FILES[$fichero]['name'];
  $extension = explode('.',$nombre_archivo);
  $num = count($extension)-1;
//		$tamano_archivo = $_FILES[$fichero]['size']; // Por si hay que comprobar el tamaño del archivo
  if (!($extension[$num] == 'pdf')) {
    $ficheros_OK = 0;
    notify('Solo se pueden subir ficheros en formato pdf');
    return false;
  } else {
    $micurso = $asignaturas[$data->curso];
    $nombre_manual = 'M-'.$titulaciones[$micurso->category]->faculty_degree.'-'.$micurso->shortname5.'-'.$aacada.'.pdf';
    $ruta_manual = $almacen.$nombre_manual;
//echo "[ ruta_manual = $ruta_manual ]";
//echo "[ tmp = ".$_FILES[$fichero]['tmp_name']." ]";
    if (move_uploaded_file($_FILES[$fichero]['tmp_name'], $ruta_manual)) {
      notify('Archivo cargado correctamente con nombre '.$nombre_manual);
      return $nombre_examen;
    } else {
      $ficheros_OK = 0;
      notify('Error añadiendo el archivo');
      return false;
    }
  }
}
?>
