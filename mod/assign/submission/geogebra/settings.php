<?php

/**
 * This file defines the admin settings for this plugin
 *
 * @package        assignsubmission_onlinetext
 * @author         Christoph Stadlbauer <christoph.stadlbauer@geogebra.org>
 * @copyright  (c) International GeoGebra Institute 2014
 * @license        http://www.geogebra.org/license
 */

$settings->add(new admin_setting_configcheckbox('assignsubmission_geogebra/default',
        new lang_string('default', 'assignsubmission_geogebra'),
        new lang_string('default_help', 'assignsubmission_geogebra'), 0));

