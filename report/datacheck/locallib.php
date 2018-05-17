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
 * Library code for the datacheck report.
 *
 * @package     report_datacheck
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('REPORT_DATACHECK_COMPLY_NO',    0);
define('REPORT_DATACHECK_COMPLY_YES',   1);
define('REPORT_DATACHECK_COMPLY_DUPS',  2);

define('REPORT_DATACHECK_CHECKBY_USER',-1);

define('REPORT_DATACHECK_APPROVE_NO',   0);
define('REPORT_DATACHECK_APPROVE_YES',  1);
define('REPORT_DATACHECK_APPROVE_ANY',  -1);

define('REPORT_DATACHECK_DOWNLOAD_ALL', 0);





/**
 * Return the type of form field to use for a placeholder, based on its name.
 * @param string $name the placeholder name.
 * @return stdClass object like report_datacheck_compliance_list ones
 */

function report_datacheck_fieldoption_user($fieldoption, $parsing) {
    global $DB;

    $record = new stdClass();
    $record->option = $fieldoption;
    $record->approved = '';
    $record->recordid = '';
    $record->content = '';
    $record->ccontent = '';
    
    $names = array('username', 'idnumber') + get_all_user_name_fields(false, '');
    
    $config = get_config('report_datacheck');
    
    $fields = array('u.id AS userid');
    foreach($names as $name) {
        $fields[] = 'u'.$name;
    }
    
    
    $group = 0;
    $context = '';
    $roles = array();
    switch ($parsing) {
        case 'shortname'    :
            $courseid = $DB->get_field('course', 'id', array('shortname'=>$fieldoption));
            $context = context_course::instance($courseid);
            break;
        case 'fullname'     :
            $courseid = $DB->get_field('course', 'id', array('fullname'=>$fieldoption));
            $context = context_course::instance($courseid);
            break;
        case 'category'     :
            $catid = $DB->get_field('course_categories', 'id', array('name'=>$fieldoption));
            $context = context_coursecat::instance($catid);
            break;
        case 'short-full'   :
            $pieces = explode('-', $fieldoption);
            $courseid = $DB->get_field('course', 'id', array('shortname'=>trim($pieces[0])));
            $context = context_course::instance($courseid);        
            break;
        case 'useridnumber' : 
            $user = $DB->get_record('user', array('idnumber'=>trim(core_text::strtolower($fieldoption))), 'id, '.implode(',', $names) ) ;
            break;
        case 'userfull'     :
            $select = " CONCAT_WS(' ', firstname, lastname) =  :fullname ";
            $user = $DB->get_record_select('user', $select, array('fullname'=>trim(core_text::strtolower($fieldoption))), 'id, '.implode(',', $names) ) ;
            break;
        case 'userfullrev'     :
            $select = " CONCAT_WS(', ', lastname, firstname) =  :fullname ";
            $user = $DB->get_record_select('user', $select, array('fullname'=>trim(core_text::strtolower($fieldoption))), 'id, '.implode(',', $names) ) ;
            break;

        case 'field'        :    
        
            
        default : $user = null;
    }
    
    if($context) {
        if($users = get_role_users($roles, $context, false, array_map(function($v){return 'u.'.$v;}, $names), 
                                        'r.sortorder ASC, u.lastname ASC' , false, $group, 0, 1)) {
            $user = reset($users);
        }
    }
    
    if($user) {
        foreach($names as $key) {
            $record->{$key} = $user->{$key};
            $record->userid = $user->id;
        }
    }
}



/**
 * Return the type of form field to use for a placeholder, based on its name.
 * @param string $name the placeholder name.
 * @return array of sql where statement & params
 */
