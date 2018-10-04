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
 * Funciones necesarias para trackertools
 *
 * @package report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once($CFG->dirroot.'/mod/tracker/locallib.php');

define('REPORT_TRACKERTOOLS_FILES_ALL',      0);
define('REPORT_TRACKERTOOLS_FILES_USER',    -1);
define('REPORT_TRACKERTOOLS_FILES_DEV',     -2);
define('REPORT_TRACKERTOOLS_FILES_BOTH',    -3);

define('REPORT_TRACKERTOOLS_GROUP_NO', 0);
define('REPORT_TRACKERTOOLS_GROUP_ISSUE',   -1);
define('REPORT_TRACKERTOOLS_GROUP_USER',    -2);
define('REPORT_TRACKERTOOLS_GROUP_DEV',     -3);

define('REPORT_TRACKERTOOLS_ISSUES_ALL',     0);
define('REPORT_TRACKERTOOLS_ISSUES_SEARCH',  1);
define('REPORT_TRACKERTOOLS_ISSUES_OPEN',    2);
define('REPORT_TRACKERTOOLS_ISSUES_CLOSED',  3);

define('REPORT_TRACKERTOOLS_MENUTYPE_OTHER',  0);
define('REPORT_TRACKERTOOLS_MENUTYPE_USER',  1);
define('REPORT_TRACKERTOOLS_MENUTYPE_COURSE',  2);

define('REPORT_TRACKERTOOLS_ANY',       0);
define('REPORT_TRACKERTOOLS_NOEMPTY',   1);
define('REPORT_TRACKERTOOLS_EMPTY',     2);
define('REPORT_TRACKERTOOLS_LAST',      3);


/**
 * Get sql WHERE snippnet to limit issues to operate on
 *
 * @param int $trackerid 
 * @param object $fromform data from user export form
 * @return array ($sql, $params) for use outside
 */
function report_trackertools_issue_where_sql($trackerid, $fromform, $prefix = 'i.') {
    global $DB; 
    
    $issuewhere = '';
    $params = array('trackerid'=>$trackerid);
    $issuewhere = " {$prefix}trackerid = :trackerid ";
    if($fromform->issuesearch == REPORT_TRACKERTOOLS_ISSUES_SEARCH) { // 1 means stored search
        $issueids = array();
        $fields = tracker_extractsearchcookies();
        
        if (!empty($fields)) {
            $searchqueries = tracker_constructsearchqueries($trackerid, $fields);
        }
        if (isset($searchqueries)) {
            $sql = $searchqueries->search;
            $issueids = array_keys($DB->get_records_sql($sql, null));
            
        }
        if($issueids) {
            list($insql, $inparams) = $DB->get_in_or_equal($issueids, SQL_PARAMS_NAMED, 'iid');
            $issuewhere .= " AND {$prefix}id $insql ";
            $params = array_merge($params, $inparams);
        }
        
    } elseif($fromform->issuesearch == REPORT_TRACKERTOOLS_ISSUES_OPEN) { // 2 means all open
        $issuewhere .= " AND ({$prefix}status <> ".RESOLVED." AND {$prefix}status <> ".ABANDONNED." ) ";
    } elseif($fromform->issuesearch == REPORT_TRACKERTOOLS_ISSUES_CLOSED) { // 2 means all closed
        $issuewhere .= " AND ({$prefix}status = ".RESOLVED." OR {$prefix}status = ".ABANDONNED." ) ";
    }

    return array($issuewhere, $params);
}

/**
 * Get sql statement & params to find all relevant course users & data for exportation
 *
 * @uses $DB
 * @param object $tracker object fron DB
 * @param object $fromform data from user export form
 * @return array ($sql, $params, $columns) for use out
 */
function report_trackertools_exportuser_getsql($tracker, $fromform) {
    global $DB;
    
    $columns = array('reportedby'=>tracker_getstring('reportedby', 'tracker'));
    if($fromform->useridnumber) {
        $columns['reportedbyidnumber'] =  get_string('reportedbyidnumber', 'report_trackertools');
    }
    
    $columns += array('assignedto'=>tracker_getstring('assignedto', 'tracker'));
    if($fromform->useridnumber) {
        $columns['assignedtoidnumber'] =  get_string('assignedtoidnumber', 'report_trackertools');
    }

    $columns += array('summary'=>tracker_getstring('summary', 'tracker'),
                        'status'=>tracker_getstring('status', 'tracker'),
                    );
                    
    $optional = array('datereported' => tracker_getstring('datereported', 'tracker'),
                        'description' => tracker_getstring('description', 'tracker'), 
                        'resolution' => tracker_getstring('resolution', 'tracker'),
                        'usermodified' => tracker_getstring('dateupdated', 'tracker'),
                        'resolvermodified' => tracker_getstring('staffupdated', 'tracker'), 
                        'userlastseen' => tracker_getstring('userlastseen', 'tracker'), 
    );
                    
    foreach($optional as $key => $field) {
        if(isset($fromform->{$key}) &&  $fromform->{$key}) {
            $columns[$key] = $optional[$key];
        }
    }
    
    $elements = array();
    tracker_loadelementsused($tracker, $elements);
    foreach($elements as $key => $element) {
        $ekey = 'element'.$element->name;
        if(isset($fromform->{$ekey}) &&  $fromform->{$ekey}) {
            $columns[$element->name] = format_string($element->description);
            $elements[$element->name] = $element;
            unset($elements[$key]); // change to index by name to be used in report_trackertools_exportuser_row
        }
    }
    
    foreach(array('comment', 'file') as $extra) {
        foreach(array('user', 'dev') as $field) {
            $key =  $extra.$field;
            if(isset($fromform->{$key}) &&  $fromform->{$key}) {
                $columns[$key] = get_string($key, 'report_trackertools');
            }
        }
    }
    
    $studentuserfields = get_all_user_name_fields(true, 'su', '', 'su');
    $tutoruserfields = get_all_user_name_fields(true, 'tu', '', 'tu');
    if($fromform->useridnumber) {
        $studentuserfields .= ', su.idnumber AS reportedbyidnumber';
        $tutoruserfields .= ', tu.idnumber AS assignedtoidnumber';
    }
    
    
    $params = array('trackerid' => $tracker->id); 
    
    list($issuewhere, $inparams) = report_trackertools_issue_where_sql($tracker->id, $fromform);
    $params = array_merge($params, $inparams);
        
    $sortjoin = '';
    $sortorder = '';
    if($fromform->exportsort) {
        $usersort = " su.lastname ASC, su.firstname ASC, 
                        tu.lastname ASC, tu.firstname ASC, ";   
    
        switch($fromform->exportsort) {
            case 'assignedto' : 
                    $sortorder = " tu.lastname ASC, tu.firstname ASC, 
                                   su.lastname ASC, su.firstname ASC, ";
                    break;

            case 'reportedby' :
                    $sortorder = $usersort;
                    break;
            case 'summary' :
            case 'description' :
            case 'resoluton' :
            case 'datereported' :
            case 'usermodified' :
            case 'userlastseen ' :
                    $sortorder = " i.{$fromform->exportsort} ASC, "; 
                    $sortorder .= $usersort;
                    break;
            default :
                if(array_key_exists($fromform->exportsort, $elements)) {
                    $params['eid'] = $elements[$fromform->exportsort]->id;
                    $sortjoin = " LEFT JOIN {tracker_issueattribute} ia 
                                                ON ia.trackerid = i.trackerid AND i.id = ia.issueid AND ia.elementid = :eid
                                    ";
                    $sortorder = " ia.elementitemid  ASC, "; 
                    $sortorder .= $usersort;
                }
        }
    }
           
    $sql = "SELECT i.*, $studentuserfields , $tutoruserfields 
                FROM {tracker_issue} i 
                JOIN {user} su ON su.id = i.reportedby
                LEFT JOIN {user} tu ON tu.id = i.assignedto
                $sortjoin
            WHERE $issuewhere 
            ORDER BY $sortorder i.usermodified ASC
            ";

    return array($sql, $params, $columns, $elements);
}


