<?php

/**
 * Defines the capabilities used by the coursetemplating admin tools
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    /* allows the user to manage ULPGC settings */
    'local/ulpgccore:manage' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    /* allows the user to upload manuals & otherfiles ULPGC settings */
    'local/ulpgccore:upload' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    
    // Review and resort courses within a category
    'local/ulpgccore:categoryreview' => array(

        'riskbitmask' => RISK_XSS | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/category:update'
    ),

    // Edit a locked gradecategory
    'local/ulpgccore:gradecategoryedit' => array(
        'riskbitmask' => RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/category:update'
    ),

    // Edit name & description for section 0 in a course
    'local/ulpgccore:editsection0' => array(

        'riskbitmask' => RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Manage activities for section 0 in a course
    'local/ulpgccore:managesection0' => array(

        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Edit settings for a module 
    'local/ulpgccore:modedit' => array(

        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Delete a course module 
    'local/ulpgccore:moddelete' => array(

        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Move a course module 
    'local/ulpgccore:modmove' => array(

        'riskbitmask' => RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Duplicate a course module 
    'local/ulpgccore:modduplicate' => array(

        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),
    
    // Manage permission assigment on a course module 
    'local/ulpgccore:modpermissions' => array(

        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ),   

    // Manage permission assigment on a course module 
    'local/ulpgccore:modroles' => array(

        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:activityvisibility'
    ), 
);
