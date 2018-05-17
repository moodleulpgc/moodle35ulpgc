<?php //$Id: deletedefaulter.php, 2010/04/05 $
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

    $straction = get_string('totaldeletedefaultertitle', 'block_usermanagement');
    $baseurl = new moodle_url('/blocks/usermanagement/manageusers.php');
    $context = context_course::instance(SITEID);
    $PAGE->set_context($context);
    $PAGE->set_url($baseurl);

    $navlinks = array();
    $navlinks = array(array('name' => get_string('administration'), 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc'));
    //$navlinks[] = array('name' => $strdepartments, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    echo $OUTPUT->header();
    //print_header($straction, $straction, $navigation);


    $systemcontext = context_system::instance();
    require_capability('block/usermanagement:manage', $systemcontext);

    $action = optional_param('action', 'none', PARAM_ALPHA);

    //print_heading(get_string('totaldeletedefaultertitle', 'block_usermanagement'), 'center', 3);
    echo $OUTPUT->heading(get_string('totaldeletedefaultertitle', 'block_usermanagement'));
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
	<!--
    <h3 class="main"><?php echo get_string('totaldeletedefaultertitle', 'block_usermanagement'); ?></h3>
    -->
	<p style="text-align:center;font-size:14px">
		&#8226; <?php echo get_string('totaldeletedefaulterbody', 'block_usermanagement'); ?>
	</p>
	<?php
	print_form_totalremove($userlist);
	?>
	<br />
<?php

/**
 * PIE DE LA PAGINA
 */
	echo $OUTPUT->footer();
	//admin_externalpage_print_footer();
/**
 * FIN PIE DE LA PAGINA
 */


////////////////////////////////////////////
//	BAJA DEFINITIVA						  //
////////////////////////////////////////////


function print_form_totalremove($userlist)
{
	global $CFG;

	$aux = optional_param('defaulters', '', PARAM_TEXT);

	if(isset($aux)){
		$defaulters = unserialize($aux);
	}

	//si se ha pulsado el botón procedemos a dar de baja
	if(isset($defaulters) && is_array($defaulters))
	{
		if (!confirm_sesskey()) {error('Bad Session Key');}

		if(count($defaulters) > 0)
		{
			foreach($defaulters as $defid)
			{
				total_remove($defid);
			}
			redirect($CFG->wwwroot.'/blocks/usermanagement/manageusers.php',
				'<div class="noticebox notifysuccess">'.get_string('notifytotaldelete', 'block_usermanagement').'</div>');
		}
	}

	$course_defaulter = get_course_defaulter();
	//si no existe el curso de defaulters lo creamos
	if($course_defaulter == false)
	{
		print_new_course($userlist,'totalremove');

	}
	else
	{
	?>
	<div align="center" class="generalbox box"style="background:#EFEFEF" >
	<form name="bajadefinitiva" method="post">
		<table cellpadding="6" class="generaltable generalbox groupmanagementtable boxaligncenter" >
			<tr>
			<td align="top" width="600">
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
			<td align="center" style="padding:8px">
				<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
				<input type="hidden" name="defaulters" id="defaulters" value="<?php echo serialize($defaulters); ?>"/>
				<input 	type="button" name="cancel" value="◄ Cancelar"
						onclick="location.href='<?php echo $CFG->wwwroot;?>/blocks/usermanagement/manageusers.php';"/>
				<input 	type="button" value="Dar de baja definitivamente ►"
						<?php if($num==0){ ?> disabled="true" <?php } ?>
						onclick="if(confirm('¿Está totalmente seguro?'))document.bajadefinitiva.submit();" />
			</td>
			</tr>
		</table>
	</div>
	</form>
	<?php
	}
}



?>