/**
 * Get sql statement & params to find all relevant course users & data for exportation
 *
 * @uses $DB
 * @param object $issue record issue, 
 * @param string $prefix flag determining user of staff comments
 * @return string text with collated comments
 */
function report_trackertools_issue_comments($issue, $prefix = 'user') {
    global $DB;

    $text = '';
    
    $params = array('trackerid'=>$issue->trackerid, 'issueid'=>$issue->id);
    $select = ' trackerid = :trackerid AND  issueid = :issueid  AND '; 
    if($prefix == 'user') {
        $select .= ' userid = :userid ';
        $params['userid'] = $issue->reportedby; 
    } else {
        $select .= ' ((userid != :reportedby) OR (userid = :assignedto)) ';
        $params['reportedby'] = $issue->reportedby;
        $params['assignedto'] = $issue->assignedto; 
    }

    $comments = $DB->get_records_select('tracker_issuecomment', $select, $params, 'datecreated'); 

    
    if($comments = $DB->get_records_select('tracker_issuecomment', $select, $params, 'datecreated')) {
        foreach($comments as $key => $comment) {
            $comments[$key] = get_string('contentadded', 'report_trackertools', userdate($comment->datecreated))."\n".
                                format_text($comment->comment, $comment->commentformat);
        }
        $text = implode("\n", $comments);
    }
    return $text;
}


/**
 * Get sql statement & params to find all relevant course users & data for exportation
 *
 * @uses $DB
 * @param object $issue record issue, 
 * @param string $prefix flag determining user of staff comments
 * @param boolen $text flag determining return value
 * @return mixed, string text with collated filenames or array of filenames
 */
function report_trackertools_issue_files($issue, $prefix = 'user', $text = true) {
    global $DB;

    $result = '';
    
    $params = array('trackerid'=>$issue->trackerid, 'issueid'=>$issue->id);
    $select = ' trackerid = :trackerid AND  issueid = :issueid  AND '; 
    if($prefix == 'user') {
        $select .= ' ic.userid = :userid ';
        $params['userid'] = $issue->reportedby; 
    } else {
        $select .= ' ((ic.userid != :reportedby) OR (ic.userid = :assignedto)) ';
        $params['reportedby'] = $issue->reportedby;
        $params['assignedto'] = $issue->assignedto; 
    }

    $sql = "SELECT ic.id, ic.datecreated, f.filename
            FROM {tracker_issuecomment} ic
            JOIN {files} f ON f.itemid = ic.id AND 
                        f.component = 'mod_tracker' AND f.filearea = 'issuecomment' AND f.filename != '.' AND f.filesize != 0  	
            WHERE $select 
            ORDER BY ic.datecreated ASC
    ";
    
    if($comments = $DB->get_records_sql($sql, $params)) {
        if($text) {
            foreach($comments as $key => $comment) {
                $comments[$key] = get_string('contentadded', 'report_trackertools', userdate($comment->datecreated))."\n".
                                    format_string($comment->filename);
            }
            $result = implode("\n", $comments);
        } else {
            $result = $comments;
        }
    }
    return $result;
}


/**
 * Processes a user row from raw SQL to dataformat export format
 *
 * @uses $SESSION
 * @param object $row user data to export
 * @return object data for exportation
 */
function report_trackertools_exportissue_row($row) {
    global $DB, $SESSION;
    
    $columns = $SESSION->report_trackertools_export_columns;
    $statuskeys = $SESSION->report_trackertools_export_statuskeys;
    $elements = $SESSION->report_trackertools_export_elements;
    $names = get_all_user_name_fields();
    $user = core_user::get_support_user(); 
    
    $newrow = array();
    foreach($columns as $col) {

        $value = '';
        if(isset($row->{$col})) {
            $value = $row->{$col};
        }
        
        $prefix1 = 'su';
        $prefix2 = 'user';
        switch($col) {
            case 'assignedto' : $prefix1 = 'tu';
            case 'reportedby' : 
                    if($value) {
                        foreach($names as $field) {
                            $user->{$field} = $row->{$prefix1.$field};
                        }
                        $value = fullname($user, false, 'lastname');
                    } else {
                        $value = '';
                    }
                    break;
            case 'assignedtoidnumber' : 
            case 'reportedbyidnumber' :
                    $value = $row->{$col};
                    break;
            case 'datereported' :
            case 'usermodified' :
            case 'userlastseen' :
                    $value =  userdate($value);
                    break;
            case 'summary' :
                    $value = format_string($value);
                    break;
            case 'description' :
            case 'resolution' :
                    $value = format_text($value, $row->{$col.'format'});
                    break;
            case 'status' : 
                    $value = $statuskeys[$row->status];
                    break;
            case 'commentdev' : $prefix2 = 'dev';
            case 'commentuser' : 
                    $value = report_trackertools_issue_comments($row, $prefix2);
                    break;
            case 'filedev' : $prefix2 = 'dev';
            case 'fileuser' :
                    $value = report_trackertools_issue_files($row, $prefix2);
                    break;
            default : 
                    if(array_key_exists($col, $elements)) {
                        $value = $elements[$col]->view($row->id);
                    }              
        }
        $newrow["$col"] = format_string($value);
    }
    
    return $newrow;
}

/**
 * Collects and download field files as a ZIP
 *
 * @param string $filename file name, with extension
 * @param stdClass $fromform object with data from user input form
 * @return string prefixed path filename
 */
function report_trackertools_get_filename($filename, $issue, $fromform) {
    $pathfilename = '';
    $prefixedfilename = '';
    $names = get_all_user_name_fields();
    $user = core_user::get_support_user(); 
    
    if ($fromform->groupfield) {
        if ($fromform->groupfield == REPORT_TRACKERTOOLS_GROUP_ISSUE) {
            // is by a field
            $prefixedfilename = clean_filename($issue->id);
        } else {
            $prefix = 'su';
            if($fromform->groupfield == REPORT_TRACKERTOOLS_GROUP_DEV) {
                $prefix = 'tu';
            }
            foreach($names as $field) {
                $user->{$field} = $issue->{$prefix.$field};
            }
            $prefixedfilename = clean_filename(fullname($user, false, 'lastname').'('.$issue->{$prefix.'idnumber'}.')'); 
        }
        
        $pathfilename = $prefixedfilename . '/' . $filename;
    } else {
        $pathfilename =  $filename;
    }
    
    if(isset($filesforzipping[$pathfilename])) {
        $parts = pathinfo($pathfilename); 
        $pathfilename = $parts['filename']."({$issue->id}-{$issue->elementname}).".$parts['extension'];
        if($parts['dirname']) {
            $pathfilename = $parts['dirname'].'/'.$pathfilename;
        }
        
    }
    
    return $pathfilename;
}



