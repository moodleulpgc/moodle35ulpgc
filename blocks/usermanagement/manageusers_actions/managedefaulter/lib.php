<?php  // $Id: lib.php $

//require_once('deletedefaulter.php');
////////////////////////////////////////////
//	FUNCIONES DE APOYO					  //
////////////////////////////////////////////

/**
 * imprime el formulario de creación del curso defaulter y
 * pasa los datos para la creación
 */
function print_new_course($userlist, $action)
{
	global $CFG, $DB;

	$course_category 	= optional_param('category', '', PARAM_TEXT);
	$course_fullname 	= optional_param('fullname', '', PARAM_TEXT);
	$course_shortname 	= optional_param('shortname', '', PARAM_TEXT);
	$course_import		= optional_param('importar', '', PARAM_TEXT);

	//si se han recogido los parámetros del formulario, creamos el curso defaulter
	if(($course_category) && ($course_fullname) && ($course_shortname))
	{
		if (!confirm_sesskey()) {error('Bad Session Key');}

		$course = $DB->get_field('course', 'id', array('shortname'=>$course_shortname));
		if(!$course)
		{
			create_course_defaulter($course_category, $course_fullname, $course_shortname,$course_import);

			if(exists_course_defaulter() != false)
			{
				/**
				 * la variable action nos informa desde donde se llamo a
				 * print_new_course, por lo que nos servira para devolver el control
				 * a ese punto una vez creado el curso
				 */
				switch($action)
				{
					case 'to_defaulter':
							print_to_defaulter($userlist);
							break;
					case 'from_defaulter':
							print_from_defaulter($userlist);
							break;
					case 'totalremove':
							print_form_totalremove($userlist);
							break;
					case 'updatedefaulter':
							redirect($CFG->wwwroot.'/blocks/usermanagement/manageusers.php',
							'<div class="noticebox notifysuccess"><b>[Curso actualizado]</b><br/><i>Espere por favor...</i></div>');
							break;
				}

			}
		}
		else
		{
			?>
			<div class="errorboxcontent">
				<b>YA EXISTE UN CURSO CON EL NOMBRE <?php echo $course_shortname; ?></b><br/>
				<i>por favor, pruebe con otro nombre</i>
			</div>
			<?php
		}
	}
	else
	{
		//creamos un formulario para crear un curso para gestionar defaulters
		$displaylist = array();
	    $parentlist = array();
	    make_categories_list($displaylist, $parentlist, 'moodle/course:create');

	    ?>
		<script language="javascript">
			<!--
			function valida_form(f)
			{
				if(f.fullname.value == '')
				{
					alert('Debe rellenar el nombre completo');
					return false;
				}
				if(f.shortname.value == '')
				{
					alert('Debe rellenar el nombre corto');
					return false;
				}
			}
			-->
		</script>
		<div class="generalbox box" style="background:#EFEFEF;padding-left:50px" >
			<p style="font-weight:bold;" ><?php echo get_string('createdefcourse', 'block_usermanagement'); ?></p>
			<div style="padding-left:100px">
			<div class="fcontainer clearfix">
			<form name="course_defaulter" method="post" onsubmit="v = valida_form(this);
																 if(v!=false){
															     document.course_defaulter.style.display='none';
															     }return v;">
				<div class="fitem">
				<div class="fitemtitle">
				Categor&iacute;a</div>
				<div class="felement fselect">
				<select name="category" >
				<?php
				foreach($displaylist as $categoryid => $category)
				{
					?>
					<option value="<?php echo $categoryid; ?>" ><?php echo $category; ?></option>
					<?php
				}
				?>
				</select></div></div>
				<div class="fitem">
				<div class="fitemtitle">
				<?php echo get_string('fullname', 'block_usermanagement'); ?></div>
				<div class="felement ftext">
				<input type="text" size="100" maxlength="100" name="fullname"
					   value="Hemos detectado incidencias relacionadas con el pago de su matrícula. Póngase en contacto con la Administración (Tfn: 928458095; 928457422)" /></div></div>
				<div class="fitem">
				<div class="fitemtitle">
				<?php echo get_string('shortname', 'block_usermanagement'); ?> </div>
				<div class="felement ftext">
				<input type="text" size="25" maxlength="50" name="shortname" value="IncidenciaMatricula" />
				<br/>
				Importar morosos
				<input type="radio" name="importar" value="1" checked="checked" /><b>Importar</b>
				&nbsp;&nbsp;&nbsp;
				<input type="radio" name="importar" value="0" ><b>Borrar</b>
				</div></div>
				<div class="fitem">
				<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
				<br/>
				<input 	type="button" name="cancel" value="◄ Cancelar"
						onclick="location.href='<?php echo $CFG->wwwroot;?>/blocks/usermanagement/manageusers.php';"/>
				<input type="submit" value="Crear curso ►" /></div>
			</form>
			</div>
			</div>
		</div>
		<?php
	}

}


