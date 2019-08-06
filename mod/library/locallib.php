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
 * Private library module utility functions
 *
 * @package    mod_library
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/library/lib.php");


/**
 * Get the parameters that may be appended to URL
 * @param object $config library module config options
 * @return array array describing opt groups
 */
function library_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'library'));

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
        'catidnumber'  => get_string('idnumbercat', 'library'),
    );
    
    $options[get_string('modulename', 'library')] = array(
        'modinstance'     => 'id',
        'modcmid'         => 'cmid',
        'modname'         => get_string('name'),
        'modidnumber'     => get_string('idnumbermod'),
        'activitygroup'   => get_string('group'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl'       => get_string('serverurl', 'library'),
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
 * @param object $library module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function library_get_variable_values($library, $cm, $course, $config) {
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
        'modinstance'     => $library->id,
        'modcmid'         => $cm->id,
        'modname'         => format_string($library->name),
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
        $values['encryptedcode'] = library_get_encrypted_parameter($library, $config);
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
 * @param object $library module instance
 * @param object $cm
 * @param object $course
 * @return array of parameter values
 */
function library_parameter_value_mapping($library, $cm, $course) {
    global $USER, $CFG;
    
    $parameters = array();
    
    if($variables = empty($library->parameters) ? array() : unserialize($library->parameters)) { 
        $config = get_config('library');
        $parvalues = library_get_variable_values($library, $cm, $course, $config);
        foreach($variables as $name => $param) {
            $paramenters[$config->separator.$name.$config->separator] = $parvalues[$param];
        }
    }
    
    return $paramenters;
}

/**
 * 
 * @param object $library
 * @return plugin librarysource class subtype
 */
function library_get_source_plugin($library) {
    global $CFG;
    include_once($CFG->dirroot.'/mod/library/source/'.$library->source.'/source.php');
    $classname = 'librarysource_'.$library->source;
    
    return new $classname($library);
}


/**
 * 
 * @param object $library
 * @return plugin repository
 */
function library_get_repository($library) {
    global $CFG;

    require_once($CFG->dirroot . '/repository/lib.php');
    //$onlyvisible = (strpos($allowhidden, $library->source) === false) ? true : false;
    $onlyvisible = false;
    
    $instances = repository::get_instances(array('onlyvisible'=>$onlyvisible, 'type'=>$library->source));
    foreach($instances as $repository) {
        if($repository->get_name() == $library->reponame) {
            return $repository;
        }
    }
    
    return false;
}


/**
 * Gets details of the file to cache in course cache to be displayed using {@link library_get_optional_details()}
 *
 * @param object $library Resource table row (only property 'displayoptions' is used here)
 * @param object $cm Course-module table row
 * @return string Size and type or empty string if show options are not enabled
 */
function library_get_file_details($library, $cm) {
    $options = empty($library->displayoptions) ? array() : @unserialize($library->displayoptions);
    $filedetails = array();
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_library', 'content', 0, 'sortorder DESC, id ASC', false);
        // For a typical file library, the sortorder is 1 for the main file
        // and 0 for all other files. This sort approach is used just in case
        // there are situations where the file has a different sort order.
        $mainfile = $files ? reset($files) : null;
        if (!empty($options['showsize'])) {
            $filedetails['size'] = 0;
            foreach ($files as $file) {
                // This will also synchronize the file size for external files if needed.
                $filedetails['size'] += $file->get_filesize();
                if ($file->get_repository_id()) {
                    // If file is a reference the 'size' attribute can not be cached.
                    $filedetails['isref'] = true;
                }
            }
        }
        if (!empty($options['showtype'])) {
            if ($mainfile) {
                $filedetails['type'] = get_mimetype_description($mainfile);
                // Only show type if it is not unknown.
                if ($filedetails['type'] === get_mimetype_description('document/unknown')) {
                    $filedetails['type'] = '';
                }
            } else {
                $filedetails['type'] = '';
            }
        }
        if (!empty($options['showdate'])) {
            if ($mainfile) {
                // Modified date may be up to several minutes later than uploaded date just because
                // teacher did not submit the form promptly. Give teacher up to 5 minutes to do it.
                if ($mainfile->get_timemodified() > $mainfile->get_timecreated() + 5 * MINSECS) {
                    $filedetails['modifieddate'] = $mainfile->get_timemodified();
                } else {
                    $filedetails['uploadeddate'] = $mainfile->get_timecreated();
                }
                if ($mainfile->get_repository_id()) {
                    // If main file is a reference the 'date' attribute can not be cached.
                    $filedetails['isref'] = true;
                }
            } else {
                $filedetails['uploadeddate'] = '';
            }
        }
    }
    return $filedetails;
}

/**
 * Gets optional details for a library, depending on library settings.
 *
 * Result may include the file size and type if those settings are chosen,
 * or blank if none.
 *
 * @param object $library Resource table row (only property 'displayoptions' is used here)
 * @param object $cm Course-module table row
 * @return string Size and type or empty string if show options are not enabled
 */
function library_get_optional_details($library, $cm) {
    global $DB;

    $details = '';

    $options = empty($library->displayoptions) ? array() : @unserialize($library->displayoptions);
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        if (!array_key_exists('filedetails', $options)) {
            $filedetails = library_get_file_details($library, $cm);
        } else {
            $filedetails = $options['filedetails'];
        }
        $size = '';
        $type = '';
        $date = '';
        $langstring = '';
        $infodisplayed = 0;
        if (!empty($options['showsize'])) {
            if (!empty($filedetails['size'])) {
                $size = display_size($filedetails['size']);
                $langstring .= 'size';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showtype'])) {
            if (!empty($filedetails['type'])) {
                $type = $filedetails['type'];
                $langstring .= 'type';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showdate']) && (!empty($filedetails['modifieddate']) || !empty($filedetails['uploadeddate']))) {
            if (!empty($filedetails['modifieddate'])) {
                $date = get_string('modifieddate', 'mod_library', userdate($filedetails['modifieddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            } else if (!empty($filedetails['uploadeddate'])) {
                $date = get_string('uploadeddate', 'mod_library', userdate($filedetails['uploadeddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            }
            $langstring .= 'date';
            $infodisplayed += 1;
        }

        if ($infodisplayed > 1) {
            $details = get_string("librarydetails_{$langstring}", 'library',
                    (object)array('size' => $size, 'type' => $type, 'date' => $date));
        } else {
            // Only one of size, type and date is set, so just append.
            $details = $size . $type . $date;
        }
    }

    return $details;
}

/**
 * Decide the best display format.
 * @param object $library
 * @return int display type constant
 */
function library_get_final_display_type($library) {
    global $CFG, $PAGE;

    if ($library->display != RESOURCELIB_DISPLAY_AUTO) {
        return $library->display;
    }

    if (empty($library->mainfile)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    } else {
        $mimetype = mimeinfo('type', $library->mainfile);
    }

    if (file_mimetype_in_typegroup($mimetype, 'archive')) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (file_mimetype_in_typegroup($mimetype, array('web_image', '.htm', 'web_video', 'web_audio'))) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}



/**
 * File browsing support class
 */
class library_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}
