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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   assignfeedback_historic
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/historic/locallib.php');
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Assignment grading options form
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_historic_batch_copyfrom_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];
        $datatypes = $this->_customdata['datatypes'];


        $mform->addElement('header', 'batchcopyfromforusers', get_string('batchcopyfromforusers', 'assignfeedback_historic',
            count($params['users'])));
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assignfeedback_historic'), $params['usershtml']);

        $data = new stdClass();
        $this->set_data($data);

        $assignments = array(0=>get_string('choose'),
                             -1=>get_string('none'));
        $thisinstanceid = $assignment->get_instance()->id;
        $cms = get_coursemodules_in_course('assign', $assignment->get_course()->id);
        foreach ($cms as $cmo) {
          if($cmo->instance == $thisinstanceid) {
              continue;
          }
          $assignments[$cmo->instance] = format_string($cmo->name);
        }


        foreach($datatypes as $type=>$name) {
            $elementname = "source[$type]";
            $mform->addElement('select', $elementname, $name, $assignments);
            $mform->setType($elementname, PARAM_INT);
            $mform->addRule($elementname, null, 'required', null, 'client');
            $mform->addRule($elementname, null, 'nonzero', null, 'client' );

            $mform->addElement('advcheckbox', "withcomment[$type]", null, get_string('withcomment', 'assignfeedback_historic'));
        }



        $radio = array();
        $radio[] =& $mform->createElement('radio', 'copygrades', null, get_string('all'), 'all');
        $radio[] =& $mform->createElement('radio', 'copygrades', null, get_string('pass', 'assignfeedback_historic'), 'pass');
        $radio[] =& $mform->createElement('radio', 'copygrades', null, get_string('fail', 'assignfeedback_historic'), 'fail');
        $mform->addGroup($radio, 'copygrades', get_string('copygrade', 'assignfeedback_historic'), ' ', false);
        $mform->setDefault('copygrades', 'pass');

        $mform->addElement('advcheckbox', 'override', get_string('override', 'assignfeedback_historic'));
        $mform->setDefault('override', 0);
        $mform->setType('override', PARAM_INT);

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingbatchoperation_historic_copyfrom');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'copyfrom');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'selectedusers', implode(',', $params['users']));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('copyfrom', 'assignfeedback_historic'));

    }

}

