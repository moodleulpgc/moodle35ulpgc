<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Internal library of functions for module
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
require_once('admin/dashboard_time_form.php');

/**
 * Loads the saved data from report_deviceanalytics_data
 * @param int $starttime filters entries with starttime
 * @param int $endtime filters entries with endtime
 * @return Array $chartsdata of records or null
 */
function report_deviceanalytics_load_datas($starttime = null, $endtime = null) {
    global $DB;

    if (empty($starttime) || is_null($starttime)) {
        $conf = $DB->get_record('report_deviceanalytics', array());
        $starttime = $conf->starttime;
    }
    if (empty($endtime) || is_null($endtime)) {
        $endtime = time();
    }
    $chartsdata = $DB->get_records_sql('SELECT * FROM {report_deviceanalytics_data} WHERE objectdate >= ? AND objectdate <= ?',
        array($starttime, $endtime));
    if (! empty($chartsdata) || ! is_null($chartsdata)) {
        return $chartsdata;
    } else {
        return null;
    }
}

/**
 * Creates the Outputcontainer for dashboard.php
 * @param Array $chartout array of containers
 * @param Array $vtables array of html_tables
 * @return String all containers for output
 */
function report_deviceanalytics_create_containers($chartout, $vtables) {
    $vout = array();
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'device_types',
        'dashboard_chart_device_types',
        $chartout[0],
        $vtables[0]);
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'device_systems',
        'dashboard_chart_device_systems',
        $chartout[1],
        $vtables[1]);
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'device_browser',
        'dashboard_chart_device_browser',
        $chartout[2],
        $vtables[2]);
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'screen_sizes',
        'dashboard_chart_screen_sizes',
        $chartout[3],
        $vtables[3]);
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'window_sizes',
        'dashboard_chart_window_sizes',
        $chartout[4],
        $vtables[4]);
    $vout[] = report_deviceanaltics_create_wrapper_container(
        'pointing_method',
        'dashboard_chart_pointing_method',
        $chartout[5],
        $vtables[5]);
    return $vout;
}


/**
 * Check if object holds screen data
 * @param Object $var entryobject
 * @return bool true/false
 * @deprecated
 */
function report_device_analytics_is_not_null($var) {
    return !is_null($var->devicedisplaysizex);
}


/**
 * Calculate the number of subversion inside array
 * @param Array $datarray array ob records
 * @return int count of subversion
 */
function report_device_analytics_calc_numbers_of_version($datarray) {
    $returnnumber = 0;
    foreach ($datarray as $value) {
         $returnnumber += $value;
    }
    return $returnnumber;
}

/**
 * Calculate the percent for element inside array
 * @param Array $datarray array with prechecked elements
 * @param String $calckey calc elementkey
 * @return float percent of searched value
 */
function report_device_analytics_calc_percent($datarray, $calckey) {
    $groundvalue = 0;
    $procvalue = $datarray[$calckey];
    foreach ($datarray as $val) {
        $groundvalue += $val;
    }
    return number_format((($procvalue / $groundvalue) * 100) , 2);
}

/**
 * Same as report_device_analytics_calc_percent, but from subarray
 * @param Array $datarray array with prechecked elements
 * @param String $calckey calc elementkey
 * @return float percent of searched value
 */
function report_device_analytics_calc_percent_from_sub($datarray, $calckey) {
    $groundvalue = 0;
    $procsub = array();
    foreach ($datarray as $key => $subvalue) {
        $subval = 0;
        foreach ($subvalue as $value) {
            $subval += $value;
        }
        $procsub[$key] = $subval;
        $groundvalue += $subval;
    }
    return number_format((($procsub[$calckey] / $groundvalue) * 100) , 2);
}

/**
 * Creates the wrapper container for charts and tables - also write tables
 * @param String $wrappername name of the div wrapper
 * @param String $headername heading line - from moodle/lang
 * @param String $chartoutput holds all information for the charts
 * @param html_table $vtables table object for html_writer
 * @return String $oretrun output-string
 */
function report_deviceanaltics_create_wrapper_container($wrappername, $headername, $chartoutput, $vtables) {
    global $OUTPUT;
    $oreturn = $OUTPUT->heading(get_string($headername, 'report_deviceanalytics'), 4);
    $oreturn .= $OUTPUT->container_start('wrapper', $wrappername);
    if (! empty($chartoutput) || ! is_null($chartoutput)) {
        $oreturn .= $chartoutput;
    }
    if (! empty($vtables) || ! is_null($vtables)) {
        $oreturn .= html_writer::table($vtables);
    }
    $oreturn .= $OUTPUT->container_end();
    return $oreturn;
}

/**
 * Calculate the Tables
 * @param Array $datas - preselected entries form data table
 * @return String $returntables output-string
 */
