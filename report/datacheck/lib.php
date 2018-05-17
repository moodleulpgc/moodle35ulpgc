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
 * Plugin administration pages are defined here.
 *
 * @package     report_datacheck
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_datacheck_extend_navigation_course($navigation, $course, $context) {
    // nothing inserted at course level
}


/**
 * This function extends the module navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $cm
 */
function report_datacheck_extend_navigation_module($navigation, $cm) {
    if ($cm->modname == 'data') {
        if(has_capability('report/datacheck:view', context_module::instance($cm->id))) {
            $url = new moodle_url('/report/datacheck/index.php', array('id'=>$cm->id));
            $navigation->add(get_string('checkcompliance', 'report_datacheck'), $url, navigation_node::TYPE_SETTING, null, 'datacheckreport', new pix_icon('t/check', ''));
        }
        
        if(has_capability('report/datacheck:download', context_module::instance($cm->id))) {
            $url = new moodle_url('/report/datacheck/index.php', array('id'=>$cm->id, 'action'=>'download'));
            $navigation->add(get_string('downloadfiles', 'report_datacheck'), $url, navigation_node::TYPE_SETTING, null, 'downloadfiles', new pix_icon('i/import', ''));
        }
    }
}


/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array a list of page types
 */
function report_datacheck_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                => get_string('page-x', 'pagetype'),
        'report-*'         => get_string('page-report-x', 'pagetype'),
        'report-datacheck-*'     => get_string('page-report-datacheck-x',  'report_datacheck'),
        'report-datacheck-index' => get_string('page-report-datacheck-index',  'report_datacheck'),
        'report-datacheck-user'  => get_string('page-report-datacheck-user',  'report_datacheck')
    );
    return $array;
}