/**
 * Assignment grading options form
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_historic_batch_copyto_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];
        $datatypes = $this->_customdata['datatypes'];
        $annualities = $this->_customdata['annualities'];


        $mform->addElement('header', 'batchcopyfromforusers', get_string('batchcopyfromforusers', 'assignfeedback_historic',
            count($params['users'])));
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assignfeedback_historic'), $params['usershtml']);

        $data = new stdClass();
        $this->set_data($data);

        $assignments = array(0=>get_string('choose'),
                             -1=>get_string('none'));
        $thisinstanceid = $assignment->get_instance()->id;
        $cms = get_coursemodules_in_course('assign', $assignment->get_course()->id);
        foreach ($cms as $cmo) {
          if($cmo->instance == $thisinstanceid) {
              continue;
          }
          $assignments[$cmo->instance] = format_string($cmo->name);
        }


        $mform->addElement('select', 'annuality', get_string('annuality', 'assignfeedback_historic'), $annualities);

        foreach($datatypes as $type=>$name) {
            $elementname = "source[$type]";
            $mform->addElement('select', $elementname, $name, $assignments);
            $mform->setType($elementname, PARAM_INT);
            $mform->addRule($elementname, null, 'required', null, 'client');
            $mform->addRule($elementname, null, 'nonzero', null, 'client' );

            $mform->addElement('advcheckbox', "withcomment[$type]", null, get_string('withcomment', 'assignfeedback_historic'));
        }

        $mform->addElement('advcheckbox', 'override', get_string('override', 'assignfeedback_historic'));
        $mform->setDefault('override', 0);
        $mform->setType('override', PARAM_INT);

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingbatchoperation_historic_copyto');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'copyto');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'selectedusers', implode(',', $params['users']));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('copyto', 'assignfeedback_historic'));

    }

}




class assignfeedback_historic_export_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $DB, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];

        // annuality / datatype to export (may be all)
        $sql = "SELECT DISTINCT annuality, annuality AS name
                FROM {assignfeedback_historic_data}
                WHERE 1
                ORDER BY annuality DESC";
        $choices = array(''=>get_string('any')) + $DB->get_records_sql_menu($sql, null);
        $mform->addElement('select', 'annuality', get_string('annuality', 'assignfeedback_historic'), $choices);

        $sql = "SELECT DISTINCT type, name
                FROM {assignfeedback_historic_type}
                WHERE 1
                ORDER BY name ASC";
        $choices = array(''=>get_string('any')) + $DB->get_records_sql_menu($sql, null);
        $mform->addElement('select', 'datatype', get_string('datatype', 'assignfeedback_historic'), $choices);

        // separator & encoding
        // add support for explicit csv alternate formats
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter', get_string('separator', 'grades'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter', 'semicolon');
        } else {
            $mform->setDefault('delimiter', 'comma');
        }

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingoperation_historic_copyfrom');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'export');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('downloadexport', 'assignfeedback_historic'));

    }

}


class assignfeedback_historic_import_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'csv, txt',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'uploadfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('uploadfile', get_string('uploadnofilefound'), 'required', null, 'client');
        if($assignment) {
            $mform->addHelpButton('uploadfile', 'uploadcsvfile', 'assignfeedback_historic');
        } else {
            // re-using form for general update of the Historic. The data format is different
            $mform->addElement('static', 'updatecsvhelp', '', get_string('updatecsvfile', 'assignfeedback_historic'));
        }

        $mform->addElement('selectyesno', 'override', get_string('override', 'assignfeedback_historic'));
        $mform->addHelpButton('override', 'override', 'assignfeedback_historic');
        $mform->setDefault('override', 0);

        // add support for explicit csv alternate formats
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter', get_string('separator', 'grades'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter', 'semicolon');
        } else {
            $mform->setDefault('delimiter', 'comma');
        }

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $choices);
        $mform->setDefault('encoding', 'utf-8');

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingoperation_historic_import');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'do', 'upload');
        $mform->setType('do', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'import');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('import', 'assignfeedback_historic'));

    }

}


class assignfeedback_historic_import_confirm_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $OUTPUT, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];
        $customdata = $this->_customdata;

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $customdata['draftid'], 'id DESC', false)) {
            redirect(new moodle_url('view.php', array('id'=>$assignment->get_course_module()->id,
                                                    'action'=>'grading')));
        }
        $file = reset($files);

        $csvdata = $file->get_content();

        $columns = '';
        if ($csvdata) {
            $csvreader = new csv_import_reader($customdata['importid'], 'assignfeedback_historic_'.$params['cm']);
            $csvreader->load_csv_content($csvdata,  $customdata['encoding'],  $customdata['delimiter']);
            $csvreader->init();
            $columns = $csvreader->get_columns();
        }

        $rows = array();
        if($columns) {
            $index = 0;
            while ($index <= 5 && ($record = $csvreader->next()) ) {
                $rows[] = implode(', ', $record);
                $index += 1 ;
            }

        }

        $mform->addElement('html',  get_string('uploadtableexplain', 'assignfeedback_historic'));
        $mform->addElement('html',  $OUTPUT->box(implode(', ', $columns).'<br />'.implode('<br />', $rows), ' generalbox informationbox centerbox centeredbox' ));
        $mform->addElement('html',  get_string('uploadconfirm', 'assignfeedback_historic'));


        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

        $mform->addElement('hidden', 'importid', $customdata['importid']);
        $mform->setType('importid', PARAM_INT);
        $mform->addElement('hidden', 'draftid', $customdata['draftid']);
        $mform->setType('draftid', PARAM_INT);
        $mform->addElement('hidden', 'override', $customdata['override']);
        $mform->setType('override', PARAM_INT);
        $mform->addElement('hidden', 'encoding', $customdata['encoding']);
        $mform->setType('encoding', PARAM_TEXT);
        $mform->addElement('hidden', 'delimiter', $customdata['delimiter']);
        $mform->setType('delimiter', PARAM_TEXT);

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingoperation_historic_import');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'do', 'upload');
        $mform->setType('do', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'import');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('import', 'assignfeedback_historic'));

    }
}


class assignfeedback_historic_datatype_form extends moodleform {

    function definition() {
        $mform =& $this->_form;
        $item = $this->_customdata['item'];

        $mform->addElement('text', 'type', get_string('datatype', 'assignfeedback_historic'), array('size'=>'30'));
        $mform->setType('type', PARAM_ALPHANUMEXT);
        $mform->addRule('type', null, 'required', null, 'client');
        $mform->addRule('type', get_string('maximumchars', '', 30), 'maxlength', 30, 'client');
        $mform->addHelpButton('type', 'datatype', 'assignfeedback_historic');

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'60'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}


class assignfeedback_historic_setdefault_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $OUTPUT, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];
        $customdata = $this->_customdata;

        $mform->addElement('static', 'confirm', '', get_string('setdefaultconfirm', 'assignfeedback_historic'));

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingoperation_historic_setdefault');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'setdefault');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'historic');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('setdefault', 'assignfeedback_historic'));

    }
}

class assignfeedback_historic_update_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $OUTPUT, $USER;

        $mform = $this->_form;
        $params = $this->_customdata['params'];
        $assignment = $this->_customdata['assignment'];
        $customdata = $this->_customdata;

        $mform->addElement('static', 'info', '', get_string('updatelink_help', 'assignfeedback_historic'));

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);
        $mform->addElement('hidden', 'do', 'update');
        $mform->setType('do', PARAM_ALPHA);
        $this->add_action_buttons(true, get_string('updatelink', 'assignfeedback_historic'));
    }
}


function import_update_historic($data, $baseurl) {
    global $CFG, $DB, $USER;

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->draftid, 'id DESC', false)) {
        redirect($baseurl);
    }
    $file = reset($files);

    $csvdata = $file->get_content();

    $datacolumns = '';
    if ($csvdata) {
        $csvreader = new csv_import_reader($data->importid, 'assignfeedback_historic_');
        $csvreader->load_csv_content($csvdata, $data->encoding, $data->delimiter);
        $csvreader->init();
        $datacolumns = $csvreader->get_columns();
    }

    $columns = array();
    $cols = array('annuality', 'courseidnumber', 'useridnumber', 'datatype','grade','comment');
    foreach($cols as $col) {
        $display = get_string($col, 'assignfeedback_historic');
        $columns[$col] =  $col; // internal name
        $columns[$display] =  $col; // display name
    }

    $cols = array();
    foreach($datacolumns as $key => $col) {
        if(isset($columns[$col])) {
            $cols[$columns[$col]] = $key;
        }
    }

    $requiredfields = array('annuality', 'courseidnumber', 'useridnumber', 'datatype','grade');
    if (!$cols || $error = array_diff($requiredfields, array_keys($cols))) {
        print_error('invaliduploadcsvimport', 'assignfeedback_historic', $baseurl);
        die;
    }

    $config = get_config('assignfeedback_historic');
    list($insql, $params) = $DB->get_in_or_equal(explode(',',$config->datatypes));
    $select = " id $insql ";
    $datatypes = $DB->get_records_select_menu('assignfeedback_historic_type', $select, $params, 'type ASC', 'type, name');

    $imported = 0;
    while ($record = $csvreader->next()) {
        // check if user exists in DB
        $useridnumber = $record[$cols['useridnumber']];
        //if($userid = $DB->get_field('user', 'id', array('idnumber'=>$useridnumber))) {
        $userid = $DB->get_field('user', 'id', array('idnumber'=>$useridnumber));
            $courseidnumber = $record[$cols['courseidnumber']];
            // check if course exists in DB
            //if($courseid = $DB->get_field('course', 'id', array('idnumber'=>$courseidnumber))) {
            $courseid = $DB->get_field('course', 'id', array('shortname'=>$courseidnumber));

                // check if exisng data && updating
                $userupdated = false;
                if($historic = $DB->get_record('assignfeedback_historic_data', array('annuality'=>$record[$cols['annuality']],
                                                                                  'courseidnumber'=>$record[$cols['courseidnumber']],
                                                                                  'useridnumber'=>$record[$cols['useridnumber']],
                                                                                  'datatype'=>$record[$cols['datatype']]))) {
                    // data exists, check if update
                    if($data->override) {
                        $historic->grade = $record[$cols['grade']];
                        $historic->comment = '';
                        $comment = $record[$cols['comment']];
                        if($comment) {
                            $historic->comment = $comment;
                        }
                        if($DB->update_record('assignfeedback_historic_data', $historic)) {
                            $userupdated = true;
                        }
                    }
                } else {
                    // data is new, insert
                    $historic = new stdClass;
                    $historic->annuality = $record[$cols['annuality']];
                    $historic->courseidnumber = $record[$cols['courseidnumber']];
                    $historic->useridnumber = $record[$cols['useridnumber']];
                    $historic->datatype = $record[$cols['datatype']];
                    $historic->grade = $record[$cols['grade']];
                    $historic->comment = $record[$cols['comment']];
                    if($historic->annuality && $historic->courseidnumber && $historic->useridnumber && $historic->datatype) {
                        if($DB->insert_record('assignfeedback_historic_data', $historic)) {
                            $userupdated = true;
                        }
                    }
                }
                if($userupdated) {
                    $imported +=1;
                    // historic changed for some user/course lets update the assignment
                    if($userid && $courseid) {
                        update_user_historic_from_db($userid, $useridnumber, $courseid, $courseidnumber);
                    }
                }
            //}
        //}
    }

    return get_string('numimported', 'assignfeedback_historic', $imported);
}


function update_user_historic_from_db($userid, $useridnumber, $courseid, $shortname, $assignments = array()) {
    global $CFG, $DB, $USER;

    $success = false;
    if(!$assignments) {
        $sql = "SELECT pc.id, pc.assignment
                    FROM {assign_plugin_config} pc
                    JOIN {assign} a ON a.id = pc.assignment
                    WHERE a.course = ? AND pc.plugin ='historic' AND pc.subtype = 'assignfeedback'
                            AND pc.name = 'enabled' AND  pc.value = 1 ";
        $assigments = $DB->get_records_sql_menu($sql, array($courseid));
    }

    if($assignments) {
        foreach($assignments as $aid) {
            list ($course, $cm) = get_course_and_cm_from_instance($aid, 'assign');
            $context = context_module::instance($cm->id);
            $assignment = new assign($context, $cm, $course);
            $grade = $assignment->get_user_grade($userid, true);
            if(!$historic = $DB->get_record('assignfeedback_historic', array('assignment'=>$aid, 'grade'=>$grade->id, 'useridnumber'=>$useridnumber))) {
                $historic = new stdClass;
                $historic->assignment = $aid;
                $historic->grade = $grade->id;
                $historic->useridnumber = $useridnumber;
                $success = $DB->insert_record('assignfeedback_historic', $historic);
            } else {
                $historic->assignment = $aid;
                $historic->grade = $grade->id;
                $historic->useridnumber = $useridnumber;
                $success = $DB->update_record('assignfeedback_historic', $historic);
            }
        }
    }

    return $success;
}

function update_historic_from_db() {
    global $CFG, $DB, $USER;

    $sql = "SELECT u.id AS userid, u.idnumber, c.id AS courseid, c.shortname, c.idnumber AS courseidnumber, a.id AS assignid
                FROM {assignfeedback_historic_data} h
                JOIN {user} u ON h.useridnumber = u.idnumber
                JOIN {course} c ON c.shortname = h.courseidnumber
                JOIN {assign} a ON a.course = c.id
                JOIN {assign_plugin_config} pc ON a.id = pc.assignment AND pc.plugin ='historic' AND pc.subtype = 'assignfeedback'
                                                    AND pc.name = 'enabled' AND  pc.value = 1
                LEFT JOIN {assignfeedback_historic} fh ON fh.assignment = a.id AND fh.useridnumber = h.useridnumber
            WHERE h.grade <> '' AND (fh.id IS NULL  OR fh.id > 0) ";
    $historics = $DB->get_recordset_sql($sql, array());
    $imported = 0;
    if($historics->valid()) {
        foreach($historics as $h) {
            if($success = update_user_historic_from_db($h->userid, $h->idnumber, $h->courseid, $h->shortname, array($h->assignid))) {
                $imported += 1;
            }
        }
        $historics->close();
    }


    return get_string('numupdated', 'assignfeedback_historic', $imported);
}