/**
 * Collects and download field files as a ZIP
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_download_files($course, $tracker, $fromform) {
    global $CFG, $DB;
    
    //  pathfilename includes folder structure

    $params = array('trackerid' => $tracker->id);
    
    list($issuewhere, $inparams) = report_trackertools_issue_where_sql($tracker->id, $fromform);
    $params = array_merge($params, $inparams);
    
    $downfieldwhere = '';            
    if($fromform->downfield > 0 ) { // >0 meand an element field
        $downfieldwhere = ' AND ia.elementid = :downfield ';
        $params['downfield'] = $fromform->downfield;
    }
    
    $sortorder = '';
    if($fromform->groupfield) {
        if($fromform->groupfield > 0) { // means sort by a file field content
            $sortorder = ' ia.elementitemid ASC,  i.id  , ';
        } elseif($fromform->groupfield == REPORT_TRACKERTOOLS_GROUP_ISSUE) {
            $sortorder = ' i.id, ';
        } elseif($fromform->groupfield == REPORT_TRACKERTOOLS_GROUP_USER) {
            $sortorder = ' i.reportedby, i.id, ';
        }
        elseif($fromform->groupfield == REPORT_TRACKERTOOLS_GROUP_DEV) {
            $sortorder = ' i.assignedto, i.id, ';
        }
    }

    $studentuserfields = 'su.id AS suid, su.idnumber AS suidnumber, '.
                                get_all_user_name_fields(true, 'su', '', 'su');
    $tutoruserfields = 'tu.id AS tuid, tu.idnumber AS tuidnumber, '.
                                get_all_user_name_fields(true, 'tu', '', 'tu');

                                 
    $sql = "SELECT CONCAT_WS('-', i.id, ia.id) AS idid, i.id, i.trackerid, i.reportedby, i.assignedto, i.summary, ia.id AS iaid, ia.elementid, ia.elementitemid, 
                        (SELECT ee.name FROM {tracker_element} ee WHERE ee.id = ia.elementid) AS elementname, 
                        $studentuserfields , $tutoruserfields 
                FROM {tracker_issue} i 
                JOIN {user} su ON su.id = i.reportedby 
                LEFT JOIN {user} tu ON tu.id = i.assignedto 
                LEFT JOIN {tracker_issueattribute} ia ON ia.issueid = i.id AND ia.trackerid = i.trackerid 
                    AND EXISTS (SELECT 1 FROM {tracker_element} e WHERE ia.elementid = e.id AND e.type = 'file') 
            WHERE $issuewhere $downfieldwhere
            ORDER BY $sortorder ia.timemodified ASC, i.datereported ASC  ";
            
    $rs_issues =  $DB->get_recordset_sql($sql, $params);  

    $fs = get_file_storage();
    $context = context_module::instance($fromform->id);
    $filesforzipping = array();
    
    if($rs_issues->valid()) {
        $comments = array();
        foreach($rs_issues as $issue) {
            // add files on issue elements fields 
            if($fromform->downfield >= 0 && $issue->elementid) { // this means all or only a field
                // add the file on searching result 
                // tracker module do not allow directories in file areas. 
                if($files = $fs->get_area_files($context->id, 'mod_tracker', 'issueattribute', $issue->iaid,  'filepath, filename', false)) {
                    foreach($files as $file) {
                        $filename = $file->get_filename();
                        if($filename != '.') {
                            $pathfilename = report_trackertools_get_filename($filename, $issue, $fromform);
                            $filesforzipping[$pathfilename] = $file;
                        }
                    }
                }
            }

            
            $files = array();
            if(!isset($comments[$issue->id])) {
                // add files on issue comments
                if($fromform->downfield == REPORT_TRACKERTOOLS_FILES_ALL || 
                        $fromform->downfield == REPORT_TRACKERTOOLS_FILES_USER) {
                    // add user files on comments
                    if($cfiles = report_trackertools_issue_files($issue, 'user', false)) {
                        $files = $files + $cfiles;
                    }
                }    
                // add files on issue comments
                if($fromform->downfield == REPORT_TRACKERTOOLS_FILES_ALL || 
                        $fromform->downfield == REPORT_TRACKERTOOLS_FILES_DEV) {
                    // add dev staff files on comments
                    if($cfiles = report_trackertools_issue_files($issue, 'dev', false)) {
                        $files = $files + $cfiles;
                    }
                }
                
                if($files) {
                    foreach($files as $cfile) {
                        $filename = $cfile->filename;
                        $fullpath = "/{$context->id}/mod_tracker/issuecomment/$cfile->id/$filename";
                        $file = $fs->get_file_by_hash(sha1($fullpath));                        
                        $pathfilename = report_trackertools_get_filename($filename, $issue, $fromform);
                        $filesforzipping[$pathfilename] = $file;
                    }
                }
                $comments[$issue->id] = 1;
            }
        }
        
    }
    $rs_issues->close();

    if (count($filesforzipping) == 0) {
        return get_string('nofiles', 'report_trackertools');
    } else {
        $tempzip = tempnam($CFG->tempdir . '/', 'tracker_');
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            // Send file and delete after sending.
            $groupname = '';
            /*
            if ($fromform->groupid) {
                $groupname = '-' . groups_get_group_name($fromform->groupid);
            }
            */
            
            $filename = clean_filename($course->shortname . '-' . $tracker->name . 
                                        $groupname. '.zip');

            if (!headers_sent() && error_get_last()==NULL ) {
                // Trigger a report event.
                $eventdata = array('context' => $context, 'objectid' => $tracker->id, 'other' => array('action' => 'download'));
                $event = \report_trackertools\event\report_download::create($eventdata);
                $event->trigger();
            
                send_temp_file($tempzip, $filename);
            } else {
                $baseurl = new moodle_url('/report/trackertools/download.php', array('id' => $fromform->id));
                return notice(get_string('errorheaderssent', 'local_trackertools'), $baseurl);
            }
            // We will not get here - send_temp_file calls exit.
        }
    }

}

