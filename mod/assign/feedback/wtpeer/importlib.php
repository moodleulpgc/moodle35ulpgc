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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package assignfeedback_wtpeer
 * @copyright  2016 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir . '/csvlib.class.php');

/**
 * CSV Grade importer
 *
 * @package assignfeedback_wtpeer
 * @copyright  2016 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_marker_importer {

    /** @var string $importid - unique id for this import operation - must be passed between requests */
    public $importid;

    /** @var csv_import_reader $csvreader - the csv importer class */
    private $csvreader;

    /** @var assignfeedback_wtpeer $plugin - the pluginclass class */
    private $plugin;

    /** @var int $markerindex the column index containing the grades */
    private $markerindex = -1;

    /** @var int $itemindex the column index containing the unique id  */
    private $itemindex = -1;
    
    /** @var int $idindex the column index containing the unique id  */
    private $idindex = -1;

    /** @var int $groupid the group this importer affects to */
    private $groupid = 0;

    /** @var array $validusers only the enrolled users with the correct capability in this course */
    private $validusers;

    /** @var array $validmarkers only the enrolled users with the correct gradig capability in this course 
      *             associative array keyed by item                                                        */
    private $validmarkers;

    /** @var string $encoding Encoding to use when reading the csv file. Defaults to utf-8. */
    private $encoding;

    /** @var string $separator How each bit of information is separated in the file. Defaults to comma separated. */
    private $separator;

    /**
     * Constructor
     *
     * @param string $importid A unique id for this import
     * @param assign $assignment The current assignment
     */
    public function __construct($importid, assign_feedback_wtpeer $plugin, $encoding = 'utf-8', $separator = 'comma', $groupid = 0) {
        $this->importid = $importid;
        $this->plugin = $plugin;
        $this->encoding = $encoding;
        $this->separator = $separator;
        $this->groupid = $groupid;
    }

    /**
     * Parse a csv file and save the content to a temp file
     * Should be called before init()
     *
     * @param string $csvdata The csv data
     * @return bool false is a failed import
     */
    public function parsecsv($csvdata) {
        $this->csvreader = new csv_import_reader($this->importid, 'assignfeedback_wtpeer');
        $this->csvreader->load_csv_content($csvdata, $this->encoding, $this->separator);
    }

    /**
     * Initialise the import reader and locate the column indexes.
     *
     * @return bool false is a failed import
     */
    public function init() {
        if ($this->csvreader == null) {
            $this->csvreader = new csv_import_reader($this->importid, 'assignfeedback_wtpeer');
        }
        $this->csvreader->init();

        $columns = $this->csvreader->get_columns();
        $struser = core_text::strtolower(get_string('user'));
        $strmarker = core_text::strtolower(get_string('marker', 'assign'));
        $stritem = core_text::strtolower(get_string('assessment', 'assignfeedback_wtpeer'));

        if ($columns) {
            foreach ($columns as $index => $column) {
                if (core_text::strtolower($column) == $struser) {
                    $this->idindex = $index;
                }
                if (core_text::strtolower($column) == $strmarker) {
                    $this->markerindex = $index;
                }
                if (core_text::strtolower($column) == $stritem) {
                    $this->itemindex = $index;
                }
            }
        }
        
        if ($this->idindex < 0 || $this->markerindex < 0 || $this->itemindex < 0) {
            return false;
        }

        $this->validusers = $this->plugin->get_assignment()->list_participants($this->groupid, true);
        $weights = $this->plugin->get_assessment_weights();
        foreach($weights as $item => $weight) {
            if($weight) {
                $this->validmarkers[$item] = get_enrolled_users($this->plugin->get_assignment()->get_context(), 
                                                                    'assignfeedback/wtpeer:'.$item.'grade', 0, 'u.id, u.username, u.idnumber', null, 0, 0, true); 
                $this->items[$item] = core_text::strtolower(get_string('row'.$item, 'assignfeedback_wtpeer'));
            }
        }
        $this->realseparator = csv_import_reader::get_delimiter($this->separator);
        
        print_object($this->validusers);
        print_object($this->validmarkers);
        
        
        return true;
    }

    /**
     * Return the encoding for this csv import.
     *
     * @return string The encoding for this csv import.
     */
    public function get_encoding() {
        return $this->encoding;
    }

    /**
     * Return the separator for this csv import.
     *
     * @return string The separator for this csv import.
     */
    public function get_separator() {
        return $this->separator;
    }

    /**
     * Get the next row of data from the csv file (only the columns we care about)
     *
     * @return stdClass or false The stdClass is an object containing user, grade and lastmodified
     */
    public function next() {
        global $DB;
        $result = new stdClass();
        while ($record = $this->csvreader->next()) {
            $userid = $DB->get_field('user', 'id', array('idnumber'=>$record[$this->idindex]));
            $markerid = $DB->get_field('user', 'id', array('idnumber'=>$record[$this->markerindex]));
            $item = array_search($record[$this->itemindex], $this->items);
            if ($item && array_key_exists($userid, $this->validusers) &&  array_key_exists($markerid, $this->validmarkers[$item])) {
                $result->marker = $markerid;
                $result->user = $userid;
                $result->item = $item;
            } else {
                $result->skip = implode($this->realseparator, $record);
            }
            return $result;
        }
        return false;
    }

    /**
     * Close the grade importer file and optionally delete any temp files
     *
     * @param bool $delete
     */
    public function close($delete) {
        $this->csvreader->close();
        if ($delete) {
            $this->csvreader->cleanup();
        }
    }
}

