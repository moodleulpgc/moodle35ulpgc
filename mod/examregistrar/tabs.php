<?php

    $viewurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id));
    $manageurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id));
    $sessionurl = new moodle_url('/mod/examregistrar/session.php', array('id' => $cm->id));

    $row = array();
    if($canview) {
        $row[] = new tabobject('view', new moodle_url($viewurl, array('tab'=>'view')), get_string('view', 'examregistrar'));
    }
    if($canbook) {
        $row[] = new tabobject('booking', new moodle_url($viewurl, array('tab'=>'booking')), get_string('booking', 'examregistrar'));
    }
    if($canreview) {
        $row[] = new tabobject('review', new moodle_url($viewurl, array('tab'=>'review')), get_string('review', 'examregistrar'));
    }
    if($canprintexams) {
        $row[] = new tabobject('printexams', new moodle_url($viewurl, array('tab'=>'printexams')), get_string('printexams', 'examregistrar'));
    }
    if($canprintrooms) {
        $row[] = new tabobject('printrooms', new moodle_url($viewurl, array('tab'=>'printrooms')), get_string('printrooms', 'examregistrar'));
    }
    if($canmanage) {
        $row[] = new tabobject('session', new moodle_url($viewurl, array('tab'=>'session')), get_string('session', 'examregistrar'));
    }
    if($canmanage) {
        $row[] = new tabobject('manage', $manageurl, get_string('manage', 'examregistrar'));
    }

    if(count($row) > 1 ) {
        echo '<div class="tabdisplay">';
        echo $OUTPUT->tabtree($row, $tab);
        echo '</div>';
    }

