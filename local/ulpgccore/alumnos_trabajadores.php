<?php // $Id: alumnos_trabajadores.php,v 1.0 2009/10/28
$concabeceras=1;
    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');

    $unatitulacion = optional_param('unatitulacion', 0, PARAM_INT);

    $sitecontext = context_system::instance();
    $site = get_site();

    if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
        print_error('You do not have the required permission to edit/delete users.');
    }

    $titulaciones_validas = '(7,8,9,10,11,12,13,17)';

    $data = data_submitted();
    $exportar = (isset($data->exportar)) ? $data->exportar : null;

    if ($exportar) {
        header('Content-Type: application/octet-stream; charset=UTF-8');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename=alumnosTeleformacion.xls');
    } else {
        if ($concabeceras) {
            $PAGE->set_url(new moodle_url('/local/ulpgccore/alumnos_trabajadores.php'));
            $PAGE->set_context(context_system::instance());
            $PAGE->set_heading('Alumnos trabajadores'); // Required
            $PAGE->set_title('Alumnos trabajadores');
            $PAGE->navbar->ignore_active();
            $PAGE->navbar->add('Administraci&oacute;n', new moodle_url("$CFG->wwwroot/$CFG->admin/index.php"));
            $PAGE->navbar->add('Alumnos trabajadores');
        }
    }
    
    $sql = "SELECT cc.id, cc.name, uc.faculty, uc.degree
            FROM {course_categories} cc
            JOIN {local_ulpgccore_categories} uc ON cc.id = uc.categoryid
            WHERE cc.id IN $titulaciones_validas ";
    $titulaciones = $DB->get_records_sql($sql, null);
    //$titulaciones = $DB->get_records_select('course_categories',"id in $titulaciones_validas",null,'','id,name,faculty,degree');

    if (!$exportar) {
        if ($concabeceras) echo $OUTPUT->header();
        echo '<form method="post">';
        echo 'Filtrar por titulación:
<select name="unatitulacion" onChange="submit()">
    <option value="0">--Todas--</option>';
        foreach ($titulaciones as $tit) {
            $sel = ($tit->id == $data->unatitulacion) ? 'selected':'';
            echo "<option value='$tit->id' $sel>$tit->faculty $tit->degree - $tit->name</option>";
        }
        echo '</select><BR>';
        $checked = (isset($data->desglosar)) ? $data->desglosar : '';
        echo html_writer::checkbox('desglosar', 1, $checked, 'Desglosar detalle de actividades');
        echo ' <input name="dummy" value="Recalcular" type="submit" />';
        echo ' <input name="exportar" value="Exportar a excel" type="submit" />';
        echo '</form>';
    }

    $table = new html_table();

    if (isset($data->desglosar))
     $table->head = array ('Asignatura<BR> &nbsp; - Actividad',
       'C&oacute;digo',
       'N&ordm;actividades Junio<BR>total/activas<BR>N&ordm;acts.',
       'N&ordm;actividades Sept.<BR>total/activas<BR>N&ordm;acts.',
       'Entregaron<BR>en junio',
       'Entregaron en<BR>Septiembre',
       'Total alum.<BR>trabajadores',
       'Total alum.<BR>matriculados');
    else
     $table->head = array ('Asignatura',
       'C&oacute;digo',
       'N&ordm;actividades Junio<BR>total/activas',
       'N&ordm;actividades Sept.<BR>total/activas',
       'Entregaron<BR>en junio',
       'Entregaron en<BR>Septiembre',
       'Total alum.<BR>trabajadores',
       'Total alum.<BR>matriculados');

    $table->align = array ('left',   'center', 'center', 'center', 'right',  'right',  'right',  'right');
    $table->wrap  = array ('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');

