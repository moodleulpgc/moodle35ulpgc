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
 * Internal library of functions for module registry
 *
 * All the registry specific functions, needed to implement the module
 * logic, should go here. Don't include this file from lib.php!
 *
 * @package    mod
 * @subpackage registry
  * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include_once($CFG->libdir.'/enrollib.php');
include_once($CFG->dirroot.'/mod/tracker/lib.php');

/**
 * Gets the course category tracked by a Registry instance
 *
 * @param  stdClass $registry object
 * @param  stdClass $course object
 * @return int course category id
 */
function registry_get_coursecategory($registry, $course=null) {
    global $DB;

    if(!$course) {
        $course = $DB->get_record('course', array('id'=>$registry->course));
    }

    $category = false;
    if($registry->category <= 0) {
        if($registry->category == -2) {
            $category = $course->category;
        }
        if($registry->category == -1) {
            $ulpgccat = substr($course->idnumber, 0, 4);
            $category = $DB->get_record('course_category', 'id', array('degree'=>$ulpgccat), MUST_EXIST);
        }
    } else {
        $category = $registry->category;
    }

    return $category;
}


/**
 * Gets tracked course modules in a course
 *
 * @param  stdClass $registry object
 * @param  int $courseid course id parameter for course to search
 * @return array course_modules records
 */
function registry_get_coursemods_cm($registry, $courseid) {
    global $DB;

    $conditions = array('course'=>$courseid, 'module'=>$module);

    $sectionsel = '';
    $registry->regsection = (int)$registry->regsection;
    if($registry->regsection != -1) {
        $absection = $DB->get_field('course_sections', 'id', array('course'=>$courseid, 'section'=>$registry->regsection));
        if(!$absection) {
            $absection = -1;
        }
        $conditions['section'] = $absection;
        $sectionsel = ' AND section = :section ';
    }
    $visiblesel = '';
    if($registry->visibility) {
        $visible = ($registry->visibility == 1) ? 1 : 0;
        $conditions['visible'] = $visible;
        $visiblesel = ' AND visible = :visible ';
    }
    $adminmodsel = '';
    if($registry->adminmod) {
        $op = ($registry->adminmod == 1) ? '>' : '=';
        $adminmodsel = " AND score $op 0  ";
    }

    $select = " course = :course AND module = :module $sectionsel $visiblesel $adminmodsel ";
    return $DB->get_records_select('course_modules', $conditions);
}

/**
 * Gets tracked module records in a course
 *
 * @param  stdClass $registry object
 * @param  int $courseid course id parameter for course to search
 * @return array module records
 */
function registry_get_coursemods_mod($registry, $courseid) {
    global $DB;

    $modtable = $registry->regmodule;
    $moduleid = $DB->get_field('modules', 'id', array('name'=>$registry->regmodule), MUST_EXIST);

    $conditions = array('course'=>$courseid, 'module'=>$moduleid);
    $sectionwhere = '';
    $registry->regsection = (int)$registry->regsection;
    if($registry->regsection != -1) {
        $absection = $DB->get_field('course_sections', 'id', array('course'=>$courseid, 'section'=>$registry->regsection));
        if(!$absection) {
            $absection = -1;
        }
        $conditions['section'] = $absection;
        $sectionwhere = ' AND cm.section = :section ';
    }
    $visiblewhere = '';
    if($registry->visibility) {
        $visible = ($registry->visibility == 1) ? 1 : 0;
        $conditions['visible'] = $visible;
        $visiblewhere = ' AND visible = :visible ';
    }
    $adminmodwhere = '';
    if($registry->adminmod) {
        $op = ($registry->adminmod == 1) ? '>' : '=';
        $adminmodwhere = " AND score $op 0  ";
    }

    $sql = "SELECT m.*, cm.id AS cmid, cm.visible AS cmvisible
                FROM {course_modules} cm
                JOIN {".$modtable."} m ON m.id = cm.instance AND m.course = cm.course
            WHERE cm.course = :course AND cm.module = :module $sectionwhere $visiblewhere $adminmodwhere ";

    return $DB->get_records_sql($sql, $conditions);
}

