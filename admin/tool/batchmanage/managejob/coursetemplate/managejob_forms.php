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
class batchmanage_course_template_form extends batchmanageform {
    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $coursetemplate = get_config('moodlecourse');
        $course = get_site();
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header', 'applytemplatesettings', get_string('applytemplatesource', 'managejob_coursetemplate'));
/*        $mform->addElement('filepicker', 'templatefiles', get_string('template', 'managejob_coursetemplate'),
                                    null, array('subdirs' =>0, 'maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'accepted_types' => array('*.mbz', '*.zip', 'moodle')));*/
        $mform->addElement('filepicker', 'restoretemplatefile', get_string('template', 'managejob_coursetemplate'), null, array('maxbytes'=>$CFG->maxbytes, 'accepted_types' => array('.mbz', '.zip')));

        $mform->addRule('restoretemplatefile', null, 'required');

        $mform->addElement('header', 'applytemplatesettings', get_string('applytemplatesettings', 'managejob_coursetemplate'));


        $mform->addElement('advcheckbox', 'restorenullmodinfo', get_string('restorenullmodinfo', 'managejob_coursetemplate'));
        $mform->setDefault('restorenullmodinfo', 0);

        $mform->addElement('checkbox', 'restoreusers', get_string('restoreusers', 'managejob_coursetemplate'));
        $mform->setDefault('restoreusers', 0);
        $mform->disabledIf('restoreusers', 'restoreusers', 'notchecked');

        $mform->addElement('checkbox', 'restoreemptyfirst', get_string('deletingolddata'));
        $mform->setDefault('restoreemptyfirst', 1);

        $mform->addElement('checkbox', 'restorekeepgroups', get_string('restorekeepgroups', 'managejob_coursetemplate'));
        $mform->setDefault('restorekeepgroups', 0);
        $mform->disabledIf('restorekeepgroups', 'restoreemptyfirst', 'notchecked');

        $mform->addElement('checkbox', 'restorekeeproles', get_string('restorekeeproles', 'managejob_coursetemplate'));
        $mform->setDefault('restoreadminmods', 0);
        $mform->disabledIf('restorekeeproles', 'restoreemptyfirst', 'notchecked');

        $mform->addElement('checkbox', 'restoregroups', get_string('restoregroups', 'managejob_coursetemplate'));
        $mform->setDefault('restoregroups', 0);

        $mform->addElement('checkbox', 'restoreblocks', get_string('restoreblocks', 'managejob_coursetemplate'));
        $mform->setDefault('restoreblocks', 0);

        $mform->addElement('checkbox', 'restorefilters', get_string('restorefilters', 'managejob_coursetemplate'));
        $mform->setDefault('restorefilters', 0);

        $mform->addElement('checkbox', 'restoreadminmods', get_string('restoreadminmods', 'managejob_coursetemplate'));
        $mform->setDefault('restoreadminmods', 0);

        $this->add_action_buttons(true, $next);
    }

}




