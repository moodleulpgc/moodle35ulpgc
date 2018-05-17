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
 * File for deviceanalytics_data_storage class
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use phpbrowscap\Browscap;

/**
 * Storage Class creates the object class element
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deviceanalytics_data_storage{

    /**
     * @var Object $instance holds the a instance of the class
     */
    private static $instance;

    /**
     * init the instance for events.php
     * @see db/events.php
     */
    public function __construct() {
        self::$instance = $this;
    }

    /**
     * init the instace for events.php
     * @return deviceanalytics_data_storage $instance
     * @deprecated
     */
    public static function getinstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * create the saved object
     * @return int object id form db table
     */
    public function deviceanalytics_user_loggedin() {
        global $USER;
        global $DB;
        global $CFG;

        $pluginsetting = $DB->get_record('report_deviceanalytics', array(), '*');
        if ($pluginsetting->status == 0) {
            return 0;
        }
        if ($pluginsetting->adminlog == 0) {
            if ($this->getusersystemrole($USER->id) == 'admin') {
                return 0;
            }
        }

        $currentdevicedata = new deviceanalytics_data_object();
        if ($pluginsetting->anonymous == 0) {
            $currentdevicedata->userid = $USER->id;
        }
        $currentdevicedata->userhash = deviceanalytics_data_object::get_identify_hash($_SESSION['USER']);
        $alreadyexits = $DB->get_record_sql('SELECT * FROM {report_deviceanalytics_data} WHERE userhash = ?',
            array($currentdevicedata->userhash));

        if (!empty($alreadyexits) || is_null($alreadyexits)) {
            return 0;
        }

        $currentdevicedata->userrole = $this->getusersystemrole($USER->id);
        $currentdevicedata->objectdate = time();
        $currentdevicedata->activemoodlelang = $USER->lang;

        $browscap = new Browscap($CFG->dataroot.'/cache/');
        $browscap->doAutoUpdate = false;
        $info = $browscap->getBrowser();

        $devicetype = $info->Device_Type;
        $currentdevicedata->devicetype = $devicetype;
        $currentdevicedata->devicebrand = $info->Parent;
        $currentdevicedata->devicesystem = $info->Platform;
        $currentdevicedata->devicebrowser = $info->Browser;
        $currentdevicedata->devicebrowserversion = $info->Version;
        $currentdevicedata->devicepointingmethod = $info->Device_Pointing_Method;

        $insertid = $DB->insert_record('report_deviceanalytics_data', $currentdevicedata, true);
        return $insertid;
    }

    /**
     * insert the js enabled sizes
     * @see ajaxcall.php
     * @param int $insertid row_id
     * @param int $devicedisplaysizex display width
     * @param int $devicedisplaysizey display height
     * @param int $devicewindowsizex window width
     * @param int $devicewindowsizey window height
     */
    public function report_deviceanalytics_update_screensize(
        $insertid,
        $devicedisplaysizex,
        $devicedisplaysizey,
        $devicewindowsizex,
        $devicewindowsizey) {
        global $DB;
        $recordwithout = $DB->get_record('report_deviceanalytics_data', array('id' => $insertid), '*');
        $recordwithout->devicedisplaysizex = $devicedisplaysizex;
        $recordwithout->devicedisplaysizey = $devicedisplaysizey;
        $recordwithout->devicewindowsizex = $devicewindowsizex;
        $recordwithout->devicewindowsizey = $devicewindowsizey;

        $DB->update_record('report_deviceanalytics_data', $recordwithout);
    }

    /**
     * check if user is admin
     * @return String isadmin
     * @param int $userid userid
     */
    private function getusersystemrole($userid) {
        if (is_siteadmin($userid)) {
            return "admin";
        } else {
            return "user";
        }
    }
}