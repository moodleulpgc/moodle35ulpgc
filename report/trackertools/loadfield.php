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
 * Bulk tracker issue creation script from a comma separated file
 *
 * @copyright 2017 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package report_trackertools
 */


require_once('../../config.php');
require_once($CFG->dirroot.'/report/trackertools/fieldoptions_form.php');

$id = required_param('id', PARAM_INT);    // module ID

if (! $cm = get_coursemodule_from_id('tracker', $id)) {
    print_error('errorcoursemodid', 'tracker');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('errorcoursemisconfigured', 'tracker');
}
if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
    print_error('errormoduleincorrect', 'tracker');
}

$elements = array();
$eid = optional_param('eid', 0, PARAM_INT);
tracker_loadelements($tracker, $elements);
tracker_loadelementsused($tracker, $used);
$elements = $elements + $used;

$element = isset($elements[$eid]) ? $elements[$eid] : '';

$context = context_module::instance($cm->id);
$baseurl = new moodle_url('/report/trackertools/loadfield.php', array('id' => $cm->id));
$returnurl = new moodle_url('/mod/tracker/view.php', array('id' => $cm->id, 
                                                            'view'=>'admin',
                                                            'what'=>'viewelementoptions',
                                                            'elementid'=>$eid));

$strloadoptions = get_string('loadoptions', 'report_trackertools');

$PAGE->set_context($context);
$PAGE->set_title(format_string($strloadoptions.': '.$tracker->name));
$PAGE->set_heading(format_string($tracker->name));
$PAGE->set_url($baseurl);

// Security.
require_course_login($course->id, true, $cm);
require_capability('report/trackertools:manage', $context);

$PAGE->navbar->add(format_string($element->description), $returnurl);
$PAGE->navbar->add($strloadoptions);

$mform = new report_trackertools_fieldoptions_form(null, array('cmid'=>$id, 'tracker'=>$tracker, 'element'=>$element));

// to be used forward
$eventdata = array('context' => $context, 'objectid' => $tracker->id, 'other' => array('action' => 'loadfield',
                                                                                        'eid' => $eid,
                                                                                        'ename' => format_string($element->name)));

// If data has been uploaded, then process it
if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($formdata = $mform->get_data()) {
    $count = new StdClass();
    $count->updated = 0;
    $count->added = 0;

    if($formdata->loadmode == 2) { // delete first
        // This is deleet first, then add
        // Do not delete options if selected in some existing issue
        $sql = "SELECT ei.id, ei.elementid
                    FROM {tracker_elementitem} ei
                    WHERE ei.elementid = :eid 
                    AND NOT EXISTS (SELECT 1 FROM {tracker_issueattribute} ia 
                                        WHERE ia.trackerid = :trackerid AND ia.elementid = ei.elementid  AND  ia.elementitemid = ei.id )
                    "; 
        $params = array('eid'=>$eid, 'trackerid'=>$tracker->id);
        if($list = $DB->get_records_sql($sql, $params)) {
            $count->deleted = count($list);
            $chunks = array_chunk(array_keys($list), 500);
            foreach($chunks as $chunk) {
                $DB->delete_records_list('tracker_elementitem', 'id', $chunk);
            }
        }
        $formdata->loadmode = 0; //reset to mode Add 
    }

    $option = new StdClass();
    $option->name = '';
    $option->description = '';
    $options = explode("\n", $formdata->fieldoptions);
    foreach($options as $key => $value) {
        $parts = explode('|', $value);
        if(!isset($parts[1])) {
            $parts[1] = $parts[0];
            $parts[0] = ''; 
        }
        $option->name = trim($parts[0]);
        $option->description = trim($parts[1]);
        if($option->description) { // avoid blank lines
            $options[$key] = clone $option;
        } else {
            unset($options[$key]);
        }
    }
    
    if($formdata->loadmode == 1) {
        //this is update mode
        foreach($options as $key => $option) {
            if($record = $DB->get_record('tracker_elementitem', array('elementid'=>$eid, 'name'=>$option->name), 'id,name,description')) {
                $record->description = $option->description;
                if($DB->update_record('tracker_elementitem', $record)) {
                    unset($options[$key]);
                    $count->updated += 1;
                }
            } else {
                $formdata->loadmode = 0;
            }
        }
    }

    if($formdata->loadmode == 0 ) {
        //this is add mode
        $record = new stdClass();
        $record->elementid = $eid;
        $record->active = 1;
        $record->autoresponse = '';
        $record->sortorder = 0;
        $sortorder = 0;
        if($max = $DB->get_records('tracker_elementitem', array('elementid'=>$eid), 'sortorder DESC', 'id, sortorder', 0, 1)) {
            $max = reset($max);
            $sortorder = $max->sortorder + 1; 
        }
        
        foreach($options as $key => $option) {
            $record->name = $option->name ? $option->name : get_string('optionname', 'report_trackertools').($sortorder+1) ;
            $record->description = $option->description;
            $record->sortorder = $sortorder;
            if($DB->insert_record('tracker_elementitem', $record)) {
                $count->added += 1;
                $sortorder++;
            }
            
        }
    }
    
    // Trigger a report updated event.
    $eventdata['other']['count'] = $count->updated.' | '.$count->added;
    $event = \report_trackertools\event\field_loaded::create($eventdata);
    $event->trigger();
    
    $message = get_string('loadoptionssaved', 'report_trackertools', $count);
    redirect($returnurl, $message);
}


// Trigger a report viewed event.
$event = \report_trackertools\event\report_viewed::create($eventdata);
$event->trigger();


/// Print the form
echo $OUTPUT->header();

echo $OUTPUT->heading_with_help($strloadoptions, 'loadoptions', 'report_trackertools');
$mform ->display();
echo $OUTPUT->footer();