/**
 * comprobamos si el usuario con $userid tiene matrícula como estudiante en el curso defaulter
 * @param $userid
 * @return bool
 */
function defaulter($userid)
{
	//es defaulter si consta como enrolado al curso de moroso
	$course_defaulter = get_course_defaulter();
	if (!$course_defaulter) return false;
	//$course_context = ($course_defaulter != false) ? $course_defaulter->id : -1;
	$context = context_course::instance($course_defaulter->id);

	$studentroleid = get_student_role();

	return (is_enrolled($context, $userid) && user_has_role_assignment($userid, $studentroleid, $context->id));
}

/**
 * obtenemos el id del curso defaulter
 * está almacenado en admin_moroso con userid = -1
 * @return int
 * @return bool
 */

function get_course_defaulter()
{
    global $DB;
	$courseid = $DB->get_field('admin_moroso', 'course', array('userid'=>-1));

	if(isset($courseid) && $courseid > 0)
	{
		/**
		 * hay que comprobar que existe el curso,
		 * porque puede que se haya borrado desde la administracion
		 */
		$result = $DB->get_record('course', array('id'=>$courseid));
		return $result;
	}
	else
		return false;
}


/**
 * TRUE si existe un curso con user == -1 en admin_moroso
 */
function exists_course_defaulter()
{
	$result = get_course_defaulter();
	if($result == false)return false;
	else return true;
}


/**
 * creamos un curso para la gestión de defaulters en la base de datos
 * @param int  $course_category
 * @param text $course_fullname
 * @param text $course_shortname
 */