function report_deviceanalytics_create_data_tables($datas) {
    $returntables = array();
    $returntables[0] = new html_table();
    $returntables[0]->head = (array) get_strings(array('table_type', 'table_percent', 'table_count'), 'report_deviceanalytics');
    $devicetypes = array();
    foreach ($datas as $devicetypedata) {
        $type = $devicetypedata->devicetype;
        if (array_key_exists($type, $devicetypes)) {
            $devicetypes[$devicetypedata->devicetype]++;
        } else {
            $devicetypes[$devicetypedata->devicetype] = 1;
        }
    }
    foreach ($devicetypes as $tname => $count) {
        $returntables[0]->data[] = array($tname, report_device_analytics_calc_percent($devicetypes, $tname)."%", $count);
    }

    $returntables[1] = new html_table();
    $returntables[1]->head = (array) get_strings(array('table_os', 'table_percent', 'table_count'), 'report_deviceanalytics');
    $deviceoses = array();
    foreach ($datas as $deviceosdata) {
        $type = $deviceosdata->devicesystem;
        if (array_key_exists($type, $deviceoses)) {
            $deviceoses[$deviceosdata->devicesystem]++;
        } else {
            $deviceoses[$deviceosdata->devicesystem] = 1;
        }
    }
    foreach ($deviceoses as $tname => $count) {
        $returntables[1]->data[] = array($tname, report_device_analytics_calc_percent($deviceoses, $tname)."%", $count);
    }

    $returntables[2] = new html_table();
    $returntables[2]->head = (array) get_strings(array('table_browser', 'table_percent', 'table_count'), 'report_deviceanalytics');
    $devicebrowser = array();

    foreach ($datas as $devicebrowserdata) {
        $browser = $devicebrowserdata->devicebrowser;
        $browserversion = $devicebrowserdata->devicebrowserversion;
        if (array_key_exists($browser, $devicebrowser)) {
            if (array_key_exists($devicebrowserdata->devicebrowserversion, $devicebrowser[$devicebrowserdata->devicebrowser])) {
                $devicebrowser[$devicebrowserdata->devicebrowser][$devicebrowserdata->devicebrowserversion]++;
            } else {
                $devicebrowser[$devicebrowserdata->devicebrowser][$devicebrowserdata->devicebrowserversion] = 1;
            }
        } else {
            $devicebrowser[$devicebrowserdata->devicebrowser] = array();
            $devicebrowser[$devicebrowserdata->devicebrowser][$devicebrowserdata->devicebrowserversion] = 1;
        }
    }
    foreach ($devicebrowser as $bname => $sub) {
        $returntables[2]->data[] = array(
            '<b>'.$bname.'</b>',
            report_device_analytics_calc_percent_from_sub($devicebrowser, $bname).'%',
            report_device_analytics_calc_numbers_of_version($sub)
        );
        foreach ($sub as $vnum => $scount) {
            $returntables[2]->data[] = array(
                get_string('table_version', 'report_deviceanalytics').': '.$vnum,
                report_device_analytics_calc_percent($sub, $vnum).'%',
                $scount
            );
        }
    }

    $returntables[3] = null;
    $returntables[4] = null;

    $returntables[5] = new html_table();
    $returntables[5]->head = (array) get_strings(array('table_pointing', 'table_percent', 'table_count'), 'report_deviceanalytics');
    $devicepointing = array();
    foreach ($datas as $devicepointdata) {
        $ptype = $devicepointdata->devicepointingmethod;
        if (array_key_exists($ptype, $devicepointing)) {
            $devicepointing[$devicepointdata->devicepointingmethod]++;
        } else {
            $devicepointing[$devicepointdata->devicepointingmethod] = 1;
        }
    }
    foreach ($devicepointing as $tname => $count) {
        $returntables[5]->data[] = array($tname, report_device_analytics_calc_percent($devicepointing, $tname)."%", $count);
    }

    return $returntables;
}

/**
 * Create Canvas Elements for the charts output
 * @return Array $returncharts array of canvas elements
 */
function report_deviceanalytics_create_charts() {
    $returncharts = array();
    $returncharts[0] = '<canvas class="rd_chart" id="chart_devicetypes"></canvas>';
    $returncharts[1] = '<canvas class="rd_chart" id="chart_devicesystems"></canvas>';
    $returncharts[2] = '<canvas class="rd_chart" id="chart_devicebrowsers"></canvas>';
    $returncharts[3] = '<canvas class="rd_chart" id="chart_devicedisplaysize"></canvas>';
    $returncharts[4] = '<canvas class="rd_chart" id="chart_devicewindowsize"></canvas>';
    $returncharts[5] = null;
    return $returncharts;
}