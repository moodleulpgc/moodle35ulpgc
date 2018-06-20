<?php //$Id: todefaulter.php, 2010/04/05 $
/**
* gestión de usuarios morosos
*/

require_once('../../../../config.php');
require_once('lib.php');

/**
 * INICIO DE PAGINA Y COMPROBACION DE PERMISOS
 */
	//admin_externalpage_setup('userbulk');
	require_login();

    $straction = get_string('usertodefaultertitle', 'block_usermanagement');
    $baseurl = new moodle_url('/blocks/usermanagement/manageusers.php');
    $context = context_course::instance(SITEID);
    $PAGE->set_context($context);
    $PAGE->set_url($baseurl);

    $navlinks = array();
    $navlinks = array(array('name' => get_string('administration'), 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc'));
    //$navlinks[] = array('name' => $strdepartments, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    //print_header($straction, $straction, $navigation);
    echo $OUTPUT->header();


    $systemcontext = context_system::instance();
    require_capability('block/usermanagement:manage', $systemcontext);

    $action = optional_param('action', 'none', PARAM_ALPHA);

    //print_heading(get_string('usertodefaultertitle', 'block_usermanagement'), 'center', 3);
    echo $OUTPUT->heading(get_string('usertodefaultertitle', 'block_usermanagement'));
	//admin_externalpage_print_header();

/**
 * FIN
 */

	$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';
	if(!empty($SESSION->return_url)) {
	    $return = $SESSION->return_url;
	}

	if (empty($SESSION->bulk_users)) {
	    redirect($return);
	}

	// generate user name list
	/**
	 * userlist es del tipo [id] => firstname lastname
	 */
    list($insql, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $insql", $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    $usernames = implode('<br>', $userlist);


//cuerpo
?>
	<?php
	$course_defaulter = get_course_defaulter();
	if($course_defaulter != false){
		$coursename = $DB->get_field('course', 'fullname', array('id'=>$course_defaulter->id));
	?>
	<div class="header" style="text-align:center">
		<h4 class="headerblock"><?php echo $coursename; ?></h4>
	</div>
	<?php } ?>

    <h3 class="main"><?php echo get_string('usertodefaultertitle', 'block_usermanagement'); ?></h3>

    <p style="text-align:center;font-size:14px">
		&#8226; <?php echo get_string('usertodefaulterbody', 'block_usermanagement'); ?>
	</p>
	<?php
	//obtener datos de estudiantes y cursos
	print_to_defaulter($userlist);
	?>
	<br /><br />
<?php

/**
 * PIE DE LA PAGINA
 */
	echo $OUTPUT->footer();
	//admin_externalpage_print_footer();
/**
 * FIN PIE DE LA PAGINA
 */


/**
 * imprime en pantalla los usuarios enviados que pueden ser inscritos como morosos
 * y avisa de los usuarios seleccionados que no se pueden inscribir como morosos
 * @param array $userlist
 */
function print_to_defaulter($userlist)
{
	global $CFG;

	$action = optional_param('formAction', '', PARAM_TEXT);
	//si hay alumnos que pasar a defaulters
	if(isset($action) && $action=='to_defaulter')
		to_defaulter(optional_param('students', '', PARAM_TEXT));

	$course_defaulter = get_course_defaulter();

	//si no existe el curso de defaulters lo creamos
	if($course_defaulter == false)
	{
		print_new_course($userlist, 'to_defaulter');
	}
	else
	{

	?>

	<div align="center" class="generalbox box" style="background:#EFEFEF" >
	<form name="transference" method="post">
	<div>
		<table cellpadding="6" class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
			<tr>
			<td valign="top" width="600">
			<p style="text-align:left">
			<?php echo get_string('userselection', 'block_usermanagement'); ?>
			<div style="padding-left:20px">
			<?php

			$students = array();
			$num 		= 0;
			$error		= 0;
			$errordiv	= '<div class="errorbox">';

			$studentroleid = get_student_role(); // ecastro ULPGC "5" is not guaranteed to be student

			foreach($userlist as $id => $fullname)
			{
				//controlamos que no es defaulter y que está matriculado en algún curso
				//$accessinfo 	= get_user_access_sitewide($id);
				//$user_courses	= get_user_courses_bycap($id, 'moodle/course:view', $accessinfo,false);
				$user_courses  = enrol_get_users_courses($id);

				$is_student = false;
				foreach($user_courses as $user_course)
				{
					/**
					 * Hay que tener presente que los cursos morosos antiguos en los
					 * que el usuario aprarece enrolado no deben tenerse en cuenta
					 * como curso en el que está matriculado
					 */
					$is_student = ($is_student ||
									(user_has_role_assignment($id,$studentroleid,$user_course->ctxid) &&
										!is_course_defaulter($user_course->id)));
				}

				if(defaulter($id))
				{
					$error++;
					$user 	= $fullname;
					$userid = $id;
					$errordiv	.= '<b>- '.$user.'</b>  '.get_string('isdefaulter', 'block_usermanagement').'<br/>';
				}
				elseif(!$is_student)
				{
					$error++;
					$user 	= $fullname;
					$userid = $id;
					$errordiv	.= '<b>- '.$user.'</b>  '.get_string('notisstudent', 'block_usermanagement').'<br/>';
				}
				elseif(!defaulter($id) && count($user_courses) > 0 && $is_student)
				{
					$num++;
					$user 	= $fullname;
					$userid = $id;

					$students[count($students)] = $userid;

					echo '<b>- '.$user.'</b><br/>';
				}
			}

			if($error > 0)
			{
				echo '<br/>'.get_string('selecteddefaulters', 'block_usermanagement');
				echo $errordiv	.= '</div>';
			}

			?>
			</div>
			</p>
			</td>
			</tr>
			<tr>
			<td width="100px" align="center" style="vertical-align:middle">
				<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
				<input type="hidden" name="students" id="students" value="<?php echo serialize($students); ?>"/>
				<input type="hidden" name="formAction" id="formAction" />
				<input 	type="button" name="cancel" value="◄ Cancelar"
						onclick="location.href='<?php echo $CFG->wwwroot;?>/blocks/usermanagement/manageusers.php';"/>
				<input 	type="button" value="a morosos ►" id="amorosos"
						<?php if($num==0){ ?> disabled="true" <?php } ?>
					   	onclick="document.getElementById('formAction').value='to_defaulter';
								 if(confirm('Va a pasar a los usuarios a la lista de morosos\n¿Está seguro?'))
									document.transference.submit();"/>
			</td>
			</tr>
		</table>
	</div>
	</form>
	</div>
	<?php
	}

}

/**
 * inscribe a los alumnos en el curso defaulter
 * elimina las matrículas que tenía el alumno
 * almacena las matrículas que tenía el alumno en admin_moroso
 * @param array $students
 */
function to_defaulter($students)
{
	global $CFG, $DB;

	$students = unserialize($students);

	if (!confirm_sesskey()) {error('Bad Session Key');}
	$studentroleid = get_student_role(); // ecastro ULPGC "5" is not guaranteed to be student

    if (enrol_is_enabled('miulpgc')) {
        $miulpgc = enrol_get_plugin('miulpgc');
    } else {
        $miulpgc = NULL;
    }

	if(isset($students) && is_array($students))
	{
        $course_defaulter = get_course_defaulter();
        $instances = enrol_get_instances($course_defaulter->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'miulpgc') {
                $defaulter_instance = $instance;
                break;
            }
        }


		foreach($students as $key => $userid)
		{
			//recorremos los cursos matriculados por el usuario
// 			$sql = 'SELECT mdl_role_assignments.contextid course
// 					FROM mdl_role_assignments
// 					INNER JOIN mdl_role ON mdl_role.id = mdl_role_assignments.roleid
// 					INNER JOIN mdl_user ON mdl_user.id = mdl_role_assignments.userid
// 					WHERE mdl_user.id = ? AND mdl_role.id = ?
// 					ORDER BY mdl_role_assignments.contextid ASC';
//             $params = array($userid, $studentroleid);
			//$records = $DB->get_records_sql($sql, $params, $limitfrom=0, $limitnum=1000);

			$records = enrol_get_users_courses($userid);

			foreach($records as $record)
			{
				$contextid 	= $record->id;

				//registramos al defaulter y sus cursos actuales
				$table = 'admin_moroso';
				$aux = new stdClass();
				$aux->course 	= $contextid;
				$aux->userid 	= $userid;

				if(!is_course_defaulter($contextid))
				{
					if (!$id = $DB->insert_record($table, $aux)) {
							error('Error del insert');
					}
					//borramos todas las matrículas del alumno (solo rol estudiante)
// 					if (!delete_records("role_assignments", "userid", $userid, "roleid", $studentroleid, "contextid",$contextid)){
// 						error('Error en delete');
// 					}
                    $instances = enrol_get_instances($contextid, true);
                    $course_instance= null;
                    foreach ($instances as $instance) {
                        if ($instance->enrol === 'miulpgc') {
                            $miulpgc->unenrol_user($instance, $userid);
                            break;
                        }
                    }
				}
			}


			//matriculamos al alumno en el curso defaulter
			$course_defaulter = get_course_defaulter();
			//role_assign($studentroleid, $userid, 0, $course_defaulter->id, time());
			$miulpgc->enrol_user($defaulter_instance,$userid, $studentroleid);

		}

		/**
		 *Vaciamos el array de usuarios seleccionados
		 */
		unset($GLOBALS["HTTP_SESSION_VARS"]["SESSION"]->bulk_users);

		redirect($CFG->wwwroot.'/blocks/usermanagement/manageusers.php',
				'<div class="noticebox notifysuccess">'.get_string('notifyusertodefaulter', 'block_usermanagement').'</div>');
	}
}

?>
