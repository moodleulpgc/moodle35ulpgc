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
class batchmanage_extension_config_form extends batchmanageform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header', 'toolassigntfdueextensionsettings', get_string('settings', 'managejob_assigndueextension'));

        $mform->addElement('text', 'modgradecatname', get_string('gradecatname', 'managejob_assigndueextension'), array('size'=>'38'));
        $mform->setType('modgradecatname', PARAM_TEXT);
        $mform->setDefault('modgradecatname', '');
        $mform->addRule('modgradecatname', null, 'required');

        $mform->addElement('static', 'gradecatnameshelp', '', get_string('gradecatnamehelp', 'managejob_assigndueextension'));

        $sections = $DB->get_record_sql("SELECT MAX(section) FROM {course_sections}");
        $maxsections = reset($sections);
        unset($sections);
        $sections = array(-1 =>get_string('any'));
        for ($i = 0; $i <= $maxsections; $i++) {
            $sections[$i] = $i;
        }

        $mform->addElement('select', 'modinsection', get_string('insection', 'managejob_assigndueextension'), $sections);
        $mform->setDefault('modinsection', -1);

        $mform->addElement('selectyesno', 'modonlydmin', get_string('onlyadmin', 'managejob_assigndueextension'));
        $mform->setDefault('modonlyadmin', 0);

        $timevalue = strtotime("+1 week");
        $mform->addElement('date_time_selector', 'moddatetimevalue', get_string('timevalue', 'assignfeedback_copyset'));
        $mform->setDefault('moddatetimevalue', $timevalue);
        $mform->addHelpButton('moddatetimevalue', 'timevalue', 'assignfeedback_copyset');


        $this->add_action_buttons(true, $next);
    }

}




