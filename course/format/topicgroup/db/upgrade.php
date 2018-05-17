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
 *
 * @package format_topicgroup
 * @copyright 2015 E. Castro (ULPGC)
 * @author Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_format_topicgroup_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // upgrade from old versions for moodle 2.6 or older
    // create table and copy info from course sections
    if($oldversion <  2015040100) {

        // Define table format_topicgroup_sections to be created.
        $table = new xmldb_table('format_topicgroup_sections');

        // Adding fields to table format_topicgroup_sections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table format_topicgroup_sections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        $table->add_key('sectionid', XMLDB_KEY_FOREIGN, array('sectionid'), 'course_sections', array('id'));
        $table->add_key('groupingid', XMLDB_KEY_FOREIGN, array('groupingid'), 'groupings', array('id'));

        // Adding indexes to table format_topicgroup_sections.
        $table->add_index('course-sectionid', XMLDB_INDEX_NOTUNIQUE, array('course', 'sectionid'));
        $table->add_index('course-grouping', XMLDB_INDEX_NOTUNIQUE, array('course', 'groupingid'));

        // Conditionally launch create table for format_topicgroup_sections.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

        }

        // now store in table data from old course_sections table
        $sql = "SELECT cs.id AS sid, c.id AS course, cs.id AS sectionid, cs.groupingid
                FROM {course} c
                JOIN {course_sections} cs ON cs.course = c.id
                WHERE cs.groupingid <> 0 AND c.format = 'topicgroup' ";
/*
        $sections = $DB->get_recordset_sql($sql, array());
        if($sections->valid()) {
            $now = time();
            foreach($sections as $section) {
                $section->timecreated = $now;
                $section->timemodified = $now;
                $DB->insert_record('format_topicgroup_sections', $section);
            }
        }
        $sections->close();
*/
        upgrade_plugin_savepoint(true, 2015040100, 'format', 'topicgroup');
    }

    if($oldversion <  2016071400) {
    
      $DB->delete_records('events_handlers', array('component'=>'format_topicgroup'));  
    
      upgrade_plugin_savepoint(true, 2016071400, 'format', 'topicgroup');
    }
    
    return true;
}
