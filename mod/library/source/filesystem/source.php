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
 * Implementaton of the librarysource_filesystem plugin.
 *
 * @package    librarysource
 * @subpackage filesystem
 * @copyright  2019 Enrique  Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/library/source/sourcebase.php');


/**
 * A rule representing the time limit. It does not actually restrict access, but we use this
 * class to encapsulate some of the relevant code.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class librarysource_filesystem extends library_source_base {

    public static function make(quiz $quizobj, $timenow, $canignorefilesystems) {

        if (empty($quizobj->get_quiz()->filesystem) || $canignorefilesystems) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function description() {
        return get_string('quizfilesystem', 'librarysource_filesystem',
                format_time($this->quiz->filesystem));
    }

    public function end_time($attempt) {
        $timedue = $attempt->timestart + $this->quiz->filesystem;
        if ($this->quiz->timeclose) {
            $timedue = min($timedue, $this->quiz->timeclose);
        }
        return $timedue;
    }

    public function time_left_display($attempt, $timenow) {
        // If this is a teacher preview after the time limit expires, don't show the time_left
        $endtime = $this->end_time($attempt);
        if ($attempt->preview && $timenow > $endtime) {
            return false;
        }
        return $endtime - $timenow;
    }

    public function is_preflight_check_required($attemptid) {
        // Warning only required if the attempt is not already started.
        return $attemptid === null;
    }

    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {
        $mform->addElement('header', 'honestycheckheader',
                get_string('confirmstartheader', 'librarysource_filesystem'));
        $mform->addElement('static', 'honestycheckmessage', '',
                get_string('confirmstart', 'librarysource_filesystem', format_time($this->quiz->filesystem)));
    }
}