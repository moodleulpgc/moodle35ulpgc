<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage trackertools
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;



// site wide report settings.
$settings = new admin_settingpage('report_trackertools_settings', get_string('settings','report_trackertools')); 

$settings->add(new admin_setting_configcheckbox('report_trackertools/enabledtrackertools', get_string('enabled','report_trackertools'), get_string('explainenabled','report_trackertools'), 0));


