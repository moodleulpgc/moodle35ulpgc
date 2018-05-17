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
 * Post-install script for the quiz makeexam report.
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install script
 */
function xmldb_quiz_makeexam_install() {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $record = new stdClass();
    $record->name         = 'makeexam';
    $record->displayorder = 9000;
    $record->capability   = 'quiz/makeexam:view';

    if ($dbman->table_exists('quiz_reports')) {
        $DB->insert_record('quiz_reports', $record);
    } else {
        $DB->insert_record('quiz_report', $record);
    }

    // install official tags
    require_once($CFG->dirroot . '/tag/lib.php');
    $tags[] = get_string('tagvalidated', 'quiz_makeexam');
    $tags[] = get_string('tagrejected', 'quiz_makeexam');
    $tags[] = get_string('tagunvalidated', 'quiz_makeexam');
    foreach($tags as $tag) {
        $tag = trim($tag);
        if(!$tagid = tag_get_id($tag)) {
            $ids = tag_add($tag, 'official');
        } else {
            tag_type_set($tagid, 'official');
        }
    }

}