/**
 * Checks if a course contains tracked modules
 *
 * @param  stdClass $registry object
 * @param  int $courseid course id parameter for course to search
 * @return bool
 */
function registry_course_hasmodules($registry, $courseid) {
    global $DB;

    $modtable = $registry->regmodule;
    $moduleid = $DB->get_field('modules', 'id', array('name'=>$registry->regmodule), MUST_EXIST);

    $conditions = array('course'=>$courseid, 'module'=>$moduleid);
    $sectionsel = '';
    $registry->regsection = (int)$registry->regsection;
    if($registry->regsection != -1) {
        $absection = $DB->get_field('course_sections', 'id', array('course'=>$courseid, 'section'=>$registry->regsection));
        if(!$absection) {
            $absection = -1;
        }
        $conditions['section'] = $absection;
        $sectionsel = ' AND section = :section ';
    }
    $visiblesel = '';
    if($registry->visibility) {
        $visible = ($registry->visibility == 1) ? 1 : 0;
        $conditions['visible'] = $visible;
        $visiblesel = ' AND visible = :visible ';
    }
    $adminmodsel = '';
    if($registry->adminmod) {
        $op = ($registry->adminmod == 1) ? '>' : '=';
        $adminmodsel = " AND score $op 0  ";
    }

    $select = " course = :course AND module = :module $sectionsel $visiblesel $adminmodsel ";
    return $DB->record_exists_select('course_modules', $select, $conditions);
}

/**
 * Gets user tracked courses in the category
 *
 * @param  stdClass $course object for parent (where this Registry instace lives)
 * @param  stdClass $registry object
 * @param  int $userid
 * @param  int $term course term
 * @param  bool $withmods if true only courses with tracked modules are returned
 * @return array of reduced course objects (id, fullname, shortname, idnumber)
 */
function registry_get_user_courses($course, $registry, $userid, $term=-1, $withmods=false) {
    global $CFG, $DB;

    $trackedcat = registry_get_coursecategory($registry, $course);
    $regconfig = get_config('registry');

    $params = array('siteid'=>SITEID);
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['ctxlevel'] =  CONTEXT_COURSE;

    $checkedroles = explode(',', $regconfig->checkedroles);
    list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role');

    $termwhere = '';
    if($term != -1) {
        $termwhere = ' AND uc.term = :term ';
        $params['term'] =  $term;
    }

    $excluded = '';
    if($regconfig->excludecourses) {
        $excluded = " AND uc.credits > 0 AND c.idnumber <> '' ";
    }

    $sql = "SELECT c.id, c.shortname, c.fullname, c.idnumber, uc.term, uc.credits
                FROM {course} c
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                JOIN (SELECT DISTINCT e.courseid
                        FROM {enrol} e
                        JOIN {user_enrolments} ue ON ( ue.enrolid = e.id AND ue.userid = :userid )
                        WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                    ) en ON (en.courseid = c.id)
            WHERE c.category = :coursecat AND c.id <> :parent $excluded $termwhere
                    AND EXISTS (SELECT ctx.id
                                    FROM {context} as ctx
                                    JOIN {role_assignments} ra ON ( ra.contextid = ctx.id AND ra.userid = :userid2 )
                                    WHERE ctx.instanceid = c.id  AND ctx.contextlevel = :ctxlevel AND ra.roleid $inrolesql
                                )
                    ";

    $params['userid']  = $userid;
    $params['userid2']  = $userid;
    $params['coursecat']  = $trackedcat;
    $params['parent']  = $course->id;
    $params = array_merge($params, $inparams);

//    print_object($sql);
  //  print_object($params);

    $courses = $DB->get_records_sql($sql, $params);
    if($withmods) {
        $courseids = array_keys($courses);
        foreach($courseids as $cid) {
            if(!registry_course_hasmodules($registry, $cid)) {
                unset($courses[$cid]);
            }
        }
    }

    return $courses;
}


/**
 * Gets all tracked courses in the category
 *
 * @param  stdClass $course object for parent (where this Registry instace lives)
 * @param  stdClass $registry object
 * @param  int $term course term
 * @param  bool $withmods if true only courses with tracked modules are returned
 * @return array of reduced course objects (id, fullname, shortname, idnumber)
 */
