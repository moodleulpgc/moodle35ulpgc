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
 * Private videolib module utility functions
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/videolib/lib.php");


/**
 * Get the parameters that may be appended to URL
 * @param object $config videolib module config options
 * @return array array describing opt groups
 */
function videolib_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'videolib'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('category')] = array(
        'category'     => 'id',
        'catname'      => get_string('name'),
        'catidnumber'  => get_string('idnumbercat', 'videolib'),
    );
    
    $options[get_string('modulename', 'videolib')] = array(
        'modinstance'     => 'id',
        'modcmid'         => 'cmid',
        'modname'         => get_string('name'),
        'modidnumber'     => get_string('idnumbermod'),
        'activitygroup'   => get_string('group'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl'       => get_string('serverurl', 'videolib'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone1'),
        'userphone2'      => get_string('phone2'),
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl'         => get_string('webpage'),
    );

    if($ulpgc = get_config('local_ulpgccore')) {
        $options[get_string('course')]['department'] = get_string('department', 'local_ulpgccore');
        $options[get_string('course')]['term'] = get_string('term', 'local_ulpgccore');
    
        $options[get_string('category')]['faculty'] = get_string('faculty', 'local_ulpgccore');
        $options[get_string('category')]['degree'] = get_string('degree', 'local_ulpgccore');
    }
    
    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to URL
 * @param object $videolib module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function videolib_get_variable_values($videolib, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);
    $category = coursecat::get($course->category);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverurl'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'modinstance'     => $videolib->id,
        'modcmid'         => $cm->id,
        'modname'         => format_string($videolib->name),
        'modidnumber'     => $cm->idnumber,
        'category'        => $course->category,
        'catname'         => $category->name,
        'catidnumber'     => $category->idnumber,
    );
    
    if($ulpgc = get_config('local_ulpgccore')) {   
        require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
        $course = local_ulpgccore_get_course_details($course);
        $categoryrec = local_ulpgccore_get_category_details($category->get_db_record());
    
        $values['department'] = $course->department;
        $values['term'] = $course->term;
    
        $values['faculty'] = $categoryrec->faculty;
        $values['degree'] = $categoryrec->degree;
    }
    
    $values['activitygroup'] = groups_get_activity_group($cm); 
    
    
    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userurl']         = $USER->url;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = videolib_get_encrypted_parameter($videolib, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}


/**
 * Get the parameter values that may be substituted in searchpattern
 * @param object $videolib module instance
 * @param object $cm
 * @param object $course
 * @return array of parameter values
 */
function videolib_parameter_value_mapping($videolib, $cm, $course) {
    global $USER, $CFG;
    
    $parameters = array();
    
    if($variables = empty($videolib->parameters) ? array() : unserialize($videolib->parameters)) { 
        $config = get_config('videolib');
        $parvalues = videolib_get_variable_values($videolib, $cm, $course, $config);
        foreach($variables as $name => $param) {
            $paramenters[$config->separator.$name.$config->separator] = $parvalues[$param];
        }
    }
    
    return $paramenters;
}

/**
 * 
 * @param object $videolib
 * @param object $config
 * @return string
 */
