<?php //$Id: managedefaulter/index.php, 2010/04/05 $
/**
* gestión de usuarios morosos
*/

require_once('../../../../config.php');
//require_once($CFG->libdir.'/adminlib.php');
require_once('lib.php');

/**
 * INICIO DE PAGINA Y COMPROBACION DE PERMISOS
 */
	//admin_externalpage_setup('userbulk');
	require_login();

    $straction = get_string('defaultertopaidtitle', 'block_usermanagement');
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

    //print_heading(get_string('defaultertopaidtitle', 'block_usermanagement'), 'center', 3);
    echo $OUTPUT->heading(get_string('defaultertopaidtitle', 'block_usermanagement'));
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
		<h4  class="headerblock"><?php echo $coursename; ?></h4>
	</div>
	<?php } ?>
	<!--
    <h3 class="main"><?php echo get_string('defaultertopaidtitle', 'block_usermanagement'); ?></h3>
    -->
    <p style="text-align:center;font-size:14px">
		&#8226; <?php echo get_string('defaultertopaidbody', 'block_usermanagement'); ?>
	</p>
	<?php
	//obtener datos de estudiantes y cursos
	print_from_defaulter($userlist);
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
 * imprime en pantalla los usuarios enviados que pueden ser eliminados de la lista de morosos
 * y avisa de los usuarios seleccionados que no son morosos
 * @param array $userlist
 */
function print_from_defaulter($userlist)
{
	global $CFG;

	$action = optional_param('formAction', '', PARAM_TEXT);
	//si hay alumnos que pasar a pagados
	if(isset($action) && $action=='from_defaulter')
		from_defaulter(optional_param('defaulters', '', PARAM_TEXT));

	$course_defaulter = get_course_defaulter();

	//si no existe el curso de defaulters lo creamos
	if($course_defaulter == false)
	{
		print_new_course($userlist,'from_defaulter');

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

			$defaulters = array();
			$num 		= 0;
			$error		= 0;
			$errordiv	= '<div class="errorbox">';

			foreach($userlist as $id => $fullname)
			{
				if(!defaulter($id))
				{
					$error++;
					$user 	= $fullname;
					$userid = $id;
					$errordiv	.= '<b>- '.$user.'</b> '.get_string('notisdefaulter', 'block_usermanagement').'<br/>';
				}
				if(defaulter($id))
				{
					$num++;
					$user 	= $fullname;
					$userid = $id;

					$defaulters[count($defaulters)] = $userid;

					echo '<b>- '.$user.'</b><br/>';
				}
			}

			if($error > 0)
			{
				echo '<br/>'.get_string('notselecteddefaulters', 'block_usermanagement');
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
				<input type="hidden" name="defaulters" id="defaulters" value="<?php echo serialize($defaulters); ?>"/>
				<input type="hidden" name="formAction" id="formAction" />
				<input 	type="button" name="cancel" value="◄ Cancelar"
						onclick="location.href='<?php echo $CFG->wwwroot;?>/blocks/usermanagement/manageusers.php';"/>
				<input 	type="button" value="a Pagados ►" id="from_defaulter"
						<?php if($num==0){ ?> disabled="true" <?php } ?>
					   	onclick="document.getElementById('formAction').value='from_defaulter';
					   			if(confirm('Va a pasar a los usuarios morosos a la lista de pagados\n¿Está seguro?'))
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


function from_defaulter($defaulters)
{
	global $CFG, $DB;

	$defaulters = unserialize($defaulters);
	$studentroleid = get_student_role(); // ecastro ULPGC "5" is not guaranteed to be student

	if (!confirm_sesskey()) {error('Bad Session Key');}

    // we use only miulpgc enrol plugin here, if it is disabled no enrol is done
    if (enrol_is_enabled('miulpgc')) {
        $miulpgc = enrol_get_plugin('miulpgc');
    } else {
        $miulpgc = NULL;
    }

	if(isset($defaulters) && is_array($defaulters) && $miulpgc)
	{
		$course_defaulter = get_course_defaulter();
		$instances = enrol_get_instances($course_defaulter->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'miulpgc') {
                $defaulter_instance = $instance;
                break;
            }
        }

		foreach($defaulters as $key => $userid)
		{
			//borramos la matricula en el curso defaulter
			//role_unassign($studentroleid, $userid, 0, $course_defaulter->id);
			$miulpgc->unenrol_user($defaulter_instance, $userid);

			//inscribir cada alumno en su curso nuevamente
			$sql = 'SELECT course FROM {admin_moroso}
					WHERE userid = ?';
			$records = $DB->get_records_sql($sql,array($userid), $limitfrom=0, $limitnum=1000);

			foreach($records as $record)
			{
				$course	= $record->course;
                $instances = enrol_get_instances($course, true);
                $course_instance= null;
                foreach ($instances as $instance) {
                    if ($instance->enrol === 'miulpgc') {
                        $miulpgc->enrol_user($instance, $userid, $studentroleid);
                        break;
                    }
                }
				//volvemos a matricular al alumno en sus cursos
				//role_assign($studentroleid, $userid, 0, $course, time());

			}

			//sacar al alumno de la tabla de defaulters
			$table = 'admin_moroso';
			if (!$DB->delete_records($table, array('userid'=>$userid))) {
				error('Error en delete');
			}
		}

		/**
		 *Vaciamos el array de usuarios seleccionados
		 */
		unset($GLOBALS["HTTP_SESSION_VARS"]["SESSION"]->bulk_users);

		redirect($CFG->wwwroot.'/blocks/usermanagement/manageusers.php',
				'<div class="noticebox notifysuccess">'.get_string('notifydefaultertopaid', 'block_usermanagement').'</div>');
	}
}


?>