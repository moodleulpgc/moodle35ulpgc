<?php
/**
 * Extensión de sincronización de la ULPGC
 *
 * @package    local
 * @subpackage trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/////////////////////////////////////////////////////////////////////////////////
///  Called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$plugin->version  = 2018032001;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2015111602;  // Requires this Moodle version (Moodle 2.7)
$plugin->cron     = 0;           // Period for cron to check this module (secs)

$plugin->component = 'report_trackertools';
$plugin->maturity  = MATURITY_STABLE;

$plugin->release = '1.1';             // User-friendly version number