/**
 * Collects all selected files on file of desired format
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_export_issues($course, $tracker, $fromform) {
    global $CFG, $DB, $SESSION;
    
    require_once($CFG->libdir . '/dataformatlib.php');
    
    $message = '';
    $classname = 'dataformat_' . $fromform->dataformat . '\writer';
    if (!class_exists($classname)) {
        throw new coding_exception("Unable to locate dataformat/{$fromform->dataformat}/classes/writer.php");
    }
    $dataformat = new $classname;
    
    $filename = clean_filename($fromform->filename);
  
    list($sql, $params, $columns, $elements) = report_trackertools_exportuser_getsql($tracker, $fromform);
    
    $SESSION->report_trackertools_export_columns = array_keys($columns);
    $SESSION->report_trackertools_export_elements = $elements;
    $SESSION->report_trackertools_export_statuskeys = tracker_get_statuskeys($tracker);
   
    $rs_issues = $DB->get_recordset_sql($sql, $params); 
    if($rs_issues->valid() && $columns) {
        if (!headers_sent() && error_get_last()==NULL ) {
            download_as_dataformat($filename, $fromform->dataformat, $columns, $rs_issues, 'report_trackertools_exportissue_row');

        } else {
            $message = get_string('errorheaderssent', 'report_trackertools');
        }
    }
    $rs_issues->close();
    unset($SESSION->report_trackertools_export_columns);
    unset($SESSION->report_trackertools_export_elements);
    unset($SESSION->report_trackertools_export_statuskeys);

    return $message;
}


/**
 * Imports issue entries from a CSV file
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param array $fixed mandatory import fields
 * @param Class $cir csv_import_reader object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_import_check_columns($tracker, $fixed, $cir) {

    $message = '';
    
    if (!$fieldnames = $cir->get_columns()) {
        return get_string('csvnocolumns', 'report_trackertools');
    }
    
    $elements = array();
    tracker_loadelementsused($tracker, $elements);
    foreach($elements as $key => $element) {
        if (!$element->active) {
            unset($elements[$key]);
            continue;
        }
        if($element->mandatory && !$element->private && 
                !(($element->type == 'file') || ($element->type == 'capcha'))) {
            $fixed['element'.$element->name] = format_string($element->description);
        }
    }
        
    if($failures = array_diff($fixed, $fieldnames)) {
        return get_string('csvmissingcolumns', 'report_trackertools', implode(', ', $failures));
    }    
        
    return $message;
}        
        
/**
 * Imports issue entries from a CSV file
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @param Class $cir csv_import_reader object with data from user input form
 * @param array $datacolums array with DB datafield keys and display fieldnames
 * @return string notice message of success
 */
function report_trackertools_import_issues($course, $tracker, $fromform, $cir, $datacolumns) {
    global $CFG, $DB, $USER; 

    require_once($CFG->libdir.'/csvlib.class.php');
    
    $statuskeys = tracker_get_statuskeys($tracker);
    $statuskeys = array_map('strtolower',  $statuskeys);
    
    $elements = array();
    $elementoptions = array();
    tracker_loadelementsused($tracker, $elements);
    foreach($elements as $key => $element) {
        $elementnames['element'.$element->name] = $element->id;
        $datacolumns['element'.$element->name] = format_string($element->description);
    }
    $columns = $cir->get_columns();
    
    $recordsadded = 0;
    $cir->init();
    $recordsadded = 0;
    $issue = new  stdClass();
    $issue->trackerid = $tracker->id;
    $issue->reportedby = 0;
    $issue->assignedto = 0;
    $issue->summary = '';
    $issue->datereported = time();
    $issue->bywhomid = $USER->id;
    $issue->status = 0;
    $issue->description = '';
    $issue->descriptionformat = 1;
    $issue->resolution = '';
    $issue->resolutionformat = 1;
    $issue->userlastseen = 0;
    $issue->resolutionpriority = 100;
    $issue->usermodified = $issue->datereported;
    $issue->resolvermodified = $issue->datereported + 1;
    $issue->timeassigned = $issue->datereported;

    $attribute = new stdClass();
    $attribute->trackerid = $tracker->id; 	
    $attribute->issueid = 0;
    $attribute->elementid = 0;
    $attribute->timemodified = time();
    
    $item = new stdClass();
    $item->active = 1;
    $item->autoresponse = '';
    
    $usernames = get_all_user_name_fields();
    $noreplyuser = core_user::get_noreply_user();
    foreach($usernames as $field) {
        $noreplyuser->{$field} = $USER->{$field};
    }
    $mails = array();
    
    while ($record = $cir->next()) {
        // Fill data_content with the values imported from the CSV file:
        $issue->id = null;
        $item->id = null;
        $attribute->id = null;
        foreach ($record as $key => $value) {
            $key = array_search($columns[$key], $datacolumns);
            if($key === false) {
                continue;
            }
            switch($key) {
                case 'reportedby' :
                case 'assignedto' :
                            if(($fromform->userencoding == 'idnumber') || ($fromform->userencoding == 'username'))  {
                                $value = $DB->get_field('user', 'id', array($fromform->userencoding => $value));
                            }
                            break;
                case 'status':
                            $value = array_search(trim(core_text::strtolower($value)), $statuskeys);
                            break;
                case 'datereported':
                case 'usermodified' :
                case 'resolvermodified' :                    
                case 'userlastseen' :
                case 'timeassigned' :                    
                            $value = strtotime($value);
                            break;
            }
            $issue->{$key} = $value;
        }

        if(!$issue->resolvermodified > $issue->datereported) {
            $issue->resolvermodified = $issue->datereported + 1;
        }
        
        
        $params = array();
        foreach(array('reportedby', 'assignedto', 'summary') as $k) {
            if(isset($issue->{$k}) && $issue->{$k}) {
                $params[$k] = $issue->{$k};
            } else {
                $params[0] = true;
            }
        }
    
        if(!isset($params[0])) { 
            if($oldissue = $DB->get_record('tracker_issue', $params, 'id, assignedto, status, bywhomid, timeassigned')) {
                //update, if allowed
                if($fromform->ignoremodified) {
                    $issue->id = $oldissue->id;
                    $DB->update_record('tracker_issue', $issue);
                    
                    if ($oldissue->assignedto != $issue->assignedto) {
                        $ownership = new StdClass;
                        $ownership->trackerid = $tracker->id;
                        $ownership->issueid = $oldissue->id;
                        $ownership->userid = $oldissue->assignedto;
                        $ownership->bywhomid = $oldissue->bywhomid;
                        $ownership->timeassigned = ($oldissue->timeassigned) ? $oldissue->timeassigned : time();
                        if (!$DB->insert_record('tracker_issueownership', $ownership)) {
                            print_error('errorcannotlogoldownership', 'tracker');
                        }
                        tracker_notifyccs_changeownership($issue->id, $tracker);
                    }
                    // send state change notification
                    if ($oldissue->status != $issue->status) {
                        tracker_notifyccs_changestate($issue->id, $tracker);

                        // log state change
                        $stc = new StdClass;
                        $stc->userid = $USER->id;
                        $stc->issueid = $issue->id;
                        $stc->trackerid = $tracker->id;
                        $stc->timechange = time();
                        $stc->statusfrom = $oldissue->status;
                        $stc->statusto = $issue->status;
                        $DB->insert_record('tracker_state_change', $stc);

                        if ($stc->statusto == RESOLVED || $stc->statusto == PUBLISHED) {
                            // Check if was cascaded and needs backreported then backreport
                            // TODO : backreport to original
                        }
                    }
                } else {
                    continue; // if this record already exists & not updating do nothin else & move to nex one
                }
            } else {
                //insert as new issue
                $issue->id = $DB->insert_record('tracker_issue', $issue);
            }
            $issue->issueid = $issue->id;

            // now process elements & ownership
            tracker_register_cc($tracker, $issue, $issue->reportedby);
            
            foreach ($record as $key => $value) {
                $key = array_search($columns[$key], $datacolumns);
                if(($key === false) || !array_key_exists($key, $elementnames)) {
                    continue;
                }
            
                //we have element data in import 
                $element = $elements[$elementnames[$key]];
                $attribute->issueid = $issue->id;
                $attribute->elementid = $element->id;
                // first retrieve / insert issue attribute
                if($oldid = $DB->get_field('tracker_issueattribute', 'id',
                                                array('trackerid'=>$tracker->id, 'issueid'=>$issue->id, 'elementid'=>$element->id))) {
                    $attribute->id = $oldid;
                } else {
                    $attribute->id = $DB->insert_record('tracker_issueattribute', $attribute); 
                }
                
                // now we want update elementfield (if we are here we has checked update alwais
                if(isset($attribute->id) && $attribute->id) {
                    if($element->type_has_options()) {
                        $options = array();
                        foreach($element->options as $oid => $option) {
                            $options[$oid] = core_text::strtolower(trim($option->name)); 
                        }
                        $value = array_search(trim($value), $options);
                        if($value === false && $fromform->addoptions) {
                            $item->elementid = $element->id;
                            $item->sortorder = count($element->options) + 1;
                            $item->name = get_string('', 'record_trackertools', $item->sortorder);
                            $item->description = $value;
                            $value = $DB->insert_record('tracker_elementitem', $item);
                        }
                    }
                    //if no options is a text field or similar, elementitemid stores real content
                    if(isset($value) && $value !== false) {
                        $DB->set_field('tracker_issueattribute', 'elementitemid', $value, array('id'=>$attribute->id));
                    }
                }
            }
            
            // now send mails
            if($fromform->mailtouser || $fromform->mailtodev) {
                report_trackertools_send_email($course, $tracker, $fromform, $issue, $mails, $noreplyuser);
            }
            $recordsadded++;
        }
    }
    $cir->close();
    $cir->cleanup(true);

    if($fromform->mailtouser || $fromform->mailtodev) {
        report_trackertools_send_control_email($course, $tracker, $mails);
    }

    return $recordsadded;
}


