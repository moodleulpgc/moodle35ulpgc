<?php

/**
**************************************************************************
**                                Chairman                              **
**************************************************************************
* @package mod                                                          **
* @subpackage chairman                                                  **
* @name Chairman                                                        **
* @copyright oohoo.biz                                                  **
* @link http://oohoo.biz                                                **
* @author Raymond Wainman                                               **
* @author Patrick Thibaudeau                                            **
* @author Dustin Durand                                                 **
* @license                                                              **
http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later                **
**************************************************************************
**************************************************************************/

require_once(__DIR__.'../../../config.php');
require_once('ajax_lib.php');

$function = required_param('function',PARAM_TEXT);
$params = required_param('params',PARAM_TEXT);

$parameters = explode(',',$params);

$no_parameters = sizeof($parameters);

switch($no_parameters){
    case 0:
        $function();
        break;
    case 1:
        $function($parameters[0]);
        break;
    case 2:
        $function($parameters[0],$parameters[1]);
        break;
}

?>