function videolib_get_encrypted_parameter($videolib, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($videolib, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * 
 * @param object $videolib
 * @return plugin videolibsource class subtype
 */
function videolib_get_source_plugin($videolib) {
    global $CFG;
    include_once($CFG->dirroot.'/mod/videolib/source/'.$videolib->source.'/source.php');
    $classname = 'videolibsource_'.$videolib->source;
    
    return new $classname($videolib);
}


/**
 * 
 * @param object $fromform 
 * @return string errro message
 */
function videolib_export_mapping($fromform) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/dataformatlib.php');
    
    $message = '';
    
    $filename = clean_filename($fromform->filename);
    
    $columns = array_keys($DB->get_columns('videolib_source_mapping'));
    $columns = array_combine($columns, $columns);
    
    list($where, $params) = videolib_sql_search_mapping($fromform);
    /*
    $params = array();
    $select = '1';
    if($fromform->source) {
        $select .= ' AND source = :source';
        $params['source'] = $fromform->source;
    }
    if($fromform->annuality) {
        $select .= ' AND '.$DB->sql_like('annuality', ':annuality');
        $params['annuality'] = $fromform->annuality;
    }
    if($fromform->videolibkey) {
        $select .= ' AND '.$DB->sql_like('videolibkey', ':videolibkey');
        $params['videolibkey'] = $fromform->videolibkey;
    }*/
    if(!$where) {
        $where = 1;
    }
    
   
    $rs_entries = $DB->get_recordset_select('videolib_source_mapping', $where, $params, 'id'); 
    if($rs_entries->valid() && $columns) {
        if (!headers_sent() && error_get_last()==NULL ) {
            download_as_dataformat($filename, $fromform->dataformat, $columns, $rs_entries);
            
        } else {
            $message = get_string('errorheaderssent', 'videolib');
        }
    }
    $rs_entries->close();

    return $message;
}

function videolib_get_mapping_table($cmid, $filter = array(), $perpage = 50) {
    global $CFG, $DB, $OUTPUT;

    if((int)$perpage < 10) {
        $perpage = 10;
    }
    
    $params = array('id'=>$cmid, 'a' => 'view', 'p'=>$perpage);
    foreach($filter as $field => $value) { 
        $params[substr($field,0,2)] = $value;
    }
    $manageurl = new moodle_url('/mod/videolib/manage.php', $params);
    $viewurl = new moodle_url('/mod/videolib/view.php');

    $table = new flexible_table('videolib-manage-edit-'.$cmid);
    
    $tablecolumns = array('id', 'videolibkey', 'annuality', 'source', 'remoteid',
                            'cmids', 'timemodified', 'action');
    $tableheaders = array(get_string('rownum', 'videolib'),
                            get_string('videolibkey', 'videolib'),
                            get_string('annuality', 'videolib'),
                            get_string('source', 'videolib'),
                            get_string('remoteid', 'videolib'),
                            get_string('mapping', 'videolib'),
                            get_string('datechanged'),
                            get_string('action'),
                            );
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($manageurl->out(false));
    $table->sortable(true, 'videolibkey', SORT_ASC);
    $table->no_sorting('action');

    $table->set_attribute('id', 'videolib_sources_mapping');
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('class', 'flexible generaltable videolibsourcestable');

    $table->setup();
/*
    $params = array();
    $where = 1;
    if(isset($filter->source) && $filter->source) {
        $where[] = ' AND source = :source';
        $params['source'] = $filter->source;
    }
    if(isset($filter->annuality) && $filter->annuality) {
        $where[] = ' AND '.$DB->sql_like('annuality', ':annuality');
        $params['annuality'] = $annuality;
    }
    if(isset($filter->videolibkey) && $filter->videolibkey) {
        $where[] = ' AND '.$DB->sql_like('videolibkey', ':videolibkey');
        $params['videolibkey'] = $videolibkey;
    }
    */
    list($where, $params) = videolib_sql_search_mapping($filter);
    
    //print_object($where);
    //print_object($params);
    

    $totalcount = $DB->count_records_select('videolib_source_mapping', $where, $params);

    $table->initialbars(false);
    $table->pagesize($perpage, $totalcount);

    if ($table->get_sql_sort()) {
        $sort = $table->get_sql_sort();
    } else {
        $sort = ' videolibkey ASC, annuality DESC ';
    }

    $stredit   = get_string('edit');
    $strdelete = get_string('delete');

    if($elements = $DB->get_records_select('videolib_source_mapping', $where, $params, $sort, '*', $table->get_page_start(), $table->get_page_size())) {
        foreach($elements as $element) {
            $data = array();
            $data[] = $element->id;
            $data[] = $element->videolibkey;
            $data[] = $element->annuality;
            $data[] = $element->source;
            $data[] = $element->remoteid;
            if($cmids = explode(',', $element->cmids)) {
                foreach($cmids as $k => $value) {
                    $viewurl->param('ìd', $value); 
                    $cmids[$k] = html_writer::link($viewurl, $value);
                }
                $element->cmids = implode(',',$cmids);
            }
            $data[] = $element->cmids;
            $data[] = userdate($element->timemodified, get_string('strftimedatetimeshort'));
            
            $action = '';
            if (!$table->is_downloading()) {
                $buttons = array();
                $url = new moodle_url($manageurl, array('a'=>'update', 'item'=>$element->id));
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit)));
                $url = new moodle_url($manageurl, array('a'=>'del', 'item'=>$element->id));
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
                $action = implode('&nbsp;&nbsp;', $buttons);
            }
            $data[] = $action;

            $table->add_data($data);
        }
    }
    
    $table->finish_output();
}

/**
 * 
 * @param object $fromform 
 * @return void
 */
