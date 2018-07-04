<?php
/**
 * backuprestore tool multirestore utility
 *
 * @package    tool
 * @subpackage backuprestore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


include_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/backup/util/interfaces/checksumable.class.php');
require_once($CFG->dirroot.'/backup/backup.class.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');


// moodleform for controlling the report

class backuprestore_restoresource_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $mform->addElement('header', 'multirestoresettings', get_string('multirestoresettings', 'tool_backuprestore'));

        $mform->addElement('text', 'includefiles', get_string('includefiles', 'tool_backuprestore'), array('size'=>'20'));
        $mform->setType('includefiles', PARAM_TEXT);
        $mform->setDefault('includefiles', '*.mbz');
        $mform->addElement('static', 'includefileshelp', '', get_string('includefiles_help', 'tool_backuprestore'));

        $mform->addElement('text', 'excludefiles', get_string('excludefiles', 'tool_backuprestore'), array('size'=>'20'));
        $mform->setType('excludefiles', PARAM_TEXT);
        $mform->setDefault('excludefiles', '');
        $mform->addElement('static', 'excludefileshelp', '', get_string('excludefiles_help', 'tool_backuprestore'));

        $mform->addElement('text', 'maxfilesize', get_string('maxfilesize', 'tool_backuprestore'), array('size'=>'10'));
        $mform->setType('maxfilesize', PARAM_INT);
        $mform->setDefault('maxfilesize', 500);
        $mform->addElement('static', 'maxfilesizehelp', '', get_string('maxfilesize_help', 'tool_backuprestore'));

        $mform->addElement('text', 'filenamereplace', get_string('filenamereplace', 'tool_backuprestore'), array('size'=>'40'));
        $mform->setType('filenamereplace', PARAM_TEXT);
        $mform->setDefault('filenamereplace', '');
        $mform->addElement('static', 'filenamereplacehelp', '', get_string('filenamereplace_help', 'tool_backuprestore'));

        $this->add_action_buttons(true, get_string('restorecheck', 'tool_backuprestore'));
    }
}

class backuprestore_restorecheck_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $restore_options = $this->_customdata['restore_options'];
        $dirinfo = $this->_customdata['dirdata'];
        $duplicates = $this->_customdata['duplicates'];

        $mform->addElement('header', 'multirestorecheck', get_string('multirestorecheck', 'tool_backuprestore'));

        $mform->addElement('static', 'dirinfo', get_string('dirinfo', 'tool_backuprestore'), get_string('dirinformation', 'tool_backuprestore', $dirinfo));

        $mform->addElement('static', 'dirinfodups', get_string('dirinfoduplicates', 'tool_backuprestore'), $duplicates);

        foreach($restore_options as $key => $value) {
            $mform->addElement('hidden', $key, $value);
            $mform->setType($key, PARAM_RAW);
            $mform->addElement('static', $key.'_st', get_string($key, 'tool_backuprestore'), $value);
        }

        $mform->addElement('checkbox', 'restoretesting', get_string('restoretesting', 'tool_backuprestore'), ' '.get_string('restoretesting_help', 'tool_backuprestore'));
        $mform->setType('restoretesting', PARAM_BOOL);
        $mform->setDefault('restoretesting', true);

        $mform->addElement('hidden', 'restorechecked', 1);
        $mform->setType('restorechecked', PARAM_INT);

        $this->add_action_buttons(true, get_string('restoreproceed', 'tool_backuprestore'));
    }
}

function backuprestore_extract_idnumber($filename) {

    if($p = strpos($filename, '.zip')) {
        // es un zip, backup antiguo
        $idnumber = substr(strstr($filename,'-'), 1);
        $idnumber = strstr($idnumber,'-', 1);
    } elseif($p = strpos($filename, '.mbz')) {
        // es un mbz, backup nuevo
        $idnumber = substr(strstr($filename,'-course-'), 8);
        $p = strpos($idnumber, '-');
        $idnumber = substr($idnumber, $p+1);
        $p = strpos($idnumber, '-');
        $idnumber = substr($idnumber, 0, $p);



        /*
        if($z = strpos('-an.mbz', $filename) or $z = strpos('-nu.mbz', $filename)) {
            $idnumber = substr($filename, 0,$p-3);
        } else {
            $idnumber = substr($filename, 0,$p);
        }
        $idnumber = strstr($idnumber,'-course-');
        $idnumber = substr($idnumber, 8);
        //$idnumber = substr($idnumber,0, -14); //  ending in '-yyyymmdd-hhmm.zip'
        //fito@ulpgc date not included in backup name
        $idnumber = substr($idnumber,0, -3);
        */
    }
    return $idnumber;
}