function report_trackertools_send_email($course, $tracker, $fromform, $issue, &$mails, $noreplyuser = '') {
    global $USER, $DB;

    $usernames = get_all_user_name_fields();
    if(!$noreplyuser) {
        $noreplyuser = core_user::get_noreply_user();
            foreach($usernames as $field) {
            $noreplyuser->{$field} = $USER->{$field};
        }
    }

    $flag = '';
    $errorstr= get_string('mailerror', 'report_trackertools');
    $subject = $course->shortname.': '.$fromform->messagesubject;
    $messagetext = $fromform->messagebody;
    $messagehtml = $fromform->messagebody;
    $params = array('t' => $tracker->id, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issue->id);
    $url = new moodle_url('/mod/tracker/view.php', $params);
    $messagehtml .= '<br />'.html_writer::link($url, get_string('aboutissue', 'report_trackertools', format_string($tracker->ticketprefix.$issue->id.' - '.$issue->summary))); 
    
    //$messagecontrol = $messagehtml.'<br />'.html_writer::link($url, get_string('inrecord', 'report_trackertools', format_string($data->name))); 
    
    $messagetext = html_to_text($messagehtml);
    
    $names = get_all_user_name_fields(false, '');
   
    if($fromform->mailtouser) {
        $userstruser = tracker_getstring('reportedby', 'tracker');
        if($user = $DB->get_record('user', array('id'=>$issue->reportedby), 'id, email, mailformat, '.implode(', ', $usernames))) {
            if(!email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtml)) {
                $flag =  ' - '.$errorstr;
            }  
            $mails[] = $issue->summary. ' - '. fullname($user, false, 'lastname'). " ($userstruser) $flag";
        }
    }
    if($fromform->mailtodev) {
        $userstrdev = tracker_getstring('assignedto', 'tracker');
        if($user = $DB->get_record('user', array('id'=>$issue->assignedto), 'id, email, mailformat, '.implode(', ', $usernames))) {
            if(!email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtml)) {
                $flag =  ' - '.$errorstr;
            }
            $mails[] = $issue->summary. ' - '. fullname($user, false, 'lastname'). " ($userstrdev) $flag";
        }
    }
}



function report_trackertools_send_control_email($course, $tracker, $sent, $userid = 0, $noreplyuser = '') {
    global $USER, $DB;
    // send control e-mail to operating staff
    $usernames = get_all_user_name_fields();
    if(!$noreplyuser) {
        $noreplyuser = core_user::get_noreply_user();
            foreach($usernames as $field) {
            $noreplyuser->{$field} = $USER->{$field};
        }
    }
    
    if(!$userid) {
        $user = $USER;
    } else {
        $user = $DB->ger_record('user', array('id'=>$user), 'id, idnumber, mail, mailformat, '.implode(', ', $usernames));
    }
    
    $subject = $course->shortname.': '.get_string('controlemailsubject', 'report_trackertools');
    $messagehtml = get_string('controlemailbody', 'report_trackertools',  $tracker->name);
    $params = array('t' => $tracker->id, 'view' => 'view', 'screen' => 'browse');
    $url = new moodle_url('/mod/tracker/view.php', $params);
    $messagehtml .= '<br />'.html_writer::link($url, format_string($tracker->name)); 
    $messagehtml .= "<br />\n".implode("<br />\n", $sent);
    $messagetext = html_to_text($messagehtml);
    
    email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtml);
}


/**
 * Sends warning emails to users related to issue 
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @param array $issues object with data from user input form 
 * @return string notice message of success
 */
function report_trackertools_warning_issues($course, $tracker, $fromform, $issues) {
    global $DB, $USER;

    $issueswarn = 0;

    $usernames = get_all_user_name_fields();
    $noreplyuser = core_user::get_noreply_user();
    foreach($usernames as $field) {
        $noreplyuser->{$field} = $USER->{$field};
    }
    
    $mails = array();
    if($issues) {
        foreach($issues as $issue) {
            // now send mails
            if($fromform->mailtouser || $fromform->mailtodev) {
                report_trackertools_send_email($course, $tracker, $fromform, $issue, $mails, $noreplyuser);
            }
            $issueswarn++;
        }
        
        if($fromform->mailtouser || $fromform->mailtostaff) {   
            report_trackertools_send_control_email($course, $tracker, $mails);
        }
    }
    
    return $issueswarn;
}