function registry_get_allcatcourses($course, $registry, $term=-1, $withmods=false) {
    global $CFG, $DB;

    $trackedcat = registry_get_coursecategory($registry, $course);
    $regconfig = get_config('registry');

    $params = array('siteid'=>SITEID);

    $excluded = '';
    if($regconfig->excludecourses) {
        $excluded = " AND uc.credits > 0 AND c.idnumber <> '' ";
    }

    $termwhere = '';
    if($term != -1) {
        $termwhere = ' AND uc.term = :term ';
        $params['term'] =  $term;
    }

    $sql = "SELECT c.id, c.shortname, c.fullname, c.idnumber, uc.term, uc.credits 
                FROM {course} c
                LEFT JOIN {local_ulpgccore_course} uc ON uc.courseid = c.id
            WHERE c.category = :coursecat AND c.id <> :parent $excluded $termwhere ";
    $params['coursecat']  = $trackedcat;
    $params['parent']  = $course->id;

    $courses = $DB->get_records_sql($sql, $params);
    if($withmods) {
        $courseids = array_keys($courses);
        foreach($courseids as $cid) {
            if(!registry_course_hasmodules($registry, $cid)) {
                unset($courses[$cid]);
            }
        }
    }

    return $courses;
}


/**
 * Gets course submission record
 *
 * @param  stdClass $registry  Registry instance object
 * @param  int $regcourse course ID
 * @param  bool $create if true a new registry_submision record is inserted in DB
 * @return object registry_submissions
 */
function registry_get_course_submission($registry, $regcourse, $create=false) {
    global $DB, $USER;

    if(!$regsub = $DB->get_record('registry_submissions', array('registryid'=>$registry->id, 'regcourse'=>$regcourse))) {
        if($create) {
            $regsub = new stdClass;
            $regsub->registryid = $registry->id;
            $regsub->regcourse = $regcourse;
            $regsub->userid = $USER->id;
            $regsub->grade = -1;
            $regsub->grader = 0;
            $regsub->timegraded = 0;
            $regsub->issueid = registry_create_tracker_issue($registry, $regcourse, $USER->id);
            $regsub->timecreated = time();
            $regsub->timemodified = $regsub->timecreated;
            $rid = $DB->insert_record('registry_submissions',$regsub);
            $regsub->id = $rid;
        }
    }
    return $regsub;
}

/**
 * Gets course submission record
 *
 * @param  stdClass $registry  Registry instance object
 * @param  int $courseid course ID
 * @return object registry_submissions
 */
function registry_get_course_contenthash($registry, $courseid) {
    global $DB;

    return '';
}

/**
 * Create a new tracker issue for a given Registration sub record
 *
 * @param  stdClass $registry object
 * @param  int $courseid
 * @param  int $userid user ID
 * @return int tracker issue ID
 */
function registry_create_tracker_issue($registry, $courseid, $userid =0) {
    global $CFG, $DB, $USER;

    $sql = "SELECT t.*, cm.id as cmid, cm.idnumber as cmidnumber
                FROM {tracker} t
                JOIN {course_modules} cm ON (t.id = cm.instance AND cm.course = :course1 )
                WHERE t.course = :course2 AND cm.idnumber = :idnumber ";
    $params =  array('course1'=>$registry->course, 'course2'=>$registry->course, 'idnumber'=>$registry->tracker);
    $tracker = $DB->get_record_sql($sql, $params, MUST_EXIST);


    if(!$userid) {
        $userid = $USER->id;
    }

    $course = $DB->get_record('course', array('id'=>$courseid), 'id, shortname, fullname', MUST_EXIST);
    $summary = $course->shortname.' - '.format_string($course->fullname).' : '.$registry->issuename;

    $issue = new StdClass;
    $issue->datereported = time();
    $issue->summary = $summary;
    $issue->description = ''; //registry_update_tracked_mods($registry, $courseid);
    $issue->descriptionformat = 1;
    $issue->assignedto = $tracker->defaultassignee;
    $issue->bywhomid = 0;
    $issue->trackerid = $tracker->id;
    $issue->status = POSTED;
    $issue->reportedby = $userid;
    $issue->usermodified = $issue->datereported;
    $issue->resolvermodified = $issue->datereported;

    $issueid = $DB->insert_record('tracker_issue', $issue);
    $issue->id = $issueid;

    return $issueid;
}