function report_datacheck_datafield_sql($name, $prefix, $fromform) {

    $where = '';
    $params = array();
    
    $field = $fromform->{$name};
    $operator = $fromform->{$name.'_operator'};
    $value = $fromform->{$name.'_fieldvalue'};
    $dc = $prefix.'dc';
    $content = $prefix.'content';
    
    if($field) {
        //operates on conditional join cdc cdr
        switch ($operator) {
            case 'isempty' : $where = " AND ( $dc.content IS NULL OR $dc.content = '' ) ";
                            break;
            case 'noempty' : $where = " AND $dc.content IS NOT NULL AND $dc.content != '' ";
                            break;
            case 'contain' : $where = ' AND ( '.$DB->sql_like("$dc.content", ":$content", true, true, true).' ) '; //  $dc.content LIKE :$content ) ";
                            $params[$content] = "%{$value}%"; 
                            break;
            case '>' :
            case '<' :
            case '<=' :
            case '>=' :
            case '=' :
            case '<>' :
            case '!=' : $where = " AND ( $dc.content $operator :$content ) ";
                        $params[$content] = $value; 
                            
        }
    }

    return array($where, $params);
}



/**
 * Return the type of form field to use for a placeholder, based on its name.
 * @param string $name the placeholder name.
 * @return array of fields to display in compliance form
 */
function report_datacheck_compliance_list($data, $fromform) {
    global $DB;

    $records = array(); // results array
    $params = array('dataid'=>$data->id, 'checkedfield'=>$fromform->checkedfield);
    
    $approved = '';
    if($fromform->approved != REPORT_DATACHECK_APPROVE_ANY) {
        $approved = " AND cdr.approved = :approved ";
        $params['approved'] =  $fromform->approved; 
    }

    $groupswhere = '';
    if(isset($fromform->groupid) && $fromform->groupid) {
        $groupswhere = ' AND cdr.groupid = :groupid '; 
        $params['groupid'] = $fromform->groupid;
    }
    
    $conditionfieldwhere = '';
    $conditioncontent = '';
    $conditionjoin = '';
    if($approved || $groupswhere || $fromform->datafield) {
        //operates on conditional join cdc cdr
        $conditionjoin = "JOIN {data_records} cdr ON cdr.userid = u.id AND  dr.dataid = :dataidcdr $approved $groupswhere \n";
            $params['dataidcdr'] = $data->id;

        if($fromform->datafield) {
            list($conditionfieldwhere, $inparams) = report_datacheck_datafield_sql('datafield', 'c', $fromform);
            $params = array_merge($params, $inparams);
        
            $conditionjoin .= "JOIN {data_content} cdc ON cdc.recordid = cdr.id AND cdc.fieldid = :datafield $conditionfieldwhere ";
            $params['datafield'] = $fromform->datafield;
            $conditioncontent = ' cdc.content AS conditioncontent, ';
        }
    }
    
    $complyclause = '';
    switch ($fromform->complymode ) {
        case REPORT_DATACHECK_COMPLY_NO : 
                $complyclause = ' AND dr.id IS NULL  ';
                break;
        case REPORT_DATACHECK_COMPLY_YES : 
                $complyclause = ' AND dr.id IS NOT NULL  ';
                break;
        case REPORT_DATACHECK_COMPLY_ANY : 
                $complyclause = " AND dr.id IS NOT NULL  
                                    GROUP BY dr.userid
                                    HAVING COUNT(dr.id) > 1	";
                break;
    }
    
    list($checkedcontentwhere, $inparams) = report_datacheck_datafield_sql('checkedfield', '', $fromform);
    $params = array_merge($params, $inparams);

    $usernames = get_all_user_name_fields(true, 'u');
    
    if($fromform->checkby == REPORT_DATACHECK_CHECKBY_USER) {
        //check by user, get users
        
        $context = context_course::instance($data->course);
        $groupid = 0;
        list($userssql, $userparams) = get_enrolled_sql($context, 'mod/data:writeentry', $groupid, true);
        $params = array_merge($params, $userparams);

        $sql = "SELECT CONCAT(u.id,\"-\",dr.id) AS uid, dr.userid, dr.groupid, dr.approved, dc.recordid, dc.content, $conditioncontent
                    u.id, u.username, u.idnumber, $usernames  
                    FROM {user} u 
                    JOIN ($userssql) je ON je.id = u.id
                    
                    $conditionjoin
                    
                    LEFT JOIN {data_records} dr ON dr.userid = u.id AND  dr.dataid = :dataid_dr
                    LEFT JOIN {data_content} dc ON dc.recordid = dr.id AND dc.fieldid = :ckeckfield_dc

                    WHERE dr.dataid = :dataid AND dc.fieldid = :checkedfield
                    $checkedcontentwhere
                    $complyclause  
                ";
        $params['dataid_dr'] = $data->id;
        $params['ckeckfield_dc'] = $fromform->checkedfield;
        
        $records = $DB->get_records_sql($sql, $params);
        
    } elseif($fromform->checkby > 0) {
        //check by data field options, get them
        $checkoptions = $DB->get_field('data_fields', 'param1', array('id'=>$fromform->checkby));
        $checkoptions = explode("\n", $checkoptions);
        
        $params['checkby'] = $fieldoption;

        $conditionfieldwhere = '';
        $conditioncontent = '';
        $conditionjoin = '';
        if($fromform->datafield) {
            list($conditionfieldwhere, $inparams) = report_datacheck_datafield_sql('datafield', 'c', $fromform);
            $params = array_merge($params, $inparams);
        
            $conditionjoin = "JOIN {data_content} cdc ON cdc.recordid = cdr.id AND cdc.fieldid = :datafield $conditionfieldwhere ";
            $params['datafield'] = $fromform->datafield;
            $conditioncontent = ' cdc.content AS conditioncontent, ';
        }
        
        $records = array(); // results array
	    foreach($checkoptions  as $fieldoption) {
            $params['option'] = $fieldoption;
            
            $sql = "SELECT CONCAT(cdr.userid,\"-\",cdr.id) AS uid, cdr.userid, cdr.groupid, cdr.approved, dc.recordid, 
                            fdc.content AS option, dc.content, $conditioncontent
                            u.username, u.idnumber, $usernames
                    FROM {data_records} cdr 
                    JOIN {user} u ON cdr.userid = u.id
                    
                    $conditionjoin 
                    
                    JOIN {data_content} fdc ON fdc.recordid = cdr.id AND fdc.fieldid = :checkby AND fdc.content = :option
                    LEFT JOIN {data_content} dc ON dc.recordid = cdr.id AND dc.fieldid = :checkedfield_dc
                    
                    WHERE dr.dataid = :dataid AND dc.fieldid = :checkedfield $contentwhere $approved                     
                    ";
            $params['ckeckfield_dc'] = $fromform->checkedfield;        
            $optionrecords = $DB->get_records_sql($sql, $params);
            switch ($formdata->complymode ) {
                case REPORT_DATACHECK_COMPLY_NO : 	
                        if(!$optionrecords) {
                            $user = report_datacheck_fieldoption_user($fieldoption, $fromform->userparsemode);
                            $records["{$user->userid}-"] = $user;
                        }
                        break;
                case REPORT_DATACHECK_COMPLY_YES :	
                        foreach($optionrecords as $key => $record) {
                            $records[$key] = $record;
                        }
                        break;
                case REPORT_DATACHECK_COMPLY_DUPS : 	
                        if(count($optionrecords) > 1) {
                            foreach($optionrecords as $key => $record) {
                                $records[$key] = $record;
                            }
                        }
                        break;					
            }
	    }
    }
    
    return $records;

}