/**
 * Sets user preferences in bulk 
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_mailoptions($course, $tracker, $fromform) {
    global $DB, $USER;

    $message = '';

    foreach(array('open', 'resolving', 'waiting', 'testing', 'published', 'resolved', 'abandonned', 'oncomment') as $field) {
        $$field = isset($fromform->{$field}) ?  $fromform->{$field} : 0;
    }

    $context = context_module::instance($fromform->id);
    
    $users = array();
    $developers = array();
    if($fromform->usertypeuser) {
        $users = get_enrolled_users($context, 'mod/tracker:report', 0, 'u.id, u.username, u.idnumber');
    }
    if($fromform->usertypedev) {
        $developers = get_enrolled_users($context, 'mod/tracker:develop', 0, 'u.id, u.username, u.idnumber');
    }
    
    $users = $users + $developers;
    
    $pref = new StdClass();
    $pref->trackerid = $tracker->id;
    $pref->name = 'eventmask';
    $pref->value = $open * EVENT_OPEN + $resolving * EVENT_RESOLVING + $waiting * EVENT_WAITING + $resolved * EVENT_RESOLVED + $abandonned * EVENT_ABANDONNED + $oncomment * ON_COMMENT + $testing * EVENT_TESTING + $published * EVENT_PUBLISHED;
    
    $count = 0;
    $errors = array();
    foreach($users as $user) {
        $pref->userid = $user->id;
    
        if (!$oldpref = $DB->get_record('tracker_preferences', array('trackerid' => $tracker->id, 'userid' => $user->id, 'name' => 'eventmask'))) {
            if (!$DB->insert_record('tracker_preferences', $pref)) {
                //print_error('errorcannotsaveprefs', 'tracker', $url.'&amp;view=profile');
                $error[] = $user->id;
            } else {
                $count++; 
            }
        } elseif($fromform->forceupdate) {
            $pref->id = $oldpref->id;
            if (!$DB->update_record('tracker_preferences', $pref)) {
                //print_error('errorcannotupdateprefs', 'tracker', $url.'&amp;view=profile');
                $error[] = $user->id;
            } else {
                $count++; 
            }
        }    
    }

    $message = get_string('saveduserprefs', 'report_trackertools', $count);
    if($errors) {
        $message .= '<br />'.get_string('errorcannotsaveprefs', 'report_trackertools', implode(', ', $errors));
    }
    return $message;
}
    
    
/**
 * Sets a field (or fields values) in bulk issues
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_setfield_issues($course, $tracker, $fromform) {
    global $DB, $USER;

    $message = '';
    
    list($issuewhere, $params) = report_trackertools_issue_where_sql($tracker->id, $fromform, '');

    // we set each field in form separately  
    // status field
    if(isset($fromform->statusmodify) && $fromform->statusmodify) {
        $issues = $DB->get_records_select('tracker_issue', $issuewhere, $params, 'id, status, resolution');
        if($DB->set_field_select('tracker_issue', 'status', $fromform->status, $issuewhere, $params)) {
            //then whe notify change status
            foreach($issues as $issue) {
                if($issue->status != $fromform->status) {
                    tracker_notifyccs_changestate($issue->id, $tracker);
                    // log state change
                    $stc = new StdClass;
                    $stc->userid = $USER->id;
                    $stc->issueid = $issue->id;
                    $stc->trackerid = $tracker->id;
                    $stc->timechange = time();
                    $stc->statusfrom = $issue->status;
                    $stc->statusto = $fromform->status;
                    $DB->insert_record('tracker_state_change', $stc);
                }
            }
        }
    }
    
    // resolution field
    if(isset($fromform->resolutionmodify) && $fromform->resolutionmodify) {
        if($DB->set_field_select('tracker_issue', 'resolution', $fromform->resolution_editor['text'], $issuewhere, $params)) {
            $DB->set_field_select('tracker_issue', 'resolutionformat', $fromform->resolution_editor['format'], $issuewhere, $params);
        }
    }

    // make elements array indexed by field name
    $elements = array();
    tracker_loadelementsused($tracker, $elements);
    foreach($elements as $key => $element) {
        $elements[$element->name] = $element;
        unset($elements[$key]);
    }
    
    $issues = array();
    $fromform->trackerid = $tracker->id;
    foreach($fromform as $key => $value) {
        if($value &&  strpos($key,'modifyelement')) {
            // OK, we have a key with request for change
            $elementkey = strstr($key,'modifyelement', true);
            if($element = $elements[$elementkey]) {
                //OK, we have an avtive element, update it 
                if(!$issues) {
                    $issues = $DB->get_records_select('tracker_issue', $issuewhere, $params, 'id, status');
                }
                foreach($issues as $issue) {
                    $fromform->issueid = $issue->id;
                    $elements[$elementkey]->formprocess($fromform);
                }
            }
        }
    }
    
    $message = get_string('changessaved');
    
    return $message;
}


/**
 * Parses input text contaning user ID from input  (maybe moodle ID, idnumber, username)
 * result indexed by the ID field used
 *
 * @param string $userids text containig numbers or ID codes
 * @param string $userfield the fields that is used for identifying users
 * @return arrays of user objects suitable for fullname() & not found ids
 */
function report_trackertools_userids_from_input($userids, $userfield) {
    global $DB;
    
    $userids = preg_replace('/\s+/', '|', $userids);
    $delimiter = array("|"," ",",",";","\n");
    $replace = str_replace($delimiter, $delimiter[0], trim($userids));
    $userids = array_unique(explode($delimiter[0], $replace));
    $k = array_search('', $userids);
    if($k !== false) {
        unset($userids[$k]);
    }
    natcasesort($userids);
    
    $ufields = array('id', 'username', 'idnumber');
    if($k = array_search($userfield, $ufields)) {
        // prepends userfield as first, the remove duplicates
        array_unshift($ufields, $userfield);
        $ufields = array_unique($ufields);
    }
    $ufields = implode(', ',$ufields).', email, mailformat, '.get_all_user_name_fields(true);

    $users = $DB->get_records_list('user', $userfield, $userids, 'lastname', $ufields);
    $notfound = array_diff($userids, array_keys($users));
    
    return array($users, $notfound);
}





function report_trackertools_add_userfiles($context, $issue, $usersfilesdir, $userfilenames) {
    global $DB;

    if($usersfilesdir && $userfilenames) {
    // now we do user file  storage
        $fs = get_file_storage();
        $dir = $fs->get_file_by_hash($usersfilesdir);
        $filepath = $dir->get_filepath();
        $filecontextid = $dir->get_contextid();

        $sql = "SELECT a.id
                    FROM {tracker_issueattribute} a
                    JOIN {tracker_element} e ON a.elementid = e.id AND e.type = 'file'
                    WHERE a.trackerid = ? AND a.issueid = ?  ";

        if($attributeid = $DB->get_field_sql($sql, array( $issue->trackerid,  $issue->id))) {
            $fileinfo = array(
                'contextid' => $context->id, // ID of context
                'component' => 'mod_tracker',     // usually = table name
                'filearea' => 'issueattribute',     // usually = table name
                'itemid' => $attributeid,               // usually = ID of row in table
                'filepath' => '/',           // any path beginning and ending in /
                'filename' => ''); // any filename

            foreach($userfilenames as $userfilename) {
                if($file = $fs->get_file($filecontextid, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)) {
                    $fileinfo['filename'] = $userfilename;
                    $newfile = $fs->create_file_from_storedfile($fileinfo, $file);
                }
            }
        }
    }
}



/**
 * Creates a new issue each for a bulk of users
 *
 * @param stdClass $course object
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string notice message of success
 */
