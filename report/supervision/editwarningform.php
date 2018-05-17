<?php  // $Id: post_form.php,v 1.21.2.6 2008-07-18 13:18:28 ericmerrill Exp $

require_once($CFG->libdir.'/formslib.php');

class supervision_editwarning_form extends moodleform {

    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $hiddens = $this->_customdata['params'];
        $edit = $this->_customdata['edit'];


        $mform->addElement('header', 'general', '');//fill in the data depending on page params
                                                    //later using set_data
        $mform->addElement('htmleditor', 'comment', get_string('commentprompt', 'report_supervision'), array('cols'=>70, 'rows'=>12));
        $mform->setType('comment', PARAM_CLEANHTML );


        $options[0]  = get_string('fixno', 'report_supervision');
        $options[1]  = get_string('fixyes', 'report_supervision');
        $options[-1] = get_string('fixnull', 'report_supervision');

        $mform->addElement('select', 'fixnow', get_string('fixnow', 'report_supervision'), $options);

        foreach($hiddens as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_RAW);
        }
        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_RAW);
        $mform->addElement('hidden', 'wid', $edit);
        $mform->setType('wid', PARAM_INT);


        $this->add_action_buttons(true);

    }

}
?>
