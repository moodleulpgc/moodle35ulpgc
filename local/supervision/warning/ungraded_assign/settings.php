<?php
// $plugin_settings is a mandatory name for including these plugin settings in admin tree
$options = array();
$options[0] = get_string('none');
$options[1] = get_string('all');
$options[2] = get_string('admininstances', 'local_supervision');

$plugin = 'supervisionwarning_ungraded_assign';

$settings->add(new admin_setting_configselect("$plugin/enabled", get_string('pluginname', $plugin),
                get_string('config_pluginname', $plugin), 0, $options));

$options[0] = get_string('all');
$options[1] = get_string('graded', $plugin);
$options[2] = get_string('gradenumeric', $plugin);
$options[3] = get_string('gradescale', $plugin);
$settings->add(new admin_setting_configselect("$plugin/grading", get_string('grading', $plugin),
                get_string('config_grading', $plugin), 0, $options));

$settings->add(new admin_setting_configtext("$plugin/threshold", get_string('threshold', $plugin), get_string('config_threshold', $plugin), 7, PARAM_INT));

$settings->add(new admin_setting_configcheckbox("$plugin/weekends", get_string('weekends', $plugin),
                get_string('config_weekends', $plugin), 0, PARAM_INT));