/**
 * update an existing tracker issue of a registration submission record
 *
 * @param  stdClass $registry object
 * @param  stdClass $regsubmission a record from registry_submissions table
 * @param  int $status new tracker issue status
 */
function registry_update_tracker_issue($registry, $regsubmission, $status) {
    global $DB, $USER;

    $issue = $DB->get_record('tracker_issue', array('id'=>$regsubmission->issueid), '*', MUST_EXIST);
    $issue->reportedby = $regsubmission->userid;
    $issue->usermodified = $regsubmission->timemodified;
    /// TODO resolvermodified???
    $issue->description = registry_update_tracked_mods($registry, $regsubmission->regcourse);
    $issue->status = $status;
    $DB->update_record('tracker_issue', $issue);

    $regconfig = get_config('registry');
    $roles = explode(',', $regconfig->checkedroles);
    $coursecontext = context_course::instance($regsubmission->regcourse);

    // add other teachers as cc in tracker issue
    $fields = get_all_user_name_fields(true, 'u');
    if($users = get_role_users($roles, $coursecontext, false, 'ra.id AS raid, u.id AS userid, u.idnumber, u.firstname, u.lastname, '.$fields, 'u.lastname, u.firstname', false, '', '', '', ' u.id <> '.$regsubmission->userid)) {
        $tracker = $DB->get_record('tracker', array('id'=>$issue->trackerid, 'course'=>$registry->course));
        $done = array();
        foreach($users as $user) {
            if(!isset($done[$user->userid])) {
                tracker_register_cc($tracker, $issue, $user->userid);
                $done[$user->userid] = $user->userid;
            }
        }
    }
}

/**
 * Gets updated summary links of tracked modules for a course
 *
 * @param  stdClass $registry object
 * @param  stdClass $regsubmission a record from registry_submissions table
 * @param  int $status new tracker issue status
 */
function registry_update_tracked_mods($registry, $courseid) {
    global $CFG, $DB, $OUTPUT;

    $cm = get_coursemodule_from_instance('registry', $registry->id, $registry->course, false, MUST_EXIST);
    $basecontext = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$courseid), 'id, shortname, fullname', MUST_EXIST);
    $regsub = $DB->get_record('registry_submissions', array('registryid'=>$registry->id, 'regcourse'=>$courseid), '*', MUST_EXIST);

    $summary = html_writer::tag('h3', $course->shortname.' <br /> '.format_string($course->fullname));
    $summary .= html_writer::tag('p', $registry->issuename);
    $summary .= '<br />';
    $summary = html_writer::tag('div', $summary, array('class'=>'itemname'));

    $url = new moodle_url('/mod/registry/downloadpdf.php', array('reg'=>$registry->id, 'c'=>$courseid, 'i'=>$regsub->issueid));
    $download = html_writer::link($url, get_string('downloadpdf', 'registry'), array('class'=>'contentpdflink')) ;
    $sidebar = html_writer::tag('div', $download, array('class'=>'downloadsidebar'));

    $header = html_writer::tag('div', $summary.$sidebar, array('class'=>'issueheader'));

    $mods = registry_get_coursemods_mod($registry, $courseid);
    $modtable = $registry->regmodule;

    $items = array();
    foreach($mods as $key=>$mod) {
        $url = new moodle_url("/mod/$modtable/view.php", array('id'=>$mod->cmid) );
        $item = html_writer::tag('h4', html_writer::link($url, format_string($mod->name)));
        $attachments = array();
        if($files = registry_get_module_files($mod, $modtable)) {
            foreach($files as $file) {
                $itemid = "{$file->contextid}/{$file->component}/{$file->filearea}/{$file->itemid}/{$file->filepath}{$file->filename}";
                $furl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'.$basecontext->id.'/mod_registry/othermodcontent'.'/'.$itemid);
                $image = $OUTPUT->pix_icon(file_mimetype_icon($file->mimetype), $file->filename, 'moodle', array('class'=>'icon'));
                $attachments[] = html_writer::link($furl, $image.format_string($file->filename));
            }
        }
        $attachment = '';
        if($attachments) {
            $attachment = '&nbsp;&nbsp;'.get_string('attachments', 'registry').': '.implode(', ',  $attachments);
        }
        $items[$key] = $item.$attachment;
    }
    $names = html_writer::tag('div', implode("\n", $items), array('class'=>'itemnames'));

    $content = html_writer::tag('div', $names, array('class'=>'itemcontent'));

    return $header.$content;
}


