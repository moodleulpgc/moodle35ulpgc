<?php

/**
 * This file contains the version information for the geogebra submission plugin
 *
 * @package        assignsubmission_geogebra
 * @author         Christoph Stadlbauer <christoph.stadlbauer@geogebra.org>
 * @copyright  (c) International GeoGebra Institute 2014
 * @license        http://www.geogebra.org/license
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2016092600;
$plugin->requires = 2014051200;
$plugin->dependencies = array(
        'qtype_geogebra' => 2014081906,
);

$plugin->component = 'assignsubmission_geogebra';

$plugin->maturity = MATURITY_STABLE;

$plugin->release = '1.0.2';