function create_course_defaulter($course_category, $course_fullname, $course_shortname,$course_import)
{
	global $CFG, $DB;

	// if creating new defaulter course, delete any one in admin_moroso table
	$DB->delete_records('admin_moroso', array('userid'=>-1));

	//Creamos el curso para los defaulters
	$data = new stdClass();

	$data->category		= $course_category;
	$data->sortorder 	= '';
	$data->fullname 	= $course_fullname;
	$data->shortname 	= $course_shortname;
	$data->summary 		= 'Gestion de estudiantes morosos';
	$data->startdate 	= time();
	$data->enrollable 	= 0;
	$data->groupmode 	= 0;
	$data->visible 		= 1;
	$data->timemodified = time();
	$data->format 		= '';
	$data->lang 		= '';
	$data->theme 		= '';


	if (!create_course($data)) {
		error('coursenotcreated');
	}
	build_context_path(true);

	$course = $DB->get_field('course', 'id', array('shortname'=>$course_shortname));
	$contextid = $DB->get_field('context', 'id', array('instanceid'=>$course, 'contextlevel'=>50));

	//meter en la tabla admin_moroso
	//lo identificaremos por el userid = -1
	$table 			= 'admin_moroso';
	$aux 			= new stdClass();
	$aux->course 	= $course; //$contextid;
	$aux->userid 	= -1;

	$exists_course = exists_course_defaulter();

	//capturamos el curso moroso (si existe) antes de ser actualizado
	$course_defaulter = get_course_defaulter();
	$context_old = $course_defaulter->id;

	if($exists_course != false)
	{
		//actualizamos el curso de gestion de morosos vigente
		$DB->set_field('admin_moroso', 'userid', -2, array('userid'=>-1));

		/**
		 * hacemos una revision del historico de cursos morosos en admin_morosos
		 * si ya no tienen correspondencia con un registro en mdl_context
		 * no existe ese curso y lo podemos borrar
		 */
		$sql = "SELECT distinct course
				FROM {admin_moroso}
				WHERE userid = ? ";

		$courses = get_records_sql($sql, array(-2));

		foreach($courses as $course)
		{
			if(!course_exists($course->course))
			{
				//borramos el curso de admin_moroso
				if (!$DB->delete_records("admin_moroso", array('course'=>$course->course))){
					error('Error borrando!');
				}
			}
		}
	}

	//Insertamos el curso de morosos nuevo
	if (!$id = $DB->insert_record($table, $aux))
	{
		error('inserterror');
	}

	$course_defaulter = get_course_defaulter();
    $context = context_course::instance($course_defaulter->id);
    $course_context = $context->id;

    // we use only miulpgc enrol plugin here, if it is disabled no enrol is done
    if (enrol_is_enabled('miulpgc')) {
        $miulpgc = enrol_get_plugin('miulpgc');
    } else {
        $miulpgc = NULL;
    }
    $miulpgc->add_instance($course_defaulter);
    $instances = enrol_get_instances($course_defaulter->id, true);
    foreach ($instances as $instance) {
        if ($instance->enrol === 'miulpgc') {
            $defaulter_instance = $instance;
            break;
        }
    }


	/**
	 * Enrolamos los usuario con rol STAFF
	 * en el curso de morosos como EDITINGTEACHER o TEACHER
	 */
	$users = get_role_users(get_role_id('staff'),get_system_context());

	foreach($users as $user)
	{
		$userid = $user->id;

		if(!$rol = get_role_id('editingteacher'))
		{
			$rol = get_role_id('teacher');
		}

		$miulpgc->enrol_user($defaulter_instance, $userid, $rol);
	}

	/**
	 * por ultimo tratamos la opcion de importar o borrar
	 * los alumnos morosos existentes
	 */
	if($course_import)
	{
		/**
		* Enrolamos los morosos existentes en admin_moroso si el curso no existe
		* y se solicita expresamente mediante $course_import
		*/
		import_defaulters_to_course($course_defaulter->id);
	}
	else
	{
		/**
		 * NO es necesario mantener los morosos en admin_morosos,
		 * por lo que limpiamos la tabla
		 */
		clean_defaulters($context_old);
	}


	if(isset($course))
		return true;
	else
		return false;
}


function is_course_defaulter($courseid)
{
    global $DB;
	/**
	 * dado un indentificador de curso
	 * comprobamos si pertenece o no a un curso moroso
	 * vigente(-1) o del historico(-2)
	 */
	$sql = "SELECT count(course)
			FROM {admin_moroso}
			WHERE course = ?
			AND userid < 0";
	$count = $DB->count_records_sql($sql, array($courseid));
	if($count==0)return false;
	else return true;
}

function get_student_role() {
    global $CFG, $DB;
    $studentroleid = $DB->get_field('role', 'id', array('shortname'=>'student'));
    if(!$studentroleid) {
        $studentroleid = $CFG->gradebookroles;
    }
    if(!$studentroleid) {
        error('No está definido el rol de estudiante');
    }
    return $studentroleid;
}

function is_only_student($userid)
{
    global $DB;
	/**
	 * Contamos los roles asignados al usuario que sean distinto de (estudiante)
	 * si es mayor que 0 no es sólo estudiante (devuelve false)
	 */
	$studentroleid = get_student_role(); // ecastro ULPGC "5" is not guaranteed to be student
	$sql = "SELECT count(roleid) FROM {role_assignments} WHERE userid = ? AND roleid != ? ";
	$params = array($userid, $studentroleid);
	$result = $DB->count_records_sql($sql, $params);

	($result == 0)? $isonlystudent = true : $isonlystudent = false;

	return  $isonlystudent;
}


function get_defaulters()
{
    global $DB;
	$sql = "SELECT distinct userid
			FROM {admin_moroso}
			WHERE userid != -1
			  AND userid != -2";

	$defaulters = $DB->get_records_sql($sql, array(), $limitfrom=0, $limitnum=10000);

	return $defaulters;
}