/**
 * Return the type of form field to use for a placeholder, based on its name.
 * @param string $name the placeholder name.
 * @return string field | user fullname 
 */
function report_datacheck_checked_record_text($record, $fromform, $dataid, $courseid) {
    
    $text = '';
    
    if(isset($record->content) &&  $record->content) {
        $text = format_string($record->content);
    } else {
        $text = get_string('isempty', 'report_datacheck');
    }

    if(isset($record->recordid) &&  $record->recordid) {
        $url = new moodle_url('/mod/data/view.php', array('d'=>$dataid,'rid'=> $record->recordid));
        $text = html_writer::link($url, $text);
    }

    if(isset($record->userid) && $record->userid) {
        $record->id = $record->userid;
        $url = new moodle_url('/user/view.php', array('id'=>$record->userid,'course'=> $courseid));
        $name = html_writer::link($url, fullname($record, false, 'lastname')); 
        $text .= '  |  '.$name;
    }
    
    return $text; 
}

/**
 * Sends reminder e-mails for users about compliance with filling in Data activity
 *
 * @param stdClass $course object
 * @param stdClass $data module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string message for success notifications
 */
function report_datacheck_email_to_users($course, $data, $fromform) {
    global $DB, $USER;

    $from = get_string('mailfrom', 'report_datacheck');
    $errorstr= get_string('mailerror', 'report_datacheck');
    $subject = $course->shortname.': '.$fromform->messagesubject;
    $messagetext = $fromform->messagebody;
    $messagehtml = $fromform->messagebody;
    $url = new moodle_url('/mod/data/view.php', array('d'=>$data->id));
    //$messagecontrol = $messagehtml.'<br />'.html_writer::link($url, get_string('inrecord', 'report_datacheck', format_string($data->name))); 
    
    $names = get_all_user_name_fields(false, '');
    $sent = array();
    $errors = 0;
    
    foreach($fromform->records as $record) {
        $flag = '';
        $parts = explode('-', $record);
        if($parts[0]) {
            if($user = $DB->get_record('user', array('id'=>$parts[0]), 'id, email, mailformat, '.implode(', ', $names))) {
                if($parts[1]) {
                    $url->param('rid', $parts[1]);
                }
                $messagehtml .= '<br />'.html_writer::link($url, get_string('inrecord', 'report_datacheck', format_string($data->name))); 
                if($parts[2]) {
                    $messagehtml .= '<br />'.get_string('aboutoption', 'report_datacheck', $record->content);
                }
                $messagetext = html_to_text($messagehtml);
                if(!email_to_user($user, $from, $subject, $messagetext, $messagehtml)) {
                   $flag =  ' - '.$errorstr;
                   $errors++;
                } 
                $sent[] = fullname($user, false, 'lastname').$flag;
            } else {
                $sent[] = $errorstr. '  id: '.$parts[0];
                $errors++;
            }
        }
    }
    
    $info = new stdClass();
    $info->sent = count($sent);
    $info->errors = $errors;
    
    $message = get_string('successemail', 'report_datacheck', $info);
    $messagehtml = '<br />'.$message.'<br />'.implode("\n<br />", $sent); 
    email_to_user($USER, $from, $subject, html_to_text($messagehtml), $messagehtml);
    
    return $message;
}

