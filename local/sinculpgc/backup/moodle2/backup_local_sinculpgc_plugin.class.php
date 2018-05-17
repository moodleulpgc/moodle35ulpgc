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
 * Backup plugin class that provides the necessary information
 * needed to backup sinculpgc helper tables.
 */
class backup_local_sinculpgc_plugin extends backup_local_plugin {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // not working ??
    }
 
    protected function define_course_plugin_structure() {

    }
       
}