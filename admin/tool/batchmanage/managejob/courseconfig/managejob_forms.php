<?php
/**
 * Defines course config form
 *
 * @package    tool_batchmanage
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');


/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_course_config_form extends batchmanageform {
    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $courseconfig = get_config('moodlecourse');
        $course = get_site();
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header','general', get_string('general', 'form'));

        $displaylist = coursecat::make_categories_list('moodle/course:create');
        $element = $mform->createElement('select', 'category', get_string('coursecategory'), $displaylist);
        $this->add_grouped_element($element, 'category');
        
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $element = $mform->createElement('select', 'visible', get_string('visible'), $choices);
        $mform->setDefault('visible', $courseconfig->visible);
        $this->add_grouped_element($element, 'visible');

        if(!$coursestartdate = strtotime(get_config('local_ulpgccore', 'coursestartdate'))) {
            $coursestartdate = $course->startdate;
        } else {
           // $coursestartdate +=86400; // ecastro hack due to one day missing bug
        }
        $element = $mform->createElement('date_selector', 'startdate', get_string('startdate'));
        $mform->setDefault('startdate', $coursestartdate);
        $this->add_grouped_element($element, 'startdate');

        $courseformats = get_plugin_list('format');
        $formcourseformats = array();
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }

//--------------------------------------------------------------------------------
        $mform->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));

        $element = $mform->createElement('select', 'format', get_string('format'), $formcourseformats);
        $mform->setDefault('format', $courseconfig->format);
        $this->add_grouped_element($element, 'format');

        for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
            $sectionmenu[$i] = "$i";
        }
        $element = $mform->createElement('select', 'numsections', get_string('numberweeks'), $sectionmenu);
        $mform->setDefault('numsections', $courseconfig->numsections);
        $this->add_grouped_element($element, 'numsections');

        $choices = array();
        $choices['0'] = get_string('hiddensectionscollapsed');
        $choices['1'] = get_string('hiddensectionsinvisible');
        $element = $mform->createElement('select', 'hiddensections', get_string('hiddensections'), $choices);
        $mform->setDefault('hiddensections', $courseconfig->hiddensections);
        $this->add_grouped_element($element, 'hiddensections');

        $choices = array();
        $choices['0'] = get_string('coursedisplay_single');
        $choices['1'] = get_string('coursedisplay_multi');
        $element = $mform->createElement('select', 'coursedisplay', get_string('coursedisplay'), $choices);
        $mform->setDefault('coursedisplay', $courseconfig->coursedisplay);
        $this->add_grouped_element($element, 'coursedisplay');


//--------------------------------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if (!empty($CFG->allowcoursethemes)) {
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $element = $mform->createElement('select', 'theme', get_string('forcetheme'), $themes);
            $this->add_grouped_element($element, 'theme');
        }

        $languages=array();
        $languages[''] = get_string('forceno');
        $languages += get_string_manager()->get_list_of_translations();
        $element = $mform->createElement('select', 'lang', get_string('forcelanguage'), $languages);
        $mform->setDefault('lang', $courseconfig->lang);
        $this->add_grouped_element($element, 'lang');

        $calendartypes = \core_calendar\type_factory::get_list_of_calendar_types();
        // We do not want to show this option unless there is more than one calendar type to display.
        if (count($calendartypes) > 1) {
            $calendars = array();
            $calendars[''] = get_string('forceno');
            $calendars += $calendartypes;
            $element = $mform->createElement('select', 'calendartype', get_string('forcecalendartype', 'calendar'), $calendars);
            $this->add_grouped_element($element, 'calendartype');
        }

        $element = $mform->createElement('select', 'gmarker', get_string('markthistopic'), $sectionmenu);
        $mform->setDefault('marker', 0);
        $this->add_grouped_element($element, 'marker');

        $options = range(0, 10);
        $element = $mform->createElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
        $mform->setDefault('newsitems', $courseconfig->newsitems);
        $this->add_grouped_element($element, 'newsitems');

        $element = $mform->createElement('selectyesno', 'showgrades', get_string('showgrades'));
        //$mform->addHelpButton('showgrades', 'showgrades');
        $mform->setDefault('showgrades', $courseconfig->showgrades);
        $this->add_grouped_element($element, 'showgrades');

        $element = $mform->createElement('selectyesno', 'showreports', get_string('showreports'));
        //$mform->addHelpButton('showreports', 'showreports');
        $mform->setDefault('showreports', $courseconfig->showreports);
        $this->add_grouped_element($element, 'showreports');

//--------------------------------------------------------------------------------
        $mform->addElement('header', 'filehdr', get_string('filesanduploads'));

        if (!empty($course->legacyfiles) or !empty($CFG->legacyfilesinnewcourses)) {
            if (empty($course->legacyfiles)) {
                //0 or missing means no legacy files ever used in this course - new course or nobody turned on legacy files yet
                $choices = array('0'=>get_string('no'), '2'=>get_string('yes'));
            } else {
                $choices = array('1'=>get_string('no'), '2'=>get_string('yes'));
            }
            $element = $mform->createElement('select', 'legacyfiles', get_string('courselegacyfiles'), $choices);
            //$mform->addHelpButton('legacyfiles', 'courselegacyfiles');
            if (!isset($courseconfig->legacyfiles)) {
                // in case this was not initialised properly due to switching of $CFG->legacyfilesinnewcourses
                $courseconfig->legacyfiles = 0;
            }
            $mform->setDefault('legacyfiles', $courseconfig->legacyfiles);
            $this->add_grouped_element($element, 'legacyfiles');
        }

        $coursemaxbytes = !isset($course->maxbytes) ? null : $course->maxbytes;
        $choices = get_max_upload_sizes($CFG->maxbytes, 0, 0, $coursemaxbytes);
        $element = $mform->createElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        //$mform->addHelpButton('maxbytes', 'maximumupload');
        $mform->setDefault('maxbytes', $courseconfig->maxbytes);
        $this->add_grouped_element($element, 'maxbytes');

//--------------------------------------------------------------------------------
        if (completion_info::is_enabled_for_site()) {
            $mform->addElement('header','completionhdr', get_string('progress','completion'));
            $element = $mform->createElement('selectyesno', 'enablecompletion', get_string('completion','completion'));
            $mform->setDefault('enablecompletion', $courseconfig->enablecompletion);
            $this->add_grouped_element($element, 'enablecompletion');
        }

//--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('groups', 'group'));

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $element = $mform->createElement('select', 'groupmode', get_string('groupmode', 'group'), $choices);
        $mform->setDefault('groupmode', $courseconfig->groupmode);
        $this->add_grouped_element($element, 'groupmode');

        $choices = array();
        $choices['0'] = get_string('no');
        $choices['1'] = get_string('yes');
        $element = $mform->createElement('select', 'groupmodeforce', get_string('groupmodeforce', 'group'), $choices);
        //$mform->addHelpButton('groupmodeforce', 'groupmodeforce', 'group');
        $mform->setDefault('groupmodeforce', $courseconfig->groupmodeforce);
        $this->add_grouped_element($element, 'groupmodeforce');


/// customizable role names in this course
//--------------------------------------------------------------------------------
        $mform->addElement('header','rolerenaming', get_string('rolerenaming'));
        $mform->addHelpButton('rolerenaming', 'rolerenaming');

        if ($roles = get_all_roles()) {
            foreach ($roles as $role) {
                $element = $mform->createElement('text', 'role_'.$role->id, get_string('yourwordforx', '', $role->name));
                if (isset($role->localname)) {
                    $mform->setDefault('role_'.$role->id, $role->localname);
                }
                $mform->setType('role_'.$role->id, PARAM_TEXT);
                $this->add_grouped_element($element, 'role_'.$role->id);
            }
        }

//--------------------------------------------------------------------------------


        $this->add_action_buttons(true, $next);
    }

}




