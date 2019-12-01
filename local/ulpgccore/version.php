<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/////////////////////////////////////////////////////////////////////////////////
///  Called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$plugin->version  = 2019102500;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2015111602;  // Requires this Moodle version
$plugin->cron     = 3600;           // Period for cron to check this module (secs)

$plugin->component = 'local_ulpgccore';
$plugin->maturity  = MATURITY_STABLE; 


$plugin->release = '1.7'; // User-friendly version number
