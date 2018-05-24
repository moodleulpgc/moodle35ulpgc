<?php

/**
 * Global configuration settings for the tracker module.
 *
 * @package    mod
 * @subpackage tracker
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/*
$settings->add(new admin_setting_configcheckbox('scheduler_allteachersgrading', get_string('allteachersgrading', 'scheduler'),
    get_string('allteachersgrading_desc', 'scheduler'), 0));
**/

if ($ADMIN->fulltree) {
    $trackers = $DB->get_records_menu('tracker', null, 'name ASC', $fields='id, name');
    natcasesort($trackers);
    $options = array(0 => get_string('choose')) + $trackers;
    //$options = $options + $trackers;

    $settings->add(new admin_setting_configselect('tracker/sendtracker', get_string('sendtracker', 'tracker'),
                    get_string('configsendtracker', 'tracker'), 0, $options));


    $settings->add(new admin_setting_configtext('tracker/resolvingdays', get_string('resolvingdays', 'tracker'),
                       get_string('configresolvingdays', 'tracker'), 5, PARAM_INT));

    $settings->add(new admin_setting_configtext('tracker/closingdays', get_string('closingdays', 'tracker'),
                       get_string('configclosingdays', 'tracker'), 3, PARAM_INT));

    $settings->add(new admin_setting_configtime('tracker/runtimestarthour', 'runtimestartminute', get_string('cronruntimestart', 'tracker'), get_string('configcronruntimestart', 'tracker'), array('h' => 4, 'm' => 30)));

    $settings->add(new admin_setting_configtext('tracker/reportmaxfiles', get_string('reportmaxfiles', 'tracker'),
                       get_string('configreportmaxfiles', 'tracker'), 3, PARAM_INT));

    $settings->add(new admin_setting_configtext('tracker/developmaxfiles', get_string('developmaxfiles', 'tracker'),
                       get_string('configdevelopmaxfiles', 'tracker'), 5, PARAM_INT));


}
