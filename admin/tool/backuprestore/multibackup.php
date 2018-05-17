<?php
/**
 * backuprestore tool multibackup utility
 *
 * @package    tool
 * @subpackage backuprestore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->dirroot.'/backup/lib.php');
//require_once($CFG->dirroot.'/backup/backuplib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

// moodleform for controlling the report
class backuprestore_backupfrom_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'backupfromcourses', get_string('backupfromcourses', 'tool_backuprestore'));

        $categories = make_categories_options();
        $catmenu = &$mform->addElement('select', 'categories', get_string('categories'), $categories, 'size="10"');
        $catmenu->setMultiple(true);
        $mform->addRule('categories', null, 'required');

        $mform->addElement('static', 'categorieshelp', '', get_string('categorieshelp', 'tool_backuprestore'));

        $options = array();
        $options['-1'] = get_string('all');
        $options['0'] = get_string('hidden', 'tool_backuprestore');
        $options['1'] = get_string('visible');
        $mform->addElement('select', 'applyvisible', get_string('applyvisible', 'tool_backuprestore'), $options);
        $mform->setDefault('applyvisible', -1);

        if($DB->get_manager()->field_exists('course', 'term')) {
            $options = array();
            $options['-1'] = get_string('all');
            $options['0'] = get_string('term00', 'tool_backuprestore');
            $options['1'] = get_string('term01', 'tool_backuprestore');
            $options['2'] = get_string('term02', 'tool_backuprestore');
            $mform->addElement('select', 'applyterm', get_string('term', 'tool_backuprestore').': ', $options);
            $mform->setDefault('applyterm', -1);
        }

        if($DB->get_manager()->field_exists('course', 'credits')) {
            $options = array();
            $options['-1'] = get_string('all');
            $sql = "SELECT DISTINCT credits
                                FROM {course} WHERE credits IS NOT NULL ORDER BY credits ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->credits}"] = $value->credits;
                }
                $mform->addElement('select', 'applycredit', get_string('credit', 'tool_backuprestore').': ', $options);
                $mform->setDefault('applycredit', -1);
            }
        }

        if($DB->get_manager()->field_exists('course', 'department')) {
            $options = array();
            $options['-1'] = get_string('all');
            $sql = "SELECT DISTINCT department
                                FROM {course} WHERE department IS NOT NULL ORDER BY department ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->department}"] = $value->department;
                }
                $mform->addElement('select', 'applydept', get_string('department', 'tool_backuprestore').': ', $options);
                $mform->setDefault('applydept', -1);
            }
        }

        if($DB->get_manager()->field_exists('course', 'ctype')) {
            $options = array();
            $options['all'] = get_string('all');
            $sql = "SELECT DISTINCT ctype
                                FROM {course} WHERE ctype IS NOT NULL ORDER BY ctype ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->ctype}"] = $value->ctype;
                }
                $mform->addElement('select', 'applyctype', get_string('ctype', 'tool_backuprestore').': ', $options);
                $mform->setDefault('applyctype', 'all');
            }
        }

        $courseformats = get_plugin_list('format');
        $formcourseformats = array('all' => get_string('all'));
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'applytoformat', get_string('format'), $formcourseformats);
        //$mform->setHelpButton('format', array('courseformats', get_string('courseformats')), true);
        $mform->setDefault('applytoformat', 'all');

        $mform->addElement('text', 'applytoshortnames', get_string('applytoshortnames', 'tool_backuprestore'), array('size'=>'40'));
        $mform->setType('applytoshortnames', PARAM_TEXT);
        $mform->setDefault('applytoshortnames', '');
        $mform->addElement('static', 'shortnameshelp', '', get_string('applytoshortnameshelp', 'tool_backuprestore'));

        $mform->addElement('hidden', 'process', 'check');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('multibackupcheck', 'tool_backuprestore'));
    }
}


class backuprestore_backupcheck_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $backup_options = $this->_customdata['backup_options'];
        $info = $this->_customdata['coursesinfo'];
        $courses_settings = $this->_customdata['courses_settings'];
        $categories = $this->_customdata['categories'];

        $courses = array();
        foreach($info as $cid => $cinfo) {
            $courses[$cid] = get_string('coursesincat', 'tool_backuprestore', $cinfo);
        }
        $courses = implode('<br />', $courses);

        $mform->addElement('header', 'multibackupcheck', get_string('multibackupcheck', 'tool_backuprestore'));

        $mform->addElement('static', 'categoriescount', get_string('categories'), count($info));
        $mform->addElement('static', 'coursescount', get_string('courses'), $courses);

        //$mform->addElement('static', 'backupdir', get_string('backupdir', 'tool_backuprestore'), $backup_options['backupdir']);

        foreach($backup_options as $key => $value) {
            $mform->addElement('hidden', $key, $value);
            $mform->setType($key, PARAM_RAW);
            $mform->addElement('static', $key.'_st', get_string($key, 'tool_backuprestore'), $value);
        }

        $mform->addElement('hidden', 'categories', $categories);
        $mform->setType('categories', PARAM_RAW);
        foreach($courses_settings as $key => $value) {
            $mform->addElement('hidden', $key, $value);
            $mform->setType($key, PARAM_RAW);
        }

        $mform->addElement('hidden', 'process', 'proceed');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('backupproceed', 'tool_backuprestore'));
    }
}


////////////////////////////////////////////////////////////////////////////////////////////
@set_time_limit(60*60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);
if (function_exists('apache_child_terminate')) {
    // if we are running from Apache, give httpd a hint that
    // it can recycle the process after it's done. Apache's
    // memory management is truly awful but we can help it.
    @apache_child_terminate();
}


require_login();

admin_externalpage_setup('toolmultibackup');

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
echo $OUTPUT->heading_with_help(get_string('multibackup', 'tool_backuprestore'), 'multibackup', 'tool_backuprestore');

$returnurl = new moodle_url($CFG->wwwroot.'/admin/index.php');


if (($formdata = data_submitted()) && confirm_sesskey()) {
    /// some data, process input
    if(isset($formdata->cancel)) {
        redirect($returnurl, '', 0);
    }

    $status = $formdata->process;

    /// Not cancelled, select courses
    $strcategory = get_string('category');
    $strcourse = get_string('course');
    $info = array();
    $backupcourses = array();

    if(!is_array($formdata->categories)) {
        if(is_string($formdata->categories)) {
            $formdata->categories = explode(',', str_replace(' ', '', $formdata->categories));
        } else {
            $formdata->categories = array();
        }
    }


    foreach($formdata->categories as $categoryid) {
        $category = $DB->get_record('course_categories', array('id' => $categoryid));
        $params = array();
        $select = " category = ? ";
        $params[] = $categoryid;

        if($formdata->applyvisible != -1) {
            $select .= " AND visible = ? ";
            $params[] = $formdata->applyvisible;
        }
/*        
        if(isset($formdata->applyterm) &&  $formdata->applyterm != -1 ) {
            $select .= " AND term = ? ";
            $params[] = $formdata->applyterm;
        }
        if(isset($formdata->applycredit) &&  $formdata->applycredit != -1) {
            $select .= " AND credits = ? ";
            $params[] = $formdata->applycredit;
        }
        if(isset($formdata->applydept) &&  $formdata->applydept != -1) {
            $select .= " AND department = ? ";
            $params[] = $formdata->applydept;
        }

        if($formdata->applyctype != 'all') {
            $select .= " AND ctype = ?  ";
            $params[] = $formdata->applyctype;
        }
        
*/        
        if($formdata->applytoformat != 'all') {
            $select .= " AND format = ? ";
            $params[] = $formdata->applyformat;
        }



        if($formdata->applytoshortnames != '') {
            $names = explode(',' , addslashes($formdata->applytoshortnames));
            foreach($names as $key => $name) {
                $names[$key] = trim($name);
            }
            list($insql, $inparams) = $DB->get_in_or_equal($names);
            $select .= " AND shortname $insql ";
            $params = array_merge($params, $inparams);
        }

        $courses = $DB->get_records_select('course', $select, $params, 'shortname ASC ', ' id, shortname, category ');

        if($status == 'proceed') {
            $backupcourses = array_merge($backupcourses, $courses);
        }
        $cinfo = new StdClass;
        $cinfo->id = $category->id;
        $cinfo->category = format_string($category->name);
        $cinfo->courses = count($courses);
        $info[$category->id] = $cinfo;
    }

    $backup_options = get_config('tool_backuprestore');
    $settings = array(
        'users' => $backup_options->backup_users,
        'role_assignments' => $backup_options->backup_role_assignments,
        'activities' => $backup_options->backup_activities,
        'blocks' => $backup_options->backup_blocks,
        'filters' => $backup_options->backup_filters,
        'comments' => $backup_options->backup_comments,
        'badges' => $backup_options->backup_badges,
        'completion_information' => $backup_options->backup_userscompletion,
        'logs' => $backup_options->backup_logs,
        'histories' => $backup_options->backup_histories,
        'questionbank' => $backup_options->backup_questionbank,
        'groups' => $backup_options->backup_groups,
        'backupdir' => $backup_options->backupdir,
    );

    /// there is confirmation, proceed to do the backup
    if($status == 'proceed') {
        if($backupcourses) {
            $category = 0;
            foreach($backupcourses as $course) {
                if($course->category != $category ) {
                    $category = $course->category;
                    echo $OUTPUT->heading($strcategory.': '.$info[$category]->category.' ('.$info[$category]->courses.') ');
                }
                $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $USER->id);
                $bc->get_logger()->set_next(new output_indented_logger(backup::LOG_INFO, false, true));

                try {
                    foreach ($settings as $setting => $value) {
                        if ($bc->get_plan()->setting_exists($setting)) {
                            $bc->get_plan()->get_setting($setting)->set_value($value);
                        }
                    }

                    // Set the default filename
                    $format = $bc->get_format();
                    $type = $bc->get_type();
                    $id = $bc->get_id();
                    $users = $bc->get_plan()->get_setting('users')->get_value();
                    $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
                    
                    // custom filename with idnumber
                    $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $course->id, $users, $anonymised, false);
                    if($idnumber = $DB->get_field('course', 'idnumber', array('id' => $course->id))) {
                        $shortname = $DB->get_field('course', 'shortname', array('id' => $course->id));
                        $filename = str_replace("-$shortname-", "-$idnumber-", $filename);
                    }
                    $bc->get_plan()->get_setting('filename')->set_value($filename);

                    // ULPGC Jose Luis -- Quitar timestamp del nombre de fichero para no repetir backups de cursos ya procesados
                    $v_filename = explode('-', $filename);
                    array_splice($v_filename,5,2);  // eliminar fecha y hora del nombre
                    $filename = implode('-', $v_filename);

                    if (file_exists($CFG->dataroot.'/'.$backup_options->backupdir.'/'.$filename)) {
                    	echo $OUTPUT->notification('Ya estaba: '.$filename);
                    } else {
                        $bc->set_status(backup::STATUS_AWAITING);
                        $outcome = $bc->execute_plan();
                        $results = $bc->get_results();
                        $file = $results['backup_destination'];
                        $dir = $CFG->dataroot.'/'.$backup_options->backupdir;
                        if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
                            $dir = null;
                        }
                        if (!empty($dir)) {
                        	// ULPGC Jose Luis -- $filename ya estaba calculado de antes
                            // $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $course->id, $users, $anonymised, false);
                            $outcome = $file->copy_content_to($dir.'/'.$filename);
                            $file->delete();
                            $bc->log($strcourse.': '.$course->shortname, backup::LOG_INFO, ' OK');
                        } else {
                           echo $OUTPUT->notification(get_string('backupdirnotwritable', 'tool_backuprestore'));
                           break;
                        }
                    }
                } catch (backup_exception $e) {
                    $error = '  <<< ERROR '.$e->errorcode;
                    $bc->log($strcourse.': '.$course->shortname, backup::LOG_WARNING, $error);
                }
                $bc->destroy();
                unset($bc);
            }
        } else {
            echo $OUTPUT->heading(get_string('nocoursesyet'));
        }
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die;
    } else {
    /// data, no proceed confirmation, then present second form
        unset($mform);
        $courses_settings = array();
        foreach(get_object_vars($formdata) as $key=>$value) {
            if(substr($key, 0, 5) == 'apply') {
                $courses_settings[$key] = $value;
            }
        }

        $mform = new backuprestore_backupcheck_form('multibackup.php', array('coursesinfo' =>$info, 'backup_options'=>$settings, 'courses_settings'=>$courses_settings, 'categories'=> implode(',', $formdata->categories)));
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }
}

/// no data, present the first form
$mform = new backuprestore_backupfrom_form('multibackup.php');
$mform->display();
echo $OUTPUT->footer();
