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

// A lot of this initial stuff is copied from mod/data/view.php

require_once('../../../../config.php');
require_once('../../lib.php');

// Optional params: row id "rid" - if set then export just one, otherwise export all

$id = required_param('id', PARAM_INT);                           // course module id
$a      = optional_param('a', 0, PARAM_INT);             // assignment id
$fieldid= optional_param('fid', 0 , PARAM_INT);          // update field id
$sid    = optional_param('sid', 0, PARAM_INT);           //submission id

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$url = new moodle_url('/mod/assign/submission/data/kml.php', array('id'=>$id, 'fid'=>$fieldid));
if ($sid !== 0) {
    $url->param('sid', $sid);
}
$PAGE->set_url($url);

if ($sid) {
    if (! $field = $DB->get_record('local_assigndata_fields', array('id'=>$fieldid))) {
        print_error('invalidfieldid', 'data');
    }
    if (! $field->type == 'latlong') { // Make sure we're looking at a latlong data type!
        print_error('invalidfieldtype', 'data');
    }
    if (! $content = $DB->get_record('local_assigndata_submission', array('assignment' => $field->assignment, 'fieldid'=>$fieldid, 'submission'=>$sid))) {
        print_error('nofieldcontent', 'data');
    }
    if (! $assignment = $DB->get_record('assign', array('course' =>$course->id, 'id' => $field->assignment))) {
        print_error('invalidid', 'data');;
    }
    
    
} else {   // We must have $d
    print_error('invalidrecord', 'data');
    $record = NULL;
}

/// If it's hidden then it's don't show anything.  :)
if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    $PAGE->set_title($assignment->name);
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
}

//header('Content-type: text/plain'); // This is handy for debug purposes to look at the KML in the browser
header('Content-type: application/vnd.google-earth.kml+xml kml');
header('Content-Disposition: attachment; filename="moodleearth-'.$id.'-'.$sid.'-'.$fieldid.'.kml"');

echo data_latlong_kml_top();
$pm = new stdClass();
if($sid) { // List one single item

    $pm->name = data_latlong_kml_get_item_name($content, $field);
    $pm->description = "&lt;a href='$CFG->wwwroot/mod/assign/view.php?id=$id&amp;sid=$sid'&gt;Item #$sid&lt;/a&gt; in Moodle Assignment activity";
    $pm->long = $content->content1;
    $pm->lat = $content->content;
    echo data_latlong_kml_placemark($pm);
} else {   // List all items in turn

    $contents = $DB->get_records('local_assigndata_submission', array('fieldid'=>$fieldid, 'assignment'=>$assignment->id ));

    echo '<Document>';

    foreach($contents as $content) {
        $pm->name = data_latlong_kml_get_item_name($content, $field);
        $pm->description = "&lt;a href='$CFG->wwwroot/mod/assign/view.php?d=$id&amp;sid=$content->submission'&gt;Item #$content->submission&lt;/a&gt; in Moodle data activity";
        $pm->long = $content->content1;
        $pm->lat = $content->content;
        echo data_latlong_kml_placemark($pm);
    }

    echo '</Document>';

}

echo data_latlong_kml_bottom();






function data_latlong_kml_top() {
    return '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">

';
}

function data_latlong_kml_placemark($pm) {
    return '<Placemark>
  <description>'.$pm->description.'</description>
  <name>'.$pm->name.'</name>
  <LookAt>
    <longitude>'.$pm->long.'</longitude>
    <latitude>'.$pm->lat.'</latitude>
    <range>30500.8880792294568</range>
    <tilt>46.72425699662645</tilt>
    <heading>0.0</heading>
  </LookAt>
  <visibility>0</visibility>
  <Point>
    <extrude>1</extrude>
    <altitudeMode>relativeToGround</altitudeMode>
    <coordinates>'.$pm->long.','.$pm->lat.',50</coordinates>
  </Point>
</Placemark>
';
}

function data_latlong_kml_bottom() {
    return '</kml>';
}

function data_latlong_kml_get_item_name($content, $field) {
    global $DB;

    // $field->param2 contains the user-specified labelling method

    $name = '';

    if($field->param2 > 0) {
        $name = htmlspecialchars($DB->get_field('local_assigndata_submission', 'content', array('fieldid'=>$field->param2, 'submission'=>$content->submission)));
    }elseif($field->param2 == -2) {
        $name = $content->content . ', ' . $content->content1;
    }
    if($name=='') { // Done this way so that "item #" is the default that catches any problems
        $name = get_string('entry', 'data') . " #$content->submission";
    }


    return $name;
}