function videolib_saveupdate_mapping($fromform) {
    global $DB;
    
    $success = false;
    
    $data = new stdClass();
    $data->videolibkey = $fromform->videolibkey;
    $data->annuality = $fromform->annuality;
    $data->source = $fromform->source;
    if(isset($fromform->itemid) && $fromform->itemid) {
        $params['id'] = $fromform->itemid;
        $action = 'entryupdated';
    } else {
        $params = get_object_vars($data);
        $action = 'entryadded';
    }
    $data->remoteid = $fromform->remoteid;
    $data->timemodified = time();
    
    if($rec = $DB->get_record('videolib_source_mapping', $params)) {
        // we are updating
        $data->id = $rec->id;
        if($DB->update_record('videolib_source_mapping', $data)) {
            $success = true;;
        }
    } else {
        //we are adding
        if($newid = $DB->insert_record('videolib_source_mapping', $data)) {
            $success = true;
        }
    }
    
    if($success) {
        \core\notification::add(get_string($action, 'videolib'), \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core\notification::add(get_string('dberror', 'videolib'), \core\output\notification::NOTIFY_ERROR);
    }
}


/**
 * 
 * @param object $filter object with search terms
 * @return void
 */
function videolib_sql_search_mapping($filter) {
    global $DB;
    
    $params = array();
    $where = array();
    if(isset($filter->itemid) && $filter->itemid) {
        $where[] = 'id = :itemid';
        $params['itemid'] = $filter->itemid;
    }
    if(isset($filter->source) && $filter->source) {
        $where[] = 'source = :source';
        $params['source'] = $filter->source;
    }
    if(isset($filter->annuality) && $filter->annuality) {
        $where[] = $DB->sql_like('annuality', ':annuality');
        $params['annuality'] = $filter->annuality;
    }
    if(isset($filter->videolibkey) && $filter->videolibkey) {
        $where[] = $DB->sql_like('videolibkey', ':videolibkey');
        $params['videolibkey'] = $filter->videolibkey;
    }
    if(isset($filter->remoteid) && $filter->remoteid) {
        $where[] = $DB->sql_like('remoteid', ':remoteid');
        $params['remoteid'] = $filter->remoteid;
    }
    
    if($where) {
        $where = implode(' AND ', $where);
    } else {
        $where = '';
    }
    
    return array($where, $params);
}

/**
 * 
 * @param object $fromform 
 * @return void
 */
function videolib_delete_mapping($fromform) {
    global $DB;
    
    $success = false;
    foreach($fromform as $key => $value) {
        if(strpos($key, '_confirmed')) {
            $key = strstr($key, '_confirmed', true); 
            $fromform->$key = $value;
        } 
    }
    
    if(isset($fromform->itemid) && $fromform->itemid) {
        $count = 1;
    }
    if(isset($fromform->count) && $fromform->count) {
        $count = $fromform->count;
    }
    
    list($where, $params) = videolib_sql_search_mapping($fromform);
    
    //print_object($where);
    //print_object($params);
    if(!$where) { 
        $where = 0;
    }
    
    if($DB->delete_records_select('videolib_source_mapping', $where, $params)) {
        \core\notification::add(get_string('mappingdeleted', 'videolib', $count), \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core\notification::add(get_string('dberror', 'videolib'), \core\output\notification::NOTIFY_ERROR);
    }

}


/**
 * 
 * @param object $fromform 
 * @return void
 */
function videolib_import_mapping($csvreader, $fromform) {
    global $CFG, $DB;
    
    $recordsadded = 0;
    
    $columns = $csvreader->get_columns();
    $now = time();
    
    $keys = array('source', 'annuality', 'videolibkey');
    
    $csvreader->init();
    while ($record = $csvreader->next()) {
    
        $record = array_combine($columns, $record);
    
        $filter = new stdClass();
        foreach($keys as $key) {
            $filter->{$key} = $record[$key];
        }
        
        list($where, $params) = videolib_sql_search_mapping($filter);
        
        print_object($record);
        print_object($params);
        
        if(!array_diff($keys, array_keys($params))) {
            // all mandatory keys are present
            $record['timemodified'] = $now;
            if($rec = $DB->get_record('videolib_source_mapping', $params)) {
                // The records class_exists
                if($fromform->updateonimport) {
                    $record['id'] = $rec->id;
                    //if(1) {
                    if($DB->update_record('videolib_source_mapping', $record)) {
                        $success = true;
                        $recordsadded++;
                        print_object("update");
                    }
                }
            } else {
                //we are adding
                //if(1) {
                if($newid = $DB->insert_record('videolib_source_mapping', $record)) {
                    $success = true;
                    $recordsadded++;
                    print_object("adding");
                }
            }
        }
    }
    
}

