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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Funciones necesarias para la personalización del interfaz de quiz
 *
 * @package report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_trackertools_extend_navigation_course($navigation, $course, $context) {
    // nothing inserted at course level
}

/**
 * This function extends the module navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $cm
 */
function report_trackertools_extend_navigation_module($navigation, $cm) {
    if ($cm->modname == 'tracker') {
        $context = context_module::instance($cm->id);
        
        // Do not add anything if not allowed to
        if(!has_any_capability(array('mod/tracker:manage', 'mod/tracker:configure'), $context)) {
            return;
        }
        
        $url = new moodle_url('/report/trackertools/index.php', array('id'=>$cm->id));
        $node = $navigation->add(get_string('pluginname', 'report_trackertools'), $url, navigation_node::TYPE_CONTAINER, null, 'trackertools');
        
        if(has_capability('report/trackertools:report', $context)) {
            $url->param('a','comply');
            $node->add(get_string('comply', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsreport', new pix_icon('i/completion-manual-enabled', ''));
            
            $url->param('a','fieldcomply');
            $node->add(get_string('fieldcomply', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsfieldcomply', new pix_icon('i/completion-manual-enabled', ''));
            
            $url->param('a','usercomply');
            $node->add(get_string('usercomply', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsusercomply', new pix_icon('i/completion-manual-enabled', ''));
        }
        
        if(has_capability('report/trackertools:warning', $context)) {
            $url->param('a', 'warning');
            $node->add(get_string('warning', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolswarning', new pix_icon('i/info', ''));
        }
        
        if(has_capability('report/trackertools:import', $context)) {
            $url->param('a', 'create');
            $node->add(get_string('create', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolssend', new pix_icon('i/cohort', ''));
            
            $url->param('a', 'import');
            $node->add(get_string('import', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsimport', new pix_icon('i/import', ''));
        }
        
        if(has_capability('report/trackertools:export', $context)) {
            $url->param('a', 'export');
            $node->add(get_string('export', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsexport', new pix_icon('i/export', ''));
        }
        
        if(has_capability('report/trackertools:download', $context)) {
            $url->param('a', 'download');
            $node->add(get_string('download', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsdownload', new pix_icon('t/download', ''));
        }
        
        if(has_capability('report/trackertools:manage', $context)) {
            $url->param('a', 'assigntasktable');
            $node->add(get_string('assigntasktable', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsassigntasktable', new pix_icon('t/assignroles', ''));
        
            $url->param('a', 'mailoptions');
            $node->add(get_string('mailoptions', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolsmailoptions', new pix_icon('t/email', ''));
        
            $url->param('a', 'setfield');
            $node->add(get_string('setfield', 'report_trackertools'), clone $url, navigation_node::TYPE_SETTING, null, 'trackertoolssetfield', new pix_icon('t/editstring', ''));
            
            $rurl = new moodle_url(me());
            if(($rurl->get_param('view') == 'admin') && ($eid = $rurl->get_param('elementid'))  && ($rurl->get_param('what') == 'viewelementoptions')) {
                $rurl = new moodle_url('/report/trackertools/loadfield.php', array('id'=>$cm->id, 'eid'=>$eid));
                $node->add(get_string('loadoptions', 'report_trackertools'), $rurl, navigation_node::TYPE_SETTING, null, 'trackertoolsloadfield', new pix_icon('i/withsubcat', ''));
            }
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
function report_trackertools_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                => get_string('page-x', 'pagetype'),
        'report-*'         => get_string('page-report-x', 'pagetype'),
        'report-trackertools-*'     => get_string('page-report-trackertools-x',  'report_trackertools'),
        'report-trackertools-index' => get_string('page-report-trackertools-index',  'report_trackertools'),
        'report-trackertools-user'  => get_string('page-report-trackertools-user',  'report_trackertools')
    );
    return $array;
}

