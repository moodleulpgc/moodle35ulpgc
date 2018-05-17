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
 * File for deviceanalytics_data_object class
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class which holds all saveable data
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deviceanalytics_data_object{

    /**
     * @var int $userid ID of the user
     */
    public $userid;

    /**
     * @var String $userrole "userrole" check if is admin
     */
    public $userrole;

    /**
     * @var int $objectdata currenttime in timestamp
     */
    public $objectdate;

    /**
     * @var String $activemoodlelang user used moodlelang
     */
    public $activemoodlelang;

    /**
     * @var String $userhash created hash
     */
    public $userhash;

    /**
     * @var String $devicetype type of device
     */
    public $devicetype;

    /**
     * @var String $devicesystem operating system
     */
    public $devicesystem;

    /**
     * @var String $devicebrowser current used browser
     */
    public $devicebrowser;

    /**
     * @var float $devicebrowserversion version of used browser
     */
    public $devicebrowserversion;

    /**
     * @var int $devicedisplaysizex width of devicescreen in px
     */
    public $devicedisplaysizex;

    /**
     * @var int $devicedisplaysizey height of devicescreen in px
     */
    public $devicedisplaysizey;

    /**
     * @var int $devicewindowsizex width of browser in px
     */
    public $devicewindowsizex;

    /**
     * @var int $devicewindowsizey height of browser in px
     */
    public $devicewindowsizey;

    /**
     * @var String $devicepointingmethod interation method
     */
    public $devicepointingmethod;

    /**
     * @var String $httpuserag http user agent
     */
    public $httpuserag;

    /**
     * @var String $httpssl is ssl used
     */
    public $httpssl;

    /**
     * @var String $httpacclang http active lang
     */
    public $httpacclang;

    /**
     * Construct for object
     */
    public function __construct() {
        $this->httpuserag = htmlentities($_SERVER['HTTP_USER_AGENT']);
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $this->httpssl = $_SERVER['HTTPS'];
        } else {
            $this->httpssl = 'off';
        }
        $this->httpacclang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    /**
     * Creating a hash based on userrsession - used for anonymization
     * @param moodle_session $usersession
     * @return String hash
     */
    public static function get_identify_hash($usersession) {
        $usersession = $usersession->sesskey;
        $salt = sha1(md5($usersession));
        return $anonymoushash = md5($usersession.$salt);
    }

    /**
     * Debug function
     * @deprecated
     */
    public function _data_object_debug() {
        var_dump(get_object_vars($this));
    }
}