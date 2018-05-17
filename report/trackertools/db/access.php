<?php

/**
 * Defines the capabilities used by the coursetemplating admin tools
 *
 * @package    local
 * @subpackage trackertools
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    /* allows the user to manage tracker tools settings */
    'report/trackertools:report' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    
    'report/trackertools:download' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'report/trackertools:export' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    
    'report/trackertools:import' => array(
        'riskbitmask' => RISK_MANAGETRUST|RISK_PERSONAL|RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    
    'report/trackertools:manage' => array(
        'riskbitmask' => RISK_MANAGETRUST|RISK_PERSONAL|RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'report/trackertools:warning' => array(
        'riskbitmask' => RISK_MANAGETRUST|RISK_PERSONAL|RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    
);