/**
 * Gets updated summary links of tracked modules for a course
 *
 * @param  stdClass $mod module record object with cmid
 * @return  array of file records
 */
function registry_get_module_files($mod, $modtable) {
    global $DB;

    $context = context_module::instance($mod->cmid);

    $imagelike = $DB->sql_like('mimetype', ':image', true, true, true);
    $select = " contextid = :context AND component = :mod AND filearea <> 'draft' AND filearea <> 'attachment' AND filename <> '.' AND $imagelike ";
    $files = $DB->get_records_select('files', $select, array('context'=>$context->id, 'mod'=>'mod_'.$modtable, 'image'=>'image/%'),
                                     'filearea DESC, filename ASC', 'id, contextid, component, filearea, itemid, filepath, filename, mimetype ');
    return $files;
}



////////////////////////////////////////////////////////////////////////////////
//                 Presentation & Display functions
////////////////////////////////////////////////////////////////////////////////

/**
 * Prints a table with tracked courses for this user
 * Includes a form to submit registration of tracked modules in those courses
 *
 * @param  stdClass $cm object
 * @param  stdClass $course object
 * @param  stdClass $registry object
 * @param  int $userid
 * @param  int $review true if reviewing page, with all category course, without form
 */
function registry_view_user_registerings($cm, $course, $registry, $userid, $review=0) {
    global $CFG, $DB, $USER, $OUTPUT;

    //$userid = 3;

    $STATUSKEYS = array(POSTED => 'status_posted',
                        OPEN => 'status_open',
                        RESOLVING => 'status_resolving',
                        WAITING => 'status_waiting',
                        TESTING => 'status_testing',
                        RESOLVED => 'status_resolved',
                        ABANDONNED => 'status_abandonned',
                        TRANSFERED => 'status_transfered',
                        PUBLISHED => 'status_published');

    $term = optional_param('term', -1, PARAM_INT);

    if(!$review) {
        $courses = registry_get_user_courses($course, $registry, $userid, $term);
    } else {
        $courses = registry_get_allcatcourses($course, $registry, $term);
    }

    $linkurl = new moodle_url('/mod/registry/view.php', array('id'=>$cm->id));
    $context = context_module::instance($cm->id);
    $canreview = has_capability('mod/registry:review', $context);
    $cansubmit = has_capability('mod/registry:submit', $context);
    $cansubmitany = has_capability('mod/registry:submitany', $context);

    /// print term menuform
//    echo html_writer::start_tag('div', array('class'=>'termmenu sourcegroup '));
    $options = array(-1 => get_string('any'),
                     0 => get_string('term0', 'local_ulpgccore'),
                     1 => get_string('term1', 'local_ulpgccore'),
                     2 => get_string('term2', 'local_ulpgccore'),
                     3 => get_string('term3', 'local_ulpgccore')
                     );
    $url = new moodle_url('/mod/registry/view.php', array('id'=>$cm->id, 'review'=>$review));
    $select = new single_select($url, 'term', $options, $term, array());
    $select->label = get_string('term', 'registry');
    $select->formid = 'selectterm';
    echo html_writer::tag('div', $OUTPUT->render($select), array('class'=>'termmenu sourcegroup '));


    if(!$courses) {
        echo html_writer::tag('div', get_string('nodata', 'registry'), array('class'=>'subtablelink'));
        if($canreview) {
            echo '<br />';
            $linkurl->param('review', 1);
            $link = html_writer::link($linkurl, get_string('reviewlink', 'registry'));
            echo html_writer::tag('div', $link, array('class'=>'subtablelink'));
        }
        return;
    }

    $page    = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 100, PARAM_INT);
    $perpage = ($perpage <= 0) ? 100 : $perpage ;

    $tablecolumns = array('select', 'shortname', 'coursename', 'items', 'timemodified', 'status', 'timegraded');
    $tableheaders = array(get_string('select'), get_string('shortname', 'registry'), get_string('fullname', 'registry'),
                            get_string('items', 'registry'),
                            get_string('lastsubmitted', 'registry'),
                            get_string('status', 'registry'),
                            get_string('lastgraded', 'registry'));

    require_once($CFG->libdir.'/tablelib.php');
    $table = new flexible_table('mod-registry-submissions');
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $url = new moodle_url('/mod/registry/view.php', array('id'=>$cm->id, 'review'=>$review, 'term'=>$term));
    $table->define_baseurl($url);

    $table->sortable(true, 'fullname');//sorted by fullname by default
    $table->collapsible(false);

    $table->no_sorting('select');
    $table->no_sorting('items');
    $table->no_sorting('status');
    $table->column_class('items', 'items');
    $table->column_class('timemodified', 'timemodified');
    $table->column_class('timemarked', 'timemarked');
    $table->column_class('status', 'status');
    $table->column_class('select', 'select');

    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'course_registry_submissions');
    $table->set_attribute('class', 'submissions');
    $table->set_attribute('width', '100%');


    /// Print submitting form around the table
    if(!$review || $cansubmitany) {
        $formattrs = array();
        $formattrs['action'] = new moodle_url('/mod/registry/submissions.php');
        $formattrs['id'] = 'registry_submissions_form';
        $formattrs['method'] = 'post';

        echo html_writer::start_tag('form', $formattrs);
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id',      'value'=> $cm->id));
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'page',    'value'=> $page));
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=> sesskey()));
    }

    // Start working -- this is necessary as soon as the niceties are over
    $table->setup();

    /// Construct the SQL
    list($where, $params) = $table->get_sql_where();
    if ($where) {
        $where .= ' AND ';
    }
    if ($sort = $table->get_sql_sort()) {
        $sort = ' ORDER BY '.$sort;
    }

    $courseids = array_keys($courses);
    list($incourses, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'course');

    $sql = "SELECT c.id, c.shortname, c.fullname as coursename, c.idnumber, rs.userid, rs.itemhash, rs.grade, rs.issueid, rs.timegraded, rs.timecreated, rs.timemodified
                FROM {course} c
                LEFT JOIN {registry_submissions} rs ON rs.regcourse = c.id AND rs.registryid = :rid
            WHERE $where c.id $incourses   ";
    $params['rid'] = $registry->id;
    $params['rid2'] = $registry->id;
    $params = array_merge($params, $inparams);

    $registers = $DB->get_records_sql($sql.$sort, $params, $table->get_page_start(), $table->get_page_size());

    $table->pagesize($perpage, count($registers));
    ///offset used to calculate index of course in that particular query
    $offset = $page * $perpage;


    $moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'), MUST_EXIST);
    $tcm = $DB->get_record('course_modules', array('course'=>$registry->course, 'module'=>$moduleid, 'idnumber'=>$registry->tracker), '*', MUST_EXIST);
    $trackerurl = new moodle_url('/mod/tracker/view.php', array('id'=>$tcm->id, 'view'=>'view', 'screen'=>'viewanissue'));
    $userurl = new moodle_url('/user/view.php', array('course'=>$registry->course));

    $fields = get_all_user_name_fields(true);
    foreach($registers as $cid=>$register) {
        /// build the table data
        $hasmods = false;
        if(!$review || $cansubmitany) {
            $hasmods = registry_course_hasmodules($registry, $cid);
        }
        $rowclass = null;
        $row = array();
        if($hasmods) {
            $row[] = html_writer::checkbox('coursesend_'.$cid.'', $cid, false);
        } else {
            $row[] = '';
        }
        $url = new moodle_url('/course/view.php', array('id'=>$cid));
        $row[] = html_writer::link($url, $register->shortname);
        $row[] = html_writer::link($url, format_string($register->coursename));

        $items = registry_print_coursemods($registry, $cid);
        $row[] = $items;

        $date = '-';
        $userstr = '';
        if($register->timemodified) {
            $date = userdate($register->timemodified);
            if($register->userid != $USER->id) {
                $user = $DB->get_record('user', array('id'=>$register->userid), 'id, username, idnumber, '.$fields);
                $userurl->param('id', $register->userid);
                $userstr = '<br />'.html_writer::link($userurl, fullname($user));
            }
        }
        $row[] = $date.$userstr;

        if($register->issueid) {
            $status = $DB->get_field('tracker_issue', 'status', array('id'=>$register->issueid));
            $statusmsg = html_writer::tag('span', '&nbsp;'.get_string($STATUSKEYS[$status], 'registry').'&nbsp;', array('class'=>$STATUSKEYS[$status]));
            $trackerurl->param('issueid', $register->issueid);
            $statuslink = html_writer::link($trackerurl, get_string('statuslink', 'registry'));
            $row[] = $statusmsg.'<br />'.$statuslink; //html_writer::tag('span', $statusmsg, array('class'=>$STATUSKEYS[$status]));
        } else {
            $row[] = '';
        }

        if($register->issueid) {
            $date = $DB->get_field('tracker_issue', 'usermodified', array('id'=>$register->issueid));
            $date = userdate($date);
        } else {
            $date = $register->timegraded ? userdate($register->timegraded) : '-';
        }
        $row[] = $date;

        $table->add_data($row, $rowclass);
    }


    /// Print the table
    $table->print_html();  /// Print the whole table

    /// Print submitting form around the table (end portion)
    if(!$review || $cansubmitany) {
        if($cansubmit || $cansubmitany) {
            $save = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'saveregs', 'value'=>get_string('saveregistrations', 'registry')));
            echo html_writer::tag('div', $save, array('class'=>'subtablelink'));
        }
        echo html_writer::end_tag('form');

        $link = '';
        if($canreview) {
            $linkurl->param('review', 1);
            $link = html_writer::link($linkurl, get_string('reviewlink', 'registry'));
        }
    } else {
            $link = html_writer::link($linkurl, get_string('userlink', 'registry'));
    }
    echo '<br />';
    echo html_writer::tag('div', $link, array('class'=>'subtablelink'));

}