/**
 * Sets a fixed value in a field on all concerned records in Data activity
 *
 * @param stdClass $course object
 * @param stdClass $data module record on database
 * @param stdClass $fromform object with data from user input form
 * @return string message for success notifications
 */
function report_datacheck_setvalue($course, $data, $fromform) {
    global $DB, $USER;
    
    $records = array();
    foreach($fromform->records as $record) {
        $parts = explode('-', $record);
        if($parts[1]) {
            $records[] = $parts[1];
        }
    }
    
    if($records) {
        list($insql, $params) = $DB->get_in_or_equal($records, SQL_PARAMS_NAMED, 'rec_');
        $select = " fieldid = :fieldid  AND recordid $insql ";
        $params['fieldid'] = $fromform->setfield;
        $DB->set_field_select('data_content', 'content', $fromform->valueset, $select, $params); 
    }

    return get_string('successsetvalue', 'report_datacheck', count($records));
}


/**
 * Collects and download field files as a ZIP
 *
 * @param stdClass $course object
 * @param stdClass $data module record on database
 * @param stdClass $fromform object with data from user input form
 * @return nothing
 */
function report_datacheck_download_files($course, $data, $fromform) {
    global $CFG, $DB;
    
    //  pathfilename includes folder structure
    
    $params = array('dataid' => $data->id, 'fdataid' => $data->id  );
    $downfieldwhere = '';
    if($fromform->downfield) {
        $downfieldwhere = ' AND dc.fieldid = :downfield ';
        $params['downfield'] = $fromform->downfield;
    }
    
    $approved = '';
    if($fromform->approved != REPORT_DATACHECK_APPROVE_ANY) {
        $approved = " AND dr.approved = :approved ";
        $params['approved'] =  $fromform->approved; 
    }


    $conditionjoin = '';
    if($fromform->datafield) {
        $conditionfieldwhere = '';
        list($conditionfieldwhere, $inparams) = report_datacheck_datafield_sql('datafield', 'c', $fromform);
        $params = array_merge($params, $inparams);
    
        $conditionjoin .= "JOIN {data_content} cdc ON cdc.recordid = dr.id AND cdc.fieldid = :datafield $conditionfieldwhere ";
        $params['datafield'] = $fromform->datafield;
    }
    
    $groupswhere = '';
    if(isset($fromform->groupid) && $fromform->groupid) {
        $groupswhere = ' AND dr.groupid = :groupid '; 
        $params['groupid'] = $fromform->groupid;
    }
    
    $groupingfields = '';
    $sortgrouping = '';
    $groupsortjoin = '';
    if($fromform->groupfield) {
        if($fromform->groupfield > 0) {
            $groupsortjoin = 'JOIN {data_content} gdc ON gdc.recordid = dc.recordid AND gdc.fieldid = :groupfield ';
            $params['groupfield'] = $fromform->groupfield;
            $groupingfields = ', gdc.content AS groupfield ';
            $sortgrouping = 'gdc.content ASC, ';
            
        } else {
            $sortgrouping = 'u.lastname ASC, u.firstname ASC, ';
            $groupingfields =  ', u.username, '.get_all_user_name_fields(true, 'u');
        }
    }

    $sql = "SELECT dc.id, dr.id as rid, dr.userid, dc.content, u.idnumber $groupingfields
                FROM {data_content} dc 
                JOIN {data_records} dr ON dr.id = dc.recordid AND dr.dataid = :dataid 
                JOIN {user} u ON u.id = dr.userid
                JOIN {data_fields} df ON df.id = dc.fieldid AND df.dataid = dr.dataid AND (df.type = 'file' OR df.type = 'picture')
                $conditionjoin
                $groupsortjoin

                WHERE df.dataid = :fdataid $downfieldwhere  $groupswhere
                ORDER BY $sortgrouping dc.recordid ASC, dc.content ASC
                ";
    $records =  $DB->get_records_sql($sql, $params);           
    
    $fs = get_file_storage();
    $context = context_module::instance($fromform->id);
    $filesforzipping = array();
    foreach($records as $key => $content) {
        if($content->content) {
            $filename = $content->content;
            $fullpath = "/{$context->id}/mod_data/content/$content->id/$filename";
            $file = $fs->get_file_by_hash(sha1($fullpath));
        
            if ($fromform->groupfield) {
                if ($fromform->groupfield > 0) {
                    // is by a field
                    $prefixedfilename = clean_filename($content->groupfield );
                } else {
                    // is by user
                    $prefixedfilename = clean_filename(fullname($content, false, 'lastname').'('.$content->idnumber.')'); 
                }
                $pathfilename = $prefixedfilename . '/' . $filename;
            
            } else {
                $pathfilename =  $filename;

            }

            if(isset($filesforzipping[$pathfilename])) {
                $parts = pathinfo($pathfilename); 
                $pathfilename = $parts['filename']."({$content->rid}-{$content->id}).".$parts['extension'];
                if($parts['dirname']) {
                    $pathfilename = $parts['dirname'].'/'.$pathfilename;
                }
                
            }
            $filesforzipping[$pathfilename] = $file;
        }
    }

    if (count($filesforzipping) == 0) {

        return get_string('nofiles', 'report_datacheck');
    } else {
        $tempzip = tempnam($CFG->tempdir . '/', 'data_');
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            //\mod_assign\event\all_submissions_downloaded::create_from_assign($this)->trigger();
            // Send file and delete after sending.
            $groupname = '';
            if ($fromform->groupid) {
                $groupname = '-' . groups_get_group_name($fromform->groupid);
            }
            
            $filename = clean_filename($course->shortname . '-' . $data->name . 
                                        $groupname. '.zip');

            if (!headers_sent() && error_get_last()==NULL ) {
                // Trigger a report event.
                $event = \report_datacheck\event\report_download::create(array('context' => $context));
                $event->trigger();

                send_temp_file($tempzip, $filename);
            } else {
                return notice(get_string('errorheaderssent', 'local_trackertools'), $baseurl);
            }
            // We will not get here - send_temp_file calls exit.
        }
    }

}