////////////////////////////////////////////////////////////////////////////////////////////
@set_time_limit(60*60*12); // 12 hours should be enough
raise_memory_limit(MEMORY_HUGE);  //     @raise_memory_limit('512M');
if (function_exists('apache_child_terminate')) {
    // if we are running from Apache, give httpd a hint that
    // it can recycle the process after it's done. Apache's
    // memory management is truly awful but we can help it.
    @apache_child_terminate();
}

require_login();
admin_externalpage_setup('toolmultirestore');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

if (!$site = get_site()) {
    print_error("Could not find site-level course");
}

if (!$adminuser = get_admin()) {
    print_error("Could not find site admin");
}

/// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('multirestore', 'tool_backuprestore'), 'multirestore', 'tool_backuprestore');

$returnurl = new moodle_url($CFG->wwwroot.'/admin/index.php');

$strcourse = get_string('course');

if (($formdata = data_submitted()) && confirm_sesskey()) {
    /// formdata is set,  some data, process input

    $restore_options = array('includefiles'=>trim($formdata->includefiles), 'excludefiles'=>trim($formdata->excludefiles),
                        'maxfilesize'=>trim($formdata->maxfilesize), 'filenamereplace'=>trim($formdata->filenamereplace));
    $testing = false;
    if(isset($formdata->restoretesting)) {
        $testing = true;
    }
    if(isset($formdata->restorechecked) and $testing) {
        $returnurl = new moodle_url('multirestore.php', $restore_options);
    }
    if(isset($formdata->cancel)) {
        redirect($returnurl);
    }

    /// set common util data
    $sourcedir = /* $CFG->dataroot.'/'. */trim(get_config('tool_backuprestore', 'restoredir'));
    $includepattern = $restore_options['includefiles'];
    $excludepattern = $restore_options['excludefiles'];
    $maxfilesize = 1024*1024*(int)$restore_options['maxfilesize'];
    $searchreplace = $restore_options['filenamereplace'];
    $fnsearch = '';
    $fnreplace = '';
    if($p = strpos($searchreplace, '//')) {
        $fnsearch = trim(strstr($searchreplace, '//', true));
        $fnreplace = trim(substr($searchreplace, $p+2));
    }

    /// there is confirmation, proceed to do the restore
    if(isset($formdata->restorechecked) and $formdata->restorechecked) {

        $included = array();
        $excluded = array();
        $toobig   = array();
        $nonmatch = array();
        $restored = array();
        $notfound = array();
        $errors   = array();
        $duplicates = array();
        $restored_idnumbers = array();
        $log = array();

        if(!$testing) {
            make_writable_directory($sourcedir.'/restored');
            make_writable_directory($sourcedir.'/duplicates');
            check_dir_exists($sourcedir.'/restored');
            check_dir_exists($sourcedir.'/duplicates');
            //check_dir_exists($sourcedir.'/notfound');
            //check_dir_exists($sourcedir.'/excluded');
            //check_dir_exists($sourcedir.'/toobig');
            check_dir_exists($CFG->dataroot.'/temp/backup');
        }

        $restore_settings = get_config('tool_backuprestore');

        /// search backup files and iteratively process them
        $localfiles = get_directory_list($sourcedir, '', false, false, true);
        foreach($localfiles as $file) {
            if(fnmatch($includepattern, $file)) {
                if(fnmatch($excludepattern, $file)) {
                    $excluded[] = $file;
                    $message = get_string('fileexluding', 'tool_backuprestore', $file);
                    $log[] = $message;
                    echo $message;
                    if(!$testing) {
                        //rename($sourcedir.'/'.$file, $sourcedir.'/excluded/'.$file);
                    }
                    continue;
                }
                if(filesize($sourcedir.'/'.$file) > $maxfilesize) {
                    $toobig[] = $file;
                    $message = get_string('filetoobig', 'tool_backuprestore', $file);
                    $log[] = $message;
                    echo $OUTPUT->notification($message. 'info');
                    if(!$testing) {
                        //rename($sourcedir.'/'.$file, $sourcedir.'/toobig/'.$file);
                    }
                    continue;
                }
            /// something to do with this file
                $idnumber = backuprestore_extract_idnumber($file);
                if($fnsearch) {
                    $idnumber = str_replace($fnsearch, $fnreplace, $idnumber);
                }
                if(in_array($idnumber, $restored)) {
                    $duplicates[] = $file;
                    $message = get_string('fileduplicated', 'tool_backuprestore', $file);
                    $log[] = $message;
                    echo $OUTPUT->notification($message, 'info');
                    continue;
                }

                if($restore_settings->restore_target == backup::TARGET_NEW_COURSE) {
                   $courseid = restore_dbops::create_new_course('fullname', 'shortname', 2);
                   $course = new StdClass;
                   $course->id = $courseid;
                   $courses = array($course);
                } else {
                    // EXISTING, ADDING or DELETING
                    $courses = array();
                    if($idnumber) {
                        $courses = $DB->get_records('course', array('idnumber'=>$idnumber));
                    }
                    if (!$courses) {
                    	$idnumberparts = explode('_', $idnumber);
                        $courseshortname = isset($idnumberparts[5]) ? $idnumberparts[5] : '';
                        if($courseshortname) {
                            $courses = $DB->get_records('course', array('shortname'=>$courseshortname));
                        }
                    }
                }
                if($courses) {
                    foreach($courses as $course) {
                        $success = ' testing';
                        if(isset($course->shortname)) {
                            $coursename = $course->shortname;
                        } else {
                            $coursename = 'ID-'.$course->id;
                        }
                        $message = $coursename;
                        if(!$testing) {
                            /// NOW we are about to restore someting
                            $now = time();
                            $tempdir = 'backuprestore-'.strstr($file, '.', 1).$now;
//                             $fs = get_file_storage();
//                             $file_record = array('contextid'=>$context->id, 'component'=>'tool_backuprestore', 'filearea'=>'tempfiles',
//                                         'itemid'=>0, 'filepath'=>'/', 'filename'=> $tempdir ,'timecreated'=>$now, 'timemodified'=>$now);
//                             $moodlefile = $fs->create_file_from_pathname($file_record, $sourcedir.'/'.$file);
//                             $moodlefile->extract_to_pathname(get_file_packer(), $CFG->dataroot.'/temp/backup/'.$tempdir);

                            $fb = get_file_packer('application/vnd.moodle.backup');
                            $result = $fb->extract_to_pathname($sourcedir.'/'.$file,
                                    $CFG->dataroot.'/temp/backup/'.$tempdir);

//                             $fs->delete_area_files($context->id, 'tool_backuprestore', 'tempfiles');
//                             unset($moodlefile);
//                             unset($fs);
//
                            $controller = new restore_controller($tempdir, $course->id,
                                    backup::INTERACTIVE_NO, $restore_settings->restore_target, $USER->id,
                                    $restore_settings->restore_target);
                            try {
                                $controller->get_logger()->set_next(new output_indented_logger(backup::LOG_INFO, false, true));
                                $controller->execute_precheck();
                                $plan = $controller->get_plan();
                                $plan->get_setting('users')->set_value($restore_settings->restore_users);
                                $plan->get_setting('role_assignments')->set_value($restore_settings->restore_role_assignments);
                                $plan->get_setting('activities')->set_value($restore_settings->restore_activities);
                                $plan->get_setting('blocks')->set_value($restore_settings->restore_blocks);
                                $plan->get_setting('filters')->set_value($restore_settings->restore_filters);
                                $plan->get_setting('comments')->set_value($restore_settings->restore_comments);
                                $plan->get_setting('badges')->set_value($restore_settings->restore_badges);
                                $plan->get_setting('userscompletion')->set_value($restore_settings->restore_userscompletion);
                                $plan->get_setting('logs')->set_value($restore_settings->restore_logs);
                                $plan->get_setting('grade_histories')->set_value($restore_settings->restore_histories);
                                //$plan->get_setting('questionbank')->set_value($restore_settings->restore_questionbank);
                                $plan->get_setting('groups')->set_value($restore_settings->restore_groups);
                                $plan->get_setting('adminmods')->set_value($restore_settings->restore_adminmods);
                                
                                if($restore_settings->restore_target == backup::TARGET_EXISTING_DELETING) {
                                    $plan->get_setting('overwrite_conf')->set_value($restore_settings->restore_overwriteconf);
                                    $options = array('keep_roles_and_enrolments'=>$restore_settings->restore_keeproles ,'keep_groups_and_groupings'=>$restore_settings->restore_keeproles);
                                    restore_dbops::delete_course_content($controller->get_courseid(), $options);
                                }

                                $controller->execute_plan();
                                $controller->log($strcourse.': '.$coursename, backup::LOG_INFO, ' OK');
                                if(!$testing) {
                                    rename($sourcedir.'/'.$file, $sourcedir.'/restored/'.$file);
                                }
                                
                                $controller->destroy();
                                unset($controller);

                            } catch (backup_exception $e) {
                                $error = '  <<< ERROR '.$e->errorcode;
                                $controller->log($strcourse.': '.$coursename, backup::LOG_WARNING, $error);
                            }
                        }
                        /*
                        if(!$error) {
                            $restored[] = $file;
                            $message = get_string('filerestored', 'tool_backuprestore', $file);
                            if(!$testing) {
                                rename($sourcedir.'/'.$file, $sourcedir.'/restored/'.$file);
                                remove_dir($CFG->dataroot.'/temp/backup/'.$tempdir);
                            }
                        } else {
                            $message = get_string('filenotrestored', 'tool_backuprestore', $file).$error;
                        }*/
                        $log[] = $message;
                        //echo $OUTPUT->notification($message, 'info');
                    }
                } else {
                    $notfound[] = $file;
                    $message = get_string('filenotfound', 'tool_backuprestore', $file);
                    $log[] = $message;
                    echo $OUTPUT->notification($message, 'info');
                    if(!$testing) {
                        //rename($sourcedir.'/'.$file, $sourcedir.'/notfound/'.$file);
                    }
                }
            } else {
                $nonmatch[] = $file;
                $message = get_string('filenonmatch', 'tool_backuprestore', $file);
                $log[] = $message;
                echo $OUTPUT->notification($message, 'info');
                if(!$testing) {
                    //rename($sourcedir.'/'.$file, $sourcedir.'/nonmatch/'.$file);
                }
            }
        }
        if($testing) {
            echo $OUTPUT->single_button($returnurl, get_string('restorenottesting', 'tool_backuprestore'));
        }
        echo $OUTPUT->continue_button('multirestore.php');
        die;
    } else {
    /// data, no proceed confirmation, then present second form
        unset($mform);

        $included = 0;
        $excluded = 0;
        $nonmatch = 0;
        $toobig   = 0;
        $duplicates = array();

        $localfiles = get_directory_list($sourcedir, '', false, false, true);
        foreach($localfiles as $file) {
            if(fnmatch($includepattern, $file)) {
                $included +=1;
                if(fnmatch($excludepattern, $file)) {
                    $excluded +=1;
                }
                if(filesize($sourcedir.'/'.$file) > $maxfilesize) {
                    $toobig +=1;
                }
                $idnumber = backuprestore_extract_idnumber($file);
                $backups = glob($sourcedir.'/*-'.$idnumber.'-*');
                if(count($backups) > 1) {
                    $duplicates[] = $file;
                }
            } else {
                $nonmatch += 1;
            }
        }

        $dirinfo = new StdClass;
        $dirinfo->sourcedir = $sourcedir;
        $dirinfo->included = $included;
        $dirinfo->excluded = $excluded;
        $dirinfo->toobig = $toobig;
        $dirinfo->nonmatch = $nonmatch;
        $dirinfo->total = count($localfiles);
        $mform = new backuprestore_restorecheck_form('multirestore.php', array('restore_options' =>$restore_options, 'dirdata'=>$dirinfo, 'duplicates'=>implode('<br />', $duplicates) ));
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }
}

/// no data, present the first form
$mform = new backuprestore_restoresource_form('multirestore.php');
$mform->display();
echo $OUTPUT->footer();