function import_defaulters_to_course($courseid)
{
	/**
	 * Importamos los morosos existentes en el curso moroso vigente
	 * al curso moroso que estamos creando = $courseid
	 */

	$defaulters = get_defaulters();

    // we use only miulpgc enrol plugin here, if it is disabled no enrol is done
    if (enrol_is_enabled('miulpgc')) {
        $miulpgc = enrol_get_plugin('miulpgc');
    } else {
        $miulpgc = NULL;
    }
    $instances = enrol_get_instances($courseid, true);
    foreach ($instances as $instance) {
        if ($instance->enrol === 'miulpgc') {
            $defaulter_instance = $instance;
            break;
        }
    }



	if(isset($defaulters) && is_array($defaulters))
	{
		foreach($defaulters as $defaulter)
		{
			/**
			 * se enrolan con roleid para estudiante
			 */
			$studentroleid = get_student_role();
			$miulpgc->enrol_user($defaulter_instance, $defaulter->userid, $studentroleid);
		}
	}

}


function clean_defaulters($courseid)
{
    global $DB;
	/**
	 *Eliminamos los registros de usuarios(no de cursos) de admin_moroso
	 *Si resulta que ademas los usuarios no tienen ninguna ocurrencia
	 *en mdl_role_assignments lo deshabilitamos en el sistema
	 */

	$users = get_defaulters();
	$roleid = get_student_role(); //estudiante

	$exists_course = course_exists($courseid);

	foreach($users as $user)
	{
		$userid = $user->id;

		if(!$exists_course)
		{
			/**
			 * no existe el curso moroso actual en la base de datos (se ha borrado)
			 * si el usuario no consta con ninguna otra matricula lo deshabilitamos
			 */
			if(!user_has_role_assignment($userid,  $roleid))
			{
				/**
				 *borramos al usuario de admin_moroso, y ademas
				 *total_remove comprueba que el usuario no consta matriculado con otro rol,				 *
				 *si consta, no lo deshabilita del sistema, en caso contrario, si
				 */
				total_remove($userid);
			}
		}
		else
		{
			/**
			 * existe el curso moroso actual(NO se ha borrado)
			 * no borramos al usuario totalmente,
			 * solo lo sacamos de admin_moroso
			 */
			if (!$DB->delete_records("admin_moroso", array("userid"=>$userid))){
				error('Error borrando!');
			}
		}
	}
}

function course_exists($courseid)
{
    global $DB;
	/**
	 * si el curso no se ha borrado, debe aparecer
	 * en la tabla mdl_course
	 */
	return $DB->record_exists('course', array('id'=>$courseid));
}


function get_role_id($shortname)
{
    global $DB;
	/**
	 * Obtenemos el id de un rol por su nombre
	 */
	return $DB->get_field('role', 'id', array('shortname'=>$shortname));
}


function total_remove($userid)
{
    global $DB;
	//borramos al estudiante de la tabla de defaulter
	$table = 'admin_moroso';
	if (!$DB->delete_records($table, array('userid'=>$userid))){
		error('Error en delete');
	}

	//deshabilitamos el usuario alumno si no existe en el sistema con otro rol
	if(is_only_student($userid) && has_capability('moodle/user:delete', context_system::instance()))
	{
		$user = $DB->get_record('user', array('id'=>$userid));
		/**
		 * delete_user desenrola al usuario de todos sus roles e
		 * inhabilita al usuario (deleted = 1)
		 */
		delete_user($user);
	}
	else
	{
		/**
		 *tiene otro rol aparte de estudiante, por lo que no se elimina (deleted = 0)
		 */
		 $studentroleid = get_student_role(); // ecastro ULPGC "5" is not guaranteed to be student

		//borramos al alumno de TODOS los cursos matriculados (lo desenrolamos como estudiante únicamente)
		$table = 'role_assignments';
		if (!$DB->delete_records($table, array("userid"=>$userid, "roleid"=>$studentroleid))){
			error('Error en delete');
		}

	}

	/**
	*Vaciamos el array de usuarios seleccionados
	*/
	unset($GLOBALS["HTTP_SESSION_VARS"]["SESSION"]->bulk_users);

}


?>
