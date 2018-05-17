<?php //$Id: deletedefaulter.php, 2010/04/05 $
/**
* gestión de usuarios morosos
*/

require_once('../../../../config.php');
require_once('lib.php');

/**
 * INICIO DE PAGINA Y COMPROBACION DE PERMISOS
 */
	require_login();

    $straction = get_string('updatedefaultertitle', 'block_usermanagement');
    $navlinks = array();
    $navlinks = array(array('name' => get_string('administration'), 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc'));
    $navigation = build_navigation($navlinks);
    print_header($straction, $straction, $navigation);


    $systemcontext = context_system::instance();
    require_capability('block/usermanagement:manage', $systemcontext);

    $action = optional_param('action', 'none', PARAM_ALPHA);

    print_heading(get_string('updatedefaultertitle', 'block_usermanagement'), 'center', 3);

/**
 * FIN 
 */
	
print_new_course($foo, 'updatedefaulter');

/**
 * PIE DE LA PAGINA
 */
	print_footer();
/**
 * FIN PIE DE LA PAGINA
 */
	
	
	
?>