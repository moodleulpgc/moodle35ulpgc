<?php

/**
 * Plugin capabilities.
 *
 * @package        repository_geogebratube
 * @author         Christoph Stadlbauer <christoph.stadlbauer@geogebra.org>
 * @copyright  (c) International GeoGebra Institute 2014
 * @license        http://www.geogebra.org/license
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

        'repository/geogebratube:view' => array(
                'captype'      => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes'   => array(
                        'user' => CAP_ALLOW
                )
        )
);
