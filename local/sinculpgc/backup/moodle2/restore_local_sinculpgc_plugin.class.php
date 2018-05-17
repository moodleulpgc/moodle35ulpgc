<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage sinculpgc
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file manages backup of helper tables in the sinculpgc plugin


defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore sinculpgc helper tables.
 */
class restore_local_sinculpgc_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {

    }
    
}