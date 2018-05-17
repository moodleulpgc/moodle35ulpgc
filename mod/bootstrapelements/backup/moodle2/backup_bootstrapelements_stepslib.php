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
 * Version details
 *
 * @package    mod
 * @subpackage bootstrapelements
 * @copyright  2014 Birmingham City University <michael.grant@bcu.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_bootstrap_activity_task
 */

/**
 * Define the complete bootstrap structure for backup, with file and id annotations
 */
class backup_bootstrapelements_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $bootstrapelements = new backup_nested_element('bootstrapelements', array('id'), array(
            'name', 'intro', 'introformat', 'timemodified', 'title', 'bootstraptype', 'bootstrapicon'));

        $bootstrapelements->set_source_table('bootstrapelements', array('id' => backup::VAR_ACTIVITYID));

        $bootstrapelements->annotate_files('mod_bootstrapelements', 'intro', null);

        return $this->prepare_activity_structure($bootstrapelements);
    }
}