function report_trackertools_create_issues($course, $tracker, $fromform) {
    global $DB, $USER;

    $issuecount = 0;
    
    $context = context_module::instance($fromform->id); // this is cm id
    
    $fromform->trackerid = $tracker->id;
    
    $issue = new StdClass();
    $issue->datereported = time();
    $issue->bywhomid = $USER->id;
    $issue->trackerid = $tracker->id;
    $issue->usermodified = $issue->datereported;
    $issue->userlastseen = 0;
    $issue->resolvermodified = $issue->datereported + 60;
    $issue->resolutionpriority = 10;
    foreach(array('assignedto', 'status', 'summary', 
                    'description', 'descriptionformat', 'resolution', 'resolutionformat') as $field) {
        $issue->{$field} = $fromform->{$field};
    }

    list($users, $notfound) = report_trackertools_userids_from_input($fromform->users, $fromform->ufield);
    
    $filepath = '';
    if($fromform->dir) {
        $fs = get_file_storage();
        $dir = $fs->get_file_by_hash($fromform->dir);
        $filepath = $dir->get_filepath();
    }
    
    $filerequired = get_string('filerequiredabsent', 'report_trackertools');
    $usernames = get_all_user_name_fields();
    $noreplyuser = core_user::get_noreply_user();
    foreach($usernames as $field) {
        $noreplyuser->{$field} = $USER->{$field};
    }
    $url = new moodle_url('/mod/tracker/view.php', array('id'=>$fromform->id,
                                                        'view'=>'view', 'screen'=>'viewanissue')); 
    foreach($users as $user) {
        /// Insert tracker
        $issue->reportedby = $user->id;
        
        // check files for this user (may be several extensions) 
        $userfilenames = array();
        if($fromform->dir) {
            $middle = $user->{$fromform->ufield};
            $suffixes = explode('/',$fromform->suffix);
            foreach($suffixes as $suffix) {
                $userfilename = $fromform->prefix.$middle.$suffix.$fromform->ext;
                if($fs->file_exists($context->id, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)  ) {
                    $userfilenames[] = $userfilename;
                }
            }
        }
        
        $userfilemsg = $userfilenames ? '' : '  : <span class="error">'.get_string('nouserfile', 'report_trackertools').'</span>';

        if($fromform->needuserfile  && !$userfilenames) {
            $errors[$user->id] = $user->idnumber.' - '.fullname($user).$filerequired;
        } else {
            $issue->id = $DB->insert_record('tracker_issue', $issue);
            if ($issue->id){
                $issuecount++;
                $fromform->issueid = $issue->id;
                tracker_recordelements($issue, $fromform);
                tracker_register_cc($tracker, $issue, $issue->reportedby);
                if($userfilenames) {
                    report_trackertools_add_userfiles($context, $issue, $fromform->dir, $userfilenames);
                }

                if($fromform->sendemail) {
                    //email_to_user
                    $a = new StdClass;
                    $url->param('issueid', $issue->id);
                    $a->url = $url; 
//                     $a->code = $tracker->ticketprefix.$issue->id;
                    $text = get_string('warningemailtxt',  'tracker', $a );
                    $html = ($user->mailformat == 1) ? get_string('warningemailhtml',  'tracker', $a ) : '';
                    email_to_user($user, $noreplyuser, get_string('warningsubject', 'tracker'), $text, $html);
                }
                $success[$user->id] = $user->idnumber.' - '.fullname($user).$userfilemsg;
            } else {
                $errors[$user->id] = $user->idnumber.' - '.fullname($user).$userfilemsg.' - '.get_string('inserterror','report_trackertools');
            }
        }
    }
    
    // control email
    if($fromform->sendemail) {
        $sent = array_merge($success, $errors);
        report_trackertools_send_control_email($course, $tracker, $sent, 0, $noreplyuser);
    }
    
    return $issuecount;
}


/**
 * Checks compliance of existing records with defined criteria
 *
 * 
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return array records that fullfill criteria
 */
function report_trackertools_compliance_list($tracker, $fromform) {
    global $DB;

    list($issuewhere, $params) = report_trackertools_issue_where_sql($tracker->id, $fromform);

    $studentuserfields = 'su.id AS suid, su.idnumber AS suidnumber, '.
                                get_all_user_name_fields(true, 'su', '', 'su');
    $tutoruserfields = 'tu.id AS tuid, tu.idnumber AS tuidnumber, '.
                                get_all_user_name_fields(true, 'tu', '', 'tu');

    $resolutionwhere = '';                                
    if($fromform->hasresolution) {
        if($fromform->hasresolution == REPORT_TRACKERTOOLS_NOEMPTY) {
            $resolutionwhere = ' AND ' . $DB->sql_isnotempty('tracker_issue', 'resolution', true, true);
        } elseif($fromform->hasresolution == REPORT_TRACKERTOOLS_EMPTY) {
            $resolutionwhere = ' AND ' . $DB->sql_isempty('tracker_issue', 'resolution', true, true);
        }
    }
                   
    $commentswhere = '';               
    $commentsjoin = '';
    if($fromform->hascomments) {
        $userswhere = '';
        if($fromform->commentsby == REPORT_TRACKERTOOLS_FILES_USER) {
            $userswhere = ' AND ic.userid = i.reportedby ';
        } elseif($fromform->commentsby == REPORT_TRACKERTOOLS_FILES_DEV) {
            $userswhere = ' AND (ic.userid = i.assignedto OR ic.userid != i.reportedby) ';
        }
        
        $commentsjoin = "LEFT JOIN {tracker_issuecomment} ic ON ic.trackerid = i.trackerid 
                                                                AND ic.issueid = i.id $userswhere ";
    
        if($fromform->hascomments == REPORT_TRACKERTOOLS_NOEMPTY) {
            $commentswhere = ' AND ic.id IS NOT NULL ';
        } elseif($fromform->hascomments == REPORT_TRACKERTOOLS_EMPTY) { 
            $commentswhere = ' AND ic.id IS NULL ';
        } elseif($fromform->hascomments == REPORT_TRACKERTOOLS_LAST) { 
            $inneruserswhere = str_replace('ic.', 'ic2.', $userswhere);
            $commentswhere = ' AND ic.datecreated = (SELECT MAX(ic2.datecreated) FROM {tracker_issuecomment} ic2 
                                                      WHERE ic2.trackerid = i.trackerid 
                                                                AND ic2.issueid = i.id  '.$inneruserswhere.' ) ';
        }
    
    }

    $fileswhere = '';               
    $filesjoin = '';
    if($fromform->hasfiles) {
        $userswhere = '';
        if($fromform->filesby == REPORT_TRACKERTOOLS_FILES_USER) {
            $userswhere = ' AND icf.userid = i.reportedby ';
        } elseif($fromform->filesby == EPORT_TRACKERTOOLS_FILES_DEV) {
            $userswhere = ' AND (icf.userid = i.assignedto OR icf.userid != i.reportedby) ';
        }
        $filesjoin = "LEFT JOIN {tracker_issuecomment} icf ON icf.trackerid = i.trackerid 
                                                                AND icf.issueid = i.id $userswhere ";
    
        if($fromform->hasfiles == REPORT_TRACKERTOOLS_NOEMPTY) {
            $fileswhere = " AND (icf.id IS NOT NULL 
                                AND EXISTS (SELECT 1 FROM {files} f  
                                            WHERE f.component = 'mod_tracker' AND f.filearea = 'issueattribute' 
                                            AND f.filesize > 0 AND f.itemid = icf.id )) ";
        } elseif($fromform->hasfiles == REPORT_TRACKERTOOLS_EMPTY) { 
            $fileswhere = " AND (icf.id IS NULL 
                                OR NOT EXISTS (SELECT 1 FROM {files} f 
                                                WHERE f.component = 'mod_tracker' AND f.filearea = 'issueattribute' 
                                                AND f.filesize > 0 AND f.itemid = icf.id )) ";
        }
    }     
     
    $sql = "SELECT i.id, i.trackerid, i.reportedby, i.assignedto, i.summary, i.resolution, i.resolutionformat, 
                        $studentuserfields , $tutoruserfields 
                FROM {tracker_issue} i 
                JOIN {user} su ON su.id = i.reportedby 
                LEFT JOIN {user} tu ON tu.id = i.assignedto 
                $commentsjoin
                $filesjoin
    
                WHERE $issuewhere   $commentswhere  $fileswhere  $resolutionwhere
                GROUP BY i.id 
                ORDER BY i.summary ASC, i.status ";

    
    return $DB->get_records_sql($sql, $params);
}