/**
 * Prints a list of tracked items in a course
 *
 * @param  stdClass $registry object
 * @param  int $course object
 * @return string html fragment with links to activity names
 */
function registry_print_coursemods($registry, $courseid) {
    global $DB;


    $modtable = $registry->regmodule;
    $modules = registry_get_coursemods_mod($registry, $courseid);

    $items = array();
    foreach($modules as $key=>$module){
        $url = new moodle_url("/mod/$modtable/view.php", array('id'=>$module->cmid) );
        $items[$key] = html_writer::link($url, format_string($module->name));
    }
    return implode('<br />', $items);

}

/**
 * Prints a list of tracked items in a course
 *
 * @param  stdClass $registry object
 * @param  stdClass $module object
 * @return string html fragment with the internal content of teh module (excluding intro/description)
 */
function registry_print_modcontent($registry, $mod, $context) {
    global $DB;

    $modtable = $registry->regmodule;
    $content = '';

    switch($modtable) {
        case 'tab' :
                    $content = registry_print_tabcontents($mod, $context);
                    break;
    }
    return $content;
}


function registry_print_tabcontents($mod, $context) {
    global $DB;

    $tabs = $DB->get_records('tab_content', array('tabid'=>$mod->id), 'tabcontentorder ASC');

    $content = array();
    foreach($tabs as $tab) {
        $content[] = html_writer::tag('h3', format_string($tab->tabname));
        $tabcontent = file_rewrite_pluginfile_urls($tab->tabcontent, 'pluginfile.php', $context->id, 'mod_tab', 'content', $tab->id);
        $content[] = html_writer::tag('div', format_text($tabcontent, $tab->contentformat));
    }



    return implode('', $content);
}
