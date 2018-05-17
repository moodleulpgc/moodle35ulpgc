<?php

/**
 * Defines the capabilities used by the coursetemplating admin tools
 *
 * @package    local
 * @subpackage sinculpgc
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    /* allows the user to manage ULPGC settings */
    'local/sinculpgc:manage' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

);