/**
 * Checks compliance of existing records with defined criteria
 *
 * 
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return array records that fullfill criteria
 */
function report_trackertools_field_compliance_list($tracker, $fromform) {
    global $DB;

    list($issuewhere, $params) = report_trackertools_issue_where_sql($tracker->id, $fromform);

    $elements = array();
    tracker_loadelementsused($tracker, $elements);
    
    $element = $elements[$fromform->checkedfield];
    $params['trackerid'] = $tracker->id;
    $params['elementid'] = $element->id;
    
    $names = get_all_user_name_fields(true, 'u');
    $otherjoins = '';
    
    if($fromform->fillstatus) {
        // means check absence
        $presence = ' AND ia.id IS NULL';
        $fields = 'ei.id, ei.name AS itemname, '; 
        if($fromform->menutype == REPORT_TRACKERTOOLS_MENUTYPE_USER) {
            
            $fields .= "u.username, u.idnumber, $names, u.id AS userid";
            $otherjoins =  ' JOIN {user} u ON u.idnumber = ei.name '; 
        } elseif($fromform->menutype == REPORT_TRACKERTOOLS_MENUTYPE_COURSE) {
            $fields .= "c.id as courseid, c.shortname, CONCAT(c.shortname, '-',c.fullname) AS name, 
                        u.username, u.idnumber, $names, u.id AS userid";
            $otherjoins = ' JOIN {course} c ON c.shortname = ei.name  
                            JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :ctxcourse
                            LEFT JOIN {role_assignments} ra  ON ra.userid = (SELECT userid FROM {role_assignments} 
                                                        WHERE contextid = ctx.id  AND roleid = :role 
                                                        ORDER BY id LIMIT 1) 
                            LEFT JOIN {user} u ON ra.userid = u.id ' ; 
            $params['role'] = $fromform->userrole; 
            $params['ctxcourse'] = CONTEXT_COURSE; 
        } else {
            $fields = "ei.*";
        }
        $fields .= ", {$fromform->menutype} AS menutype ";
        
    } else {
        $presence = ' AND ia.id IS NOT NULL ';
        $fields = ' i.*,  ei.id AS itemid, ei.name AS itemname '; 
        $otherjoins = 'LEFT JOIN {user} tu ON tu.id = i.assignedto 
                       LEFT JOIN {user} su ON su.id = i.reportedby ';
        
        $studentuserfields = 'su.id AS suid, su.idnumber AS suidnumber, '.
                                    get_all_user_name_fields(true, 'su', '', 'su');
        $tutoruserfields = 'tu.id AS tuid, tu.idnumber AS tuidnumber, '.
                                    get_all_user_name_fields(true, 'tu', '', 'tu');
        
        $fields .= ', '. $studentuserfields .', '. $tutoruserfields;
        
    }
    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($element->options),SQL_PARAMS_NAMED, 'op');
    
    $params = array_merge($params, $inparams);
    
    $sql = "SELECT $fields     
            FROM {tracker_elementitem} ei
            JOIN {tracker_elementused} eu ON ei.elementid = eu.elementid AND eu.trackerid = :trackerid
            LEFT JOIN {tracker_issueattribute} ia ON ia.elementid = ei.elementid AND ia.elementitemid = ei.id AND ia.trackerid = eu.trackerid
            LEFT JOIN {tracker_issue} i ON ia.trackerid = i.trackerid AND ia.issueid = i.id AND $issuewhere
            $otherjoins
            WHERE ei.elementid = :elementid AND ei.id $insql $presence  
            GROUP BY ei.id ";
            
    return $DB->get_records_sql($sql, $params);
}

/**
 * Checks compliance of existing records with defined criteria
 *
 * 
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return array records that fullfill criteria
 */
function report_trackertools_user_compliance_list($tracker, $fromform) {
    global $DB;

    list($issuewhere, $params) = report_trackertools_issue_where_sql($tracker->id, $fromform);

    $elements = array();
    tracker_loadelementsused($tracker, $elements);
    $element = $elements[$fromform->checkedfield];
    $params['elementid'] = $element->id;
    $params['trackerid'] = $tracker->id;
    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($element->options),SQL_PARAMS_NAMED, 'op');
    
    $sql = "SELECT i.*, ia.elementitemid     
            FROM {tracker_issue} i 
            JOIN {tracker_issueattribute} ia ON ia.trackerid = i.trackerid AND ia.issueid = i.id
            WHERE $issuewhere AND i.trackerid = :trackerid AND ia.elementid = :elementid 
                    AND (ia.elementitemid IS NOT NULL AND ia.elementitemid <> '') ";
    
    $issues = $DB->get_records_sql($sql, $params); 
    
    
    $params = array('elementid' => $element->id, 'elementid' => $element->id);
    foreach($issues as $iid => $issue) {
    
        //search elememtitems , join comments
        list($insql, $inparams) = $DB->get_in_or_equal(array_keys($element->options),SQL_PARAMS_NAMED, 'op');
        $sql = "SELECT ei.id, ei.name, u.id AS userid 
                FROM {tracker_elementitem} ei
                JOIN {tracker_elementused} eu ON ei.elementid = eu.elementid AND eu.trackerid = :trackerid
                JOIN {user} u  ON u.idnumber = ei.name
                LEFT JOIN {tracker_issuecomment} c ON eu.trackerid = c.trackerid AND c.issueid = :issueid AND u.id = c.userid
                
                WHERE ei.elementid = :elementid AND ei.id $insql 
                
                ";
        $params = array_merge($params, $inparams);  
        $params['issueid'] = $iid;
        $users = $DB->get_records-sql($sql, $params);
    
    
    }
    
    
}

/**
 * Assign a query to a developer
 *
 * 
 * @param stdClass $tracker module record on database
 * @param stdClass $fromform object with data from user input form
 * @return array records that fullfill criteria
 */
function report_trackertools_assigntask($tracker, $fromform) {
    global $DB;

    $task = new stdClass();
    $task->trackerid = $tracker->id;
    $task->queryid = $fromform->query;
    $task->userid = $fromform->user;
    
    if(!$DB->record_exists('report_trackertools_devq', get_object_vars($task))) {
        return $DB->insert_record('report_trackertools_devq', $task);
    }
    
    return -1;
}    
    
/**
 * Set assignedto parameter in selected issues
 *
 * @uses $DB
 * @param object $tracker object fron DB
 * @param int $queryid, 
 * @param int $userid 
 */
function report_trackertools_issue_assignation($tracker, $queryid, $userid) {
    global $DB;

    $fields = tracker_extractsearchparametersfromdb($queryid);
    $searchqueries = empty($fields) ? false : tracker_constructsearchqueries($tracker->id, $fields);
    $issues = array();
    
    if ($searchqueries) {
        $searchqueries = tracker_constructsearchqueries($tracker->id, $fields);
        $sql = str_replace('WHERE ', 'WHERE i.assignedto = 0 AND ', $searchqueries->search);
        $issues = $DB->get_records_sql($sql, null);
        
        if($issues) {
            $issues = array_keys($issues);
            foreach(array_chunk($issues, 250) as $issueids) {
                list($insql, $inparams) = $DB->get_in_or_equal($issueids);
                $DB->set_field_select('tracker_issue', 'assignedto', $userid, "id $insql", $inparams);
            }
        }
    }
    
    return count($issues);
}



