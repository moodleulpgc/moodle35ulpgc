<?PHP

/**
* A file manager for Tracker
* @package mod-tracker
* @category mod
* @author Enrique Castro
*
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/repository/lib.php');

function tracker_check_enough($tracker, $method, $num) {
    global $DB;

    return false;

    $context = context_course::instance($tracker->course);
    $developers = 0;
    if($developers = get_enrolled_users($context, 'mod/tracker:develop', 0, 'u.id, u.idnumber', null, 0, 0, true)) {
        $developers = count($developers);
    }

    $issues = 0;
    if($developers = get_enrolled_users($context, 'mod/tracker:develop', 0, 'u.id, u.idnumber', null, 0, 0, true)) {
        $issues = count($issues);
    }

    $message = '';
    $unassigned = -1;
    $info = new StdClass;

    if($method == 'developer') {
        $unassigned = $issues - $num * $developers;
        if($unassigned > 0) {
            $info->type = tracker_getstring('developers', 'tracker');
        }
    } else {
        $unassigned = $developers - $num * $issues;
        if($unassigned > 0) {
            $info->type = tracker_getstring('issues', 'tracker');
        }
    }

    if($unassigned > 0) {
        $info->num = $unassigned;
        $message = tracker_getstring('randomassignfail', 'tracker', $info);
    }

    return $message;
}


function tracker_list_compare($a, $b) {
    $a = $a->current;
    $b = $b->current;

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}


function assigns_get_reviewers_list($tracker, $context, $issue, $add) {
    global $DB;

    $new = array();
    if($developers = get_enrolled_users($context, 'mod/tracker:develop', 0, 'u.id, u.idnumber', null, 0, 0, true)) {
        list($insql, $params) = $DB->get_in_or_equal(array_keys($developers), SQL_PARAMS_NAMED, 'd');
        $sql = "SELECT u.id, u.idnumber, COUNT(cc.issueid) AS current
                FROM {user} u
                LEFT JOIN {tracker_issuecc} cc ON cc.userid = u.id AND cc.trackerid = :trackerid
                LEFT JOIN {tracker_issue} i ON cc.trackerid = i.trackerid AND cc.issueid = i.id
                WHERE ( u.id $insql ) AND u.id <> :reportedby AND cc.issueid <> :issueid AND (cc.id IS NULL OR i.status <= 1)
                GROUP BY u.id ";

        $params['trackerid'] = $tracker->id;
        $params['reportedby'] = $issue->reportedby;
        $params['issueid'] = $issue->id;
        if($developers = $DB->get_records_sql($sql, $params)) {
            if(count($developers) > $add) {
                shuffle($developers);
                uasort($developers, 'tracker_list_compare');
                $new = array_slice($developers, 0, $add);
            } else {
                $new = $developers;
            }

        }
    }

    return $new;
}

function assigns_get_issues_list($tracker, $developer, $add) {
    global $DB;
    // issues not by this developer,

    $new = array();

    $sql = "SELECT i.*, COUNT(cc.userid) AS current
            FROM {tracker_issue} i
            LEFT JOIN {tracker_issuecc} cc ON cc.trackerid = i.trackerid AND cc.issueid = i.id AND cc.userid <> i.reportedby
            WHERE i.trackerid = ? AND (cc.userid IS NULL OR  cc.userid <> ?) AND i.status <= 1
            GROUP BY i.id ";

    $issues = $DB->get_records_sql($sql, array($tracker->id, $developer));
    if(count($issues) > $add) {
        shuffle($issues);
        uasort($issues, 'tracker_list_compare');
        $new = array_slice($issues, 0, $add);
    } else {
        $new = $issues;
    }

    return $new;
}

function tracker_assign_developer($tracker, $issue, $developer) {
    global $DB, $USER;

    $oldrecord = clone $issue;

    $issue->bywhomid = $USER->id;
    $issue->timeassigned = time();
    $issue->assignedto = $developer;
    if (!$DB->update_record('tracker_issue', $issue)) {
        notice ("Error updating assignation for issue $issueid");
    }
    if ($oldrecord->assignedto != $issue->assignedto) {
        $ownership = new StdClass;
        $ownership->trackerid = $tracker->id;
        $ownership->issueid = $issue->id;
        $ownership->userid = $oldrecord->assignedto;
        $ownership->bywhomid = $oldrecord->bywhomid;
        $ownership->timeassigned = ($oldrecord->timeassigned) ? $oldrecord->timeassigned : time();
        if (!$DB->insert_record('tracker_issueownership', $ownership)) {
            print_error('errorcannotlogoldownership', 'tracker');
        }
        tracker_notifyccs_changeownership($issue->id, $tracker);
    }
    tracker_register_cc($tracker, $issue, $issue->assignedto);
}


function tracker_assign_developers($tracker, $method, $num, $remove) {
    global $DB;

    $count = 0;
    if($remove) {
        $sql = "SELECT cc.id, cc.userid
                FROM {tracker_issuecc} cc
                JOIN {tracker_issue} i ON cc.issueid = i.id AND cc.trackerid = i.trackerid
                WHERE cc.trackerid = ? AND cc.userid <> i.reportedby AND i.status <= 1 ";

        if($ccs = $DB->get_records_sql_menu($sql, array($tracker->id))) {
            $chunks = array_chunk($ccs, 250, true);
            foreach($chunks as $chunk) {
                $DB->delete_records_list('tracker_issuecc', 'id', array_keys($chunk));
            }
        }

        if($method == 'developer') {
            $rs_issues = $DB->get_recordset_select('tracker_issue',
                                                    " trackerid = ? AND assignedto <> 0 AND status <= 1 ",
                                                    array($tracker->id));
            if($rs_issues->valid()) {
                foreach($rs_issues as $issue) {
                    tracker_assign_developer($tracker, $issue, 0);
                }
            }
            $rs_issues->close();
        }
    }

    $context = context_course::instance($tracker->course);

    if($method == 'issue') {
        $sql = "SELECT i.*, COUNT(cc.issueid) AS current
                FROM {tracker_issue} i
                LEFT JOIN {tracker_issuecc} cc ON cc.issueid = i.id AND cc.trackerid = i.trackerid cc.userid <> i.reportedby
                WHERE i.trackerid = ? AND i.status <= 1
                GROUP BY i.id ";

        $rs_issues = $DB->get_recordset_sql($sql, array($tracker->id));
        if($rs_issues->valid()) {
            foreach($rs_issues as $issue) {
                $add = $num - $issue->current;
                if($add) {
                    if($reviewers = assigns_get_reviewers_list($tracker, $context, $issue, $add)) {
                        foreach($reviewers as $developer) {
                            // assign a developer to the issue
                            $count +=1;
                            if($num == 1) {
                                tracker_assign_developer($tracker, $issue, $developer->id);
                            } else {
                                tracker_register_cc($tracker, $issue, $developer->id);
                            }
                        }
                    }
                }
            }
        }
        $rs_issues->close();
    }

    if($method == 'developer') {
        $developers = get_enrolled_users($context, 'mod/tracker:develop', 0, 'u.id, u.idnumber', null, 0, 0, true);
        foreach($developers as $developer) {
            $current = 0;
            if(!$remove) {
                $current = $DB->count_records_select('tracker_issue',
                                                    ' trackerid = ? AND assignedto = ? AND status <=1 ',
                                                    array($tracker->id, $developer->id));
            }
            $add = $num - $current;
            if($add) {
                if($issues = assigns_get_issues_list($tracker, $developer->id, $add)) {
                    foreach($issues as $issue) {
                        // assign an issue to the developer
                        tracker_register_cc($tracker, $issue, $developer->id);
                        $count +=1;
                    }
                }
            }
        }
    }

    return $count;
}


class tracker_assigndevelopers_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];

        $method = array();
        $options = range(0,24);
        //unset($options[0]);
        $method[0] = $mform->createElement('select', 'numassigns', ' ', $options);
        $method[0]->setSelected(1);
        $options = array('developer'=> tracker_getstring('assigndeveloper', 'tracker'),
                            'issue' => tracker_getstring('assignissue', 'tracker'),
                        );
        $method[1] = $mform->createElement('select', 'assignmethod', ' ', $options);
        $mform->addGroup($method, 'method_group', tracker_getstring('assignmethod', 'tracker'), ' ', false);
        $mform->addHelpButton('method_group', 'assignmethod', 'tracker');

        $mform->addElement('advcheckbox', 'removeexisting', tracker_getstring('removeassigns', 'tracker'));
        $mform->setDefault('removeexisting', 0);
        $mform->setType('removeexisting', PARAM_INT);

        $mform->addElement('hidden', 'id', $cm->id);
                $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'view', 'view');
                $mform->setType('view', PARAM_ALPHA);
        $mform->addElement('hidden', 'screen', 'assigns');
        $mform->setType('screen', PARAM_ALPHA);

        $this->add_action_buttons(true, tracker_getstring('savechanges'));

    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////

$id = optional_param('id', 0, PARAM_INT); // Course Module ID

if(!$cm) {
    if ($id) {
        if (! $cm = get_coursemodule_from_id('tracker', $id)) {

        }
        if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
            print_error('errormoduleincorrect', 'tracker');
        }
    } else {
        print_error('errorcoursemodid', 'tracker');
    }
}

$returnurl = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'view', 'screen'=>'browse'));
$baseurl = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'view', 'screen'=>'assigns'));

$filecontext = context::instance_by_id($context->id);
$component = 'mod_tracker';
$filearea = 'bulk_useractions';


$browser = get_file_browser();

$mform = new tracker_assigndevelopers_form(null, array('cm'=>$cm));

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if($data = $mform->get_data()) {
    // process form
    // check enough developers/issues
    if(!$message = tracker_check_enough($tracker, $data->assignmethod, $data->numassigns)) {
        $count = tracker_assign_developers($tracker, $data->assignmethod, $data->numassigns, $data->removeexisting);
        redirect($returnurl, tracker_getstring('randomassignsdone', 'tracker', $count));
    }
    // if not, show message and form again

    echo $OUTPUT->notify_message($message);
}

echo $OUTPUT->container_start();
$mform->display();
echo $OUTPUT->container_end();

echo '<br />';