//    $table->width = '95%';
    $tit_actual=0;

    if ($unatitulacion) $que_titulaciones = "($unatitulacion)";
                   else $que_titulaciones = $titulaciones_validas;

    $secciones_junio = $DB->get_records_sql('SELECT c.id, s.id idseccion
FROM {course_sections} s 
JOIN {course} c ON c.id = s.course
JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid

WHERE c.category IN '. $que_titulaciones .'
 AND uc.credits > 0
 AND upper(s.name) LIKE "%PLAN DE ACTIVIDADES DE APRENDIZAJE ORDINARIO%"');
////// AND upper(s.name) LIKE "%PLAN DE ACTIVIDADES%"');

    $secciones_sept = $DB->get_records_sql('SELECT c.id, s.id idseccion
FROM {course_sections} s 
JOIN {course} c ON c.id = s.course
JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
WHERE c.category IN '. $que_titulaciones .'
 AND uc.credits > 0
 AND upper(s.name) LIKE "%PLAN DE ACTIVIDADES DE APRENDIZAJE EXTRAORD%"');
////// AND upper(s.name) LIKE "%PLAN DE RECUPERAC%"');

    $id_modulo_tarea = $DB->get_field('modules','id', array('name' => 'assign') );

    /*$cursos = $DB->get_records_select('course',"category in $que_titulaciones and credits <> 0",null,'category,sortorder','id,category,shortname,fullname');*/
    $selectsql = "SELECT c.id,c.category,c.shortname,c.fullname FROM {course} c JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid WHERE c.category in ".$que_titulaciones." and uc.credits <> 0 ORDER BY c.category,c.sortorder";
    $cursos = $DB->get_records_sql($selectsql); 
//echo 'Cursos<pre>'; print_r($cursos); echo '</pre>';
//echo 'Sec.junio<pre>'; print_r($secciones_junio); echo '</pre>';
//echo 'Sec.sept.<pre>'; print_r($secciones_sept); echo '</pre>';
    foreach ($cursos as $curso)
      if (isset($secciones_junio[$curso->id]->idseccion)
       && isset($secciones_sept [$curso->id]->idseccion)) {
        if ($tit_actual <> $curso->category) {
            $tit_actual = $curso->category;
            $table->data[] = array('<B>'.$titulaciones[$tit_actual]->faculty.' '.$titulaciones[$tit_actual]->degree.' - '.$titulaciones[$tit_actual]->name.'</B>');
        }
        // procesar un curso
        $table->data[] = array ('x');  // los datos se calculan despuï¿œs
        end($table->data);
        $elemento_actual = key($table->data);

        $alumnos = array();

        $totactjun=0;
        $numactjun=0;
        calcula_actividades(1, $secciones_junio, $totactjun, $numactjun);
        $totactsep=0;
        $numactsep=0;
        calcula_actividades(0, $secciones_sept,  $totactsep, $numactsep);

        // si en septiembre no hay ninguna tarea enviada, piensa que no hay actividades y todos salen como entregados (son todos >= 0 )
        if ( ! $numactsep ) $numactsep++;

//if ($curso->id == 287) { echo 'Alumnos<pre>'; print_r($alumnos); echo '</pre>'; }
//if ($curso->id == 287) { echo 'Datos<pre>'; print_r($numactjun); echo ' - '; print_r($numactsep); echo '</pre>'; }
        // contabilizar nº alumnos entregados
        $alu_jun=0;
        $alu_sep=0;
        $alu_tot=0;
        foreach ($alumnos as $k => $alumno) {
            $entrega_jun = 0;
            $entrega_sep = 0;
            if (!isset($alumno->jun)) $alumno->jun = -1;
            if (!isset($alumno->sep)) $alumno->sep = -1;
            if ($alumno->jun >= 0.75 * $numactjun ) $entrega_jun++;
            if ($alumno->sep >= $numactsep ) $entrega_sep++;
            if ($entrega_jun) $alu_jun++;
            if ($entrega_sep) $alu_sep++;
            if ($entrega_jun or $entrega_sep) $alu_tot++;  // si entrega en jun y sep, se le cuenta solo 1 vez

//if ($curso->id == 277) { echo "<BR> $alu_tot $k  jun $alumno->jun entjun $entrega_jun  sep $alumno->sep entsep $entrega_sep"; }

        }

    // El siguiente query a MySQL da el num. alumnos matriculados en una asignatura, que debe corresponder con el query en Oracle:
    //      select ii.acesea,ii.aadenc, ii.nasign,count(*) from tmomatriculas i, tmocursos ii
    //      where i.aacada='201011' and i.plataforma='tf' and i.idnumber=ii.idnumber  and nvl(ii.especial,'0')<>'1'
    //       and i.estado='I' and i.rol like 'student%'  and ii.aacada='201011' and ii.plataforma='tf'
    //      group by ii.aadenc, ii.acesea,ii.nasign
    //      order by ii.acesea, ii.aadenc, ii.nasign
        $num_matriculados = $DB->get_field_sql('SELECT  count(*)
             FROM {role_assignments} RA, {role} R, {context} C
            WHERE C.instanceid = '.$curso->id.'
              and R.shortname=\'student\' and RA.roleid=R.id
              and C.contextlevel=50 and RA.contextid=C.id
            group by C.instanceid');

        if ($exportar) {
            $curso->fullname = mb_convert_encoding($curso->fullname, 'UTF-16LE', 'UTF-8');
        }
        // en $tot/$num pongo un &nbsp; para que Excel no lo considere fecha (dia/mes)
        $table->data[$elemento_actual] = array ("<a href='$CFG->wwwroot/course/view.php?id=$curso->id'>$curso->fullname</a>",
                    $curso->shortname,
                    $totactjun.' /&nbsp;'.$numactjun,
                    $totactsep.' /&nbsp;'.$numactsep,
                    $alu_jun, $alu_sep, $alu_tot, $num_matriculados);
//        if (isset($data->desglosar)) $table->rowclass[$elemento_actual] = 'teacheronly';
//        if (isset($data->desglosar)) $table->rowclass[$elemento_actual] = '" style="color:#222222';
        end($table->data);
    }


    if (!empty($table)) {
        echo html_writer::table($table);
    }
    if (!$exportar) {
        if ($concabeceras) {
            echo $OUTPUT->footer();
        }
    }


function calcula_actividades($es_jun, $secciones, &$totact, &$numact) {
global $DB, $alumnos, $curso, $id_modulo_tarea, $data, $table;
        // Solo las visibles. En sept debería ser sólo 1
        $actividades = $DB->get_records_sql('select a.id, a.name, count(sub.assignment) c
                 from {course_modules} cm, {assign} a
                 left join {assign_submission} sub  on a.id=sub.assignment
                where cm.course='.$curso->id.'
                  and cm.module='.$id_modulo_tarea.'
                  and cm.visible = 1
                  and cm.instance=a.id
                  and cm.section='.$secciones[$curso->id]->idseccion.'
                group by a.id,a.name');
//if ($curso->id == 285) { echo 'Actividades<pre>'; print_r($actividades); echo '</pre>'; }
//if ($curso->id == 285) { echo 'Alumnos<pre>'; print_r($alumnos); echo '</pre>'; }
    // de cada actividad, coger solo 1 entrega. No coger la última (latest) porque algunas están no entregadas aunque ponga submiited
        foreach($actividades as $actividad) {
            $totact++;
            if ($actividad->c) $numact++;
            $numentregadas=0;
            $subs = $DB->get_records_sql("select distinct userid
                      from {assign_submission}
                     where status='submitted'
                       and assignment='$actividad->id'
                     union 
                      select userid from (     
                        select userid, max(attemptnumber) FROM {assign_grades}
                         where assignment='$actividad->id'
                           and grade is not null
                           and grade <> -1
                         group by userid
                      ) a 
                      ");
//if ($curso->id == 277) { echo "subs $es_jun<pre>"; print_r($subs); echo '</pre>'; }
            foreach($subs as $sub) {
                //$alumnos[$sub->userid]->jun++;
                if ($es_jun) {
                  if (isset($alumnos[$sub->userid]->jun) and isset($alumnos[$sub->userid]->sep))
                      $alumnos[$sub->userid]->jun++;
                  else {
                      $alumnos[$sub->userid] = new stdClass();
                      $alumnos[$sub->userid]->jun = 1;
                      $alumnos[$sub->userid]->sep = 0;
                  }
                } else {
                  if (isset($alumnos[$sub->userid]->jun) and isset($alumnos[$sub->userid]->sep))
                      $alumnos[$sub->userid]->sep++;
                  else {
                      $alumnos[$sub->userid] = new stdClass();
                      $alumnos[$sub->userid]->jun = 0;
                      $alumnos[$sub->userid]->sep = 1;
                  }
                }
                $numentregadas++;
            }
            $texto_jun_sep = ($es_jun) ? '' : ' sept.';
            if (isset($data->desglosar)) {
              if ($es_jun) $table->data[] = array ("&nbsp; - Actividad: $actividad->id - $actividad->name", '',     $actividad->c, '', $numentregadas,'');
              else    $table->data[] = array ("&nbsp; - Actividad sept: $actividad->id - $actividad->name", '', '', $actividad->c, '', $numentregadas,'');
            }
        }
}

?>
