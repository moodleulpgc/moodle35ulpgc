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
 * Grading method controller for the mcq plugin
 *
 * @package    gradingform_mcq
 * @copyright  2013 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/grading/form/lib.php');

/**
 * This controller encapsulates the mcq grading logic
 *
 * @package    gradingform_mcq
 * @copyright  2013 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_mcq_controller extends gradingform_controller {

    // Modes of displaying the mcq (used in gradingform_mcq_renderer).
    /** mcq display mode: For editing (moderator or teacher creates a mcq) */
    const DISPLAY_EDIT_FULL = 1;

    /** mcq display mode: Preview the mcq design with hidden fields */
    const DISPLAY_EDIT_FROZEN = 2;

    /** mcq display mode: Preview the mcq design (for person with manage permission) */
    const DISPLAY_PREVIEW = 3;

    /** mcq display mode: Preview the mcq (for people being graded) */
    const DISPLAY_PREVIEW_GRADED = 8;

    /** mcq display mode: For evaluation, enabled (teacher grades a student) */
    const DISPLAY_EVAL = 4;

    /** mcq display mode: For evaluation, with hidden fields */
    const DISPLAY_EVAL_FROZEN = 5;

    /** mcq display mode: Teacher reviews filled mcq */
    const DISPLAY_REVIEW = 6;

    /** mcq display mode: Dispaly filled mcq (i.e. students see their grades) */
    const DISPLAY_VIEW = 7;

    /** @var stdClass|false the definition structure */
    public $moduleinstance = false;
    
    public $criteriafields = array('mcqmaxscore', 'choices', 'nonmcqmaxscore');
    
    
    /**
     * Extends the module settings navigation with the mcq grading settings
     *
     * This function is called when the context for the page is an activity module with the
     * FEATURE_ADVANCED_GRADING, the user has the permission moodle/grade:managegradingforms
     * and there is an area with the active grading method set to 'mcq'.
     *
     * @param settings_navigation $settingsnav {@link settings_navigation}
     * @param navigation_node $node {@link navigation_node}
     */
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node = null) {
        $node->add(get_string('definemarkingmcq', 'gradingform_mcq'), $this->get_editor_url(),
                settings_navigation::TYPE_CUSTOM, null, null, new pix_icon('icon', '', 'gradingform_mcq'));
    }

    /**
     * Extends the module navigation
     *
     * This function is called when the context for the page is an activity module with the
     * FEATURE_ADVANCED_GRADING and there is an area with the active grading method set to the given plugin.
     *
     * @param global_navigation $navigation {@link global_navigation}
     * @param navigation_node $node {@link navigation_node}
     * @return void
     */
    public function extend_navigation(global_navigation $navigation, navigation_node $node = null) {
        if (has_capability('moodle/grade:managegradingforms', $this->get_context())) {
            // No need for preview if user can manage forms, he will have link to manage.php in settings instead.
            return;
        }
        if ($this->is_form_defined() && ($options = $this->get_options()) && !empty($options['alwaysshowdefinition'])) {
            $node->add(get_string('gradingof', 'gradingform_mcq',
                    get_grading_manager($this->get_areaid())->get_area_title()),
                    new moodle_url('/grade/grading/form/' . $this->get_method_name() .
                            '/preview.php', array('areaid' => $this->get_areaid())), settings_navigation::TYPE_CUSTOM);
        }
    }

    /**
     * Saves the mcq definition into the database
     *
     * @see parent::update_definition()
     * @param stdClass $newdefinition mcq definition data as coming from gradingform_mcq_editmcq::get_data()
     * @param int $usermodified optional userid of the author of the definition, defaults to the current user
     */
    public function update_definition(stdClass $newdefinition, $usermodified = null) {
    
        $this->update_or_check_mcq($newdefinition, $usermodified, true);
        if (isset($newdefinition->mcq['regrade']) && $newdefinition->mcq['regrade']) {
            $this->mark_for_regrade();
        }
    }

    /**
     * Either saves the mcq definition into the database or check if it has been changed.
     *
     * Returns the level of changes:
     * 0 - no changes
     * 1 - only texts or criteria sortorders are changed, students probably do not require re-grading
     * 2 - added levels but maximum score on mcq is the same, students still may not require re-grading
     * 3 - removed criteria or changed number of points, students require re-grading but may be re-graded automatically
     * 4 - removed levels - students require re-grading and not all students may be re-graded automatically
     * 5 - added criteria - all students require manual re-grading
     *
     * @param stdClass $newdefinition mcq definition data as coming from gradingform_mcq_editmcq::get_data()
     * @param int|null $usermodified optional userid of the author of the definition, defaults to the current user
     * @param bool $doupdate if true actually updates DB, otherwise performs a check
     * @return int
     */
    public function update_or_check_mcq(stdClass $newdefinition, $usermodified = null, $doupdate = false) {
        global $DB;
        
        // Firstly update the common definition data in the {grading_definition} table.
        if ($this->definition === false) {
            if (!$doupdate) {
                // If we create the new definition there is no such thing as re-grading anyway.
                return 5;
            }
            // If definition does not exist yet, create a blank one
            // (we need id to save files embedded in description).
            parent::update_definition(new stdClass(), $usermodified);
            parent::load_definition();
        }
        if (!isset($newdefinition->mcq['options'])) {
            $newdefinition->mcq['options'] = self::get_default_options();
        }
        $newdefinition->options = json_encode($newdefinition->mcq['options']);
        $editoroptions = self::description_form_field_options($this->get_context());
        $newdefinition = file_postupdate_standard_editor($newdefinition, 'description',
                $editoroptions, $this->get_context(), 'grading', 'description', $this->definition->id);

        // Reload the definition from the database.
        $currentdefinition = $this->get_definition(true);

        // Update mcq data.
        $haschanges = array();

        $currentcriteria = $currentdefinition->mcq_criteria;

        $data = array('definitionid' => $this->definition->id);
        if($criterion = $DB->get_record('gradingform_mcq_criteria', $data)) {
            //we are updating
            $data = array();
            foreach($this->criteriafields as $field) {
                if(isset($newdefinition->$field) &&  ($newdefinition->$field != $criterion->$field)) {
                    $data[$field] = $newdefinition->$field;
                }
            }
            if($data) {
                $data['id'] = $criterion->id;
                if ($doupdate) {
                    $DB->update_record('gradingform_mcq_criteria', $data);
                }
                $haschanges[3] = true;            
            }
        } else {
            //we are creating new 
            foreach($this->criteriafields as $field) {
                if(isset($newdefinition->$field)) {
                    $data[$field] = $newdefinition->$field;
                } else {
                    if($field == 'mcqmaxscore') { 
                        $data[$field] = 30;
                    } elseif($field == 'choices') {
                        $data[$field] = 4;
                    } else {
                        $data[$field] = 0;
                    }
                }
            }
            if ($doupdate) {
                $id = $DB->insert_record('gradingform_mcq_criteria', $data);
            }
            $haschanges[5] = true;
        }

        foreach (array('status', 'description', 'descriptionformat', 'name', 'options') as $key) {
            if (isset($newdefinition->$key) && $newdefinition->$key != $this->definition->$key) {
                $haschanges[1] = true;
            }
        }
        if ($usermodified && $usermodified != $this->definition->usermodified) {
            $haschanges[1] = true;
        }
        if (!count($haschanges)) {
            return 0;
        }
        if ($doupdate) {
            parent::update_definition($newdefinition, $usermodified);
            $this->load_definition();
        }
        // Return the maximum level of changes.
        $changelevels = array_keys($haschanges);
        sort($changelevels);
        return array_pop($changelevels);
    }

    /**
     * Marks all instances filled with this mcq with the status INSTANCE_STATUS_NEEDUPDATE
     */
    public function mark_for_regrade() {
        global $DB;
        if ($this->has_active_instances()) {
            $conditions = array('definitionid' => $this->definition->id,
                'status' => gradingform_instance::INSTANCE_STATUS_ACTIVE);
            $DB->set_field('grading_instances', 'status', gradingform_instance::INSTANCE_STATUS_NEEDUPDATE, $conditions);
        }
    }

    /**
     * Loads the mcq form definition if it exists
     *
     * There is a new array called 'mcq_criteria' appended to the list of parent's definition properties.
     */
    protected function load_definition() {
        global $DB;

        // Get definition.
        $definition = $DB->get_record('grading_definitions', array('areaid' => $this->areaid,
            'method' => $this->get_method_name()), '*');
        if (!$definition) {
            // The definition doesn't have to exist. It may be that we are only now creating it.
            $this->definition = false;
            return false;
        }

        $this->definition = $definition;
        // Now get criteria.
        $this->definition->mcq_criteria = array();
        $criteria = $DB->get_recordset('gradingform_mcq_criteria', array('definitionid' => $this->definition->id), 'id');
        foreach ($criteria as $criterion) {
            foreach (array('id', 'mcqmaxscore', 'choices', 'nonmcqmaxscore') as $fieldname) {
                if (strpos($fieldname, 'maxscore')) {  // Strip any trailing 0.
                    $this->definition->mcq_criteria[$criterion->id][$fieldname] = (float) $criterion->{$fieldname};
                } else {
                    $this->definition->mcq_criteria[$criterion->id][$fieldname] = $criterion->{$fieldname};
                }
            }
        }
        $criteria->close();

        // Now get comments.

        if (empty($this->moduleinstance)) { // Only set if empty.
            $modulename = $this->get_component();
            $context = $this->get_context();
            if (strpos($modulename, 'mod_') === 0) {
                $dbman = $DB->get_manager();
                $modulename = substr($modulename, 4);
                if ($dbman->table_exists($modulename)) {
                    $cm = get_coursemodule_from_id($modulename, $context->instanceid);
                    if (!empty($cm)) { // This should only occur when the course is being deleted.
                        $this->moduleinstance = $DB->get_record($modulename, array("id" => $cm->instance));
                    }
                }
            }
        }
    }

    /**
     * Returns the default options for the mcq display
     *
     * @return array
     */
    public static function get_default_options() {
        $options = array(
            'alwaysshowdefinition' => 1,
            'showmarkspercriterionstudents' => 1,
            'showdescriptionstudent' => 0,
        );

        return $options;
    }

    /**
     * Gets the options of this mcq definition, fills the missing options with default values
     *
     * @return array
     */
    public function get_options() {
        $options = self::get_default_options();
        return $options;
    }

    /**
     * Converts the current definition into an object suitable for the editor form's set_data()
     *
     * @return stdClass
     */
    public function get_definition_for_editing() {
        $definition = $this->get_definition();
        $properties = new stdClass();
        $properties->areaid = $this->areaid;
        if (isset($this->moduleinstance->grade)) {
            $properties->modulegrade = $this->moduleinstance->grade;
        }
        if ($definition) {
            foreach (array('id', 'name', 'description', 'descriptionformat', 'status') as $key) {
                $properties->$key = $definition->$key;
            }
            $options = self::description_form_field_options($this->get_context());
            $properties = file_prepare_standard_editor($properties, 'description',
                    $options, $this->get_context(), 'grading', 'description', $definition->id);
        }
        $properties->mcq = array('criteria' => array(), 'options' => $this->get_options());
        if (!empty($definition->mcq_criteria)) {
            $criterion = reset($definition->mcq_criteria);
            foreach($this->criteriafields as $field) {
                $properties->$field = $criterion[$field];
            }
        }

        return $properties;
    }

    /**
     * Returns the form definition suitable for cloning into another area
     *
     * @see parent::get_definition_copy()
     * @param gradingform_controller $target the controller of the new copy
     * @return stdClass definition structure to pass to the target's {@link update_definition()}
     */
    public function get_definition_copy(gradingform_controller $target) {

        $new = parent::get_definition_copy($target);
        $old = $this->get_definition_for_editing();
        $new->description_editor = $old->description_editor;
        $new->mcq = array('criteria' => array(), 'options' => $old->mcq['options']);
        $newcritid = 1;
        foreach ($old->mcq['criteria'] as $oldcritid => $oldcrit) {
            unset($oldcrit['id']);
            $new->mcq['criteria']['NEWID'] = $oldcrit;
            $newcritid++;
        }
        foreach($this->criteriafields as $field) {
            $new->$field = $old->$field;
        }
        $newcomid = 1;

        return $new;
    }

    /**
     * Options for displaying the mcq description field in the form
     *
     * @param context $context
     * @return array options for the form description field
     */
    public static function description_form_field_options($context) {
        global $CFG;
        return array(
            'maxfiles' => -1,
            'maxbytes' => get_max_upload_file_size($CFG->maxbytes),
            'context' => $context,
        );
    }

    /**
     * Formats the definition description for display on page
     *
     * @return string
     */
    public function get_formatted_description() {
        if (!isset($this->definition->description)) {
            return '';
        }
        $context = $this->get_context();

        $options = self::description_form_field_options($this->get_context());
        $description = file_rewrite_pluginfile_urls($this->definition->description,
                'pluginfile.php', $context->id, 'grading', 'description', $this->definition->id, $options);

        $formatoptions = array(
            'noclean' => false,
            'trusted' => false,
            'filter' => true,
            'context' => $context
        );
        return format_text($description, $this->definition->descriptionformat, $formatoptions);
    }

    /**
     * Returns the mcq plugin renderer
     *
     * @param moodle_page $page the target page
     * @return gradingform_mcq_renderer
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('gradingform_' . $this->get_method_name());
    }

    /**
     * Returns the HTML code displaying the preview of the grading form
     *
     * @param moodle_page $page the target page
     * @return string
     */
    public function render_preview(moodle_page $page) {

        if (!$this->is_form_defined()) {
            throw new coding_exception('It is the caller\'s responsibility to make sure that the form is actually defined');
        }
        
        $output = $this->get_renderer($page);
        $criteria = $this->definition->mcq_criteria;
        $options = $this->get_options();
        $mcq = '';
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $showdescription = true;
        } else {
            if (empty($options['alwaysshowdefinition'])) {
                // Ensure we don't display unless show rubric option enabled.
                return '';
            }
            $showdescription = $options['showdescriptionstudent'];
        }
        if ($showdescription) {
            $mcq .= $output->box($this->get_formatted_description(), 'gradingform_mcq-description');
        }
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $mcq .= $output->display_mcq($criteria, $options, self::DISPLAY_PREVIEW, 'mcq');
        } else {
            $mcq .= $output->display_mcq($criteria, $options, self::DISPLAY_PREVIEW_GRADED, 'mcq');
        }

        return $mcq;
    }

    /**
     * Deletes the mcq definition and all the associated information
     */
    protected function delete_plugin_definition() {
        global $DB;

        // Get the list of instances.
        $instances = array_keys($DB->get_records('grading_instances', array('definitionid' => $this->definition->id), '', 'id'));
        // Delete all fillings.
        $DB->delete_records_list('gradingform_mcq_fillings', 'instanceid', $instances);
        // Delete instances.
        $DB->delete_records_list('grading_instances', 'id', $instances);
        // Get the list of criteria records.
        $criteria = array_keys($DB->get_records('gradingform_mcq_criteria',
                array('definitionid' => $this->definition->id), '', 'id'));
        // Delete critera.
        $DB->delete_records_list('gradingform_mcq_criteria', 'id', $criteria);
    }

    /**
     * If instanceid is specified and grading instance exists and it is created by this rater for
     * this item, this instance is returned.
     * If there exists a draft for this raterid+itemid, take this draft (this is the change from parent)
     * Otherwise new instance is created for the specified rater and itemid
     *
     * @param int $instanceid
     * @param int $raterid
     * @param int $itemid
     * @return gradingform_instance
     */
    public function get_or_create_instance($instanceid, $raterid, $itemid) {
        global $DB;
        if ($instanceid &&
                $instance = $DB->get_record('grading_instances', array('id' => $instanceid, 'raterid' => $raterid,
            'itemid' => $itemid), '*', IGNORE_MISSING)) {
            return $this->get_instance($instance);
        }
        if ($itemid && $raterid) {
            if ($rs = $DB->get_records('grading_instances', array('raterid' => $raterid,
                'itemid' => $itemid), 'timemodified DESC', '*', 0, 1)) {
                $record = reset($rs);
                $currentinstance = $this->get_current_instance($raterid, $itemid);
                if ($record->status == gradingform_mcq_instance::INSTANCE_STATUS_INCOMPLETE &&
                        (!$currentinstance || $record->timemodified > $currentinstance->get_data('timemodified'))) {
                    $record->isrestored = true;
                    return $this->get_instance($record);
                }
            }
        }
        return $this->create_instance($raterid, $itemid);
    }

    /**
     * Returns html code to be included in student's feedback.
     *
     * @param moodle_page $page
     * @param int $itemid
     * @param array $gradinginfo result of function grade_get_grades
     * @param string $defaultcontent default string to be returned if no active grading is found
     * @param bool $cangrade whether current user has capability to grade in this context
     * @return string
     */
    public function render_grade($page, $itemid, $gradinginfo, $defaultcontent, $cangrade) {
        return $this->get_renderer($page)->display_instances($this->get_active_instances($itemid), $defaultcontent, $cangrade);
    }

    // Full-text search support.

    /**
     * Prepare the part of the search query to append to the FROM statement
     *
     * @param string $gdid the alias of grading_definitions.id column used by the caller
     * @return string
     */
    public static function qqsql_search_from_tables($gdid) {
        return " LEFT JOIN {gradingform_mcq_criteria} gc ON (gc.definitionid = $gdid)";
    }

    /**
     * Prepare the parts of the SQL WHERE statement to search for the given token
     *
     * The returned array cosists of the list of SQL comparions and the list of
     * respective parameters for the comparisons. The returned chunks will be joined
     * with other conditions using the OR operator.
     *
     * @param string $token token to search for
     * @return array An array containing two more arrays
     *     Array of search SQL fragments
     *     Array of params for the search fragments
     */
    public static function qqsql_search_where($token) {
        global $DB;

        $subsql = array();
        $params = array();

        // Search in mcq criteria description.
        /*
        $subsql[] = $DB->sql_like('gc.description', '?', false, false);
        $params[] = '%' . $DB->sql_like_escape($token) . '%';
        */

        return array($subsql, $params);
    }

}

/**
 * Manage one mcq grading instance. Performs actions like update,copy,validate, subit etc
 *
 * @package    gradingform_mcq
 * @copyright  2012 Dan Marsden <dan@danmarsden.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_mcq_instance extends gradingform_instance {

    /** @var array */
    protected $mcq;

    /** @var array An array of validation errors */
    protected $validationerrors = array();

    /**
     * Deletes this (INCOMPLETE) instance from database.
     */
    public function cancel() {
        global $DB;
        parent::cancel();
        $DB->delete_records('gradingform_mcq_fillings', array('instanceid' => $this->get_id()));
    }

    /**
     * Duplicates the instance before editing (optionally substitutes raterid and/or itemid with
     * the specified values)
     *
     * @param int $raterid value for raterid in the duplicate
     * @param int $itemid value for itemid in the duplicate
     * @return int id of the new instance
     */
    public function copy($raterid, $itemid) {
        global $DB;
        $instanceid = parent::copy($raterid, $itemid);
        $currentgrade = $this->get_mcq_filling();
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            $params = array('instanceid' => $instanceid, 'criterionid' => $criterionid,
                'score' => $record['score'], 'mcqscore' => $record['mcqscore'],
                'mcqfails' => $record['mcqfails'], 'nonmcqscore' => $record['nonmcqscore']);
            $DB->insert_record('gradingform_mcq_fillings', $params);
        }
        return $instanceid;
    }

    /**
     * Validates that mcq is fully completed and contains valid grade on each criterion
     *
     * @param array $elementvalue value of element as came in form submit
     * @return boolean true if the form data is validated and contains no errors
     */
    public function validate_grading_element($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->mcq_criteria;
        if (!isset($elementvalue['criteria']) || !is_array($elementvalue['criteria']) ||
                count($elementvalue['criteria']) < count($criteria)) {
            return false;
        }
        // Reset validation errors.
        $this->validationerrors = null;
        foreach ($criteria as $id => $criterion) {
            if (!isset($elementvalue['criteria'][$id]['mcqscore']) ||
                    !is_numeric($elementvalue['criteria'][$id]['mcqscore']) ||
                    $elementvalue['criteria'][$id]['mcqscore'] < 0 ||
                    $elementvalue['criteria'][$id]['mcqscore'] > $criterion['mcqmaxscore'] ) {
                $this->validationerrors[$id]['mcqscore'] = $elementvalue['criteria'][$id]['mcqscore'];
            }

            if (!isset($elementvalue['criteria'][$id]['mcqfails']) ||
                    !is_numeric($elementvalue['criteria'][$id]['mcqfails']) ||
                    $elementvalue['criteria'][$id]['mcqfails'] < 0 ||
                    $elementvalue['criteria'][$id]['mcqfails'] > $criterion['mcqmaxscore'] ) {
                $this->validationerrors[$id]['mcqfails'] = $elementvalue['criteria'][$id]['mcqfails'];
            }
            
            if(isset($elementvalue['criteria'][$id]['mcqscore']) &&
                    is_numeric($elementvalue['criteria'][$id]['mcqscore']) &&
                    isset($elementvalue['criteria'][$id]['mcqfails']) &&
                    is_numeric($elementvalue['criteria'][$id]['mcqfails']) &&
                    (($elementvalue['criteria'][$id]['mcqscore'] + $elementvalue['criteria'][$id]['mcqfails']) > $criterion['mcqmaxscore'])) {
                $this->validationerrors[$id]['mcqfails'] = $elementvalue['criteria'][$id]['mcqfails'];
            }

            if ($criterion['nonmcqmaxscore'] > 0 &&
                    (!isset($elementvalue['criteria'][$id]['nonmcqscore']) ||
                    !is_numeric($elementvalue['criteria'][$id]['nonmcqscore']) ||
                    $elementvalue['criteria'][$id]['nonmcqscore'] < 0 ||
                    $elementvalue['criteria'][$id]['nonmcqscore'] > $criterion['nonmcqmaxscore'])) {
                $this->validationerrors[$id]['nonmcqscore'] = $elementvalue['criteria'][$id]['nonmcqscore'];
            }
            
            
        }
        if (!empty($this->validationerrors)) {
            return false;
        }
        return true;
    }

    /**
     * Retrieves from DB and returns the data how this mcq was filled
     *
     * @param bool $force whether to force DB query even if the data is cached
     * @return array
     */
    public function get_mcq_filling($force = false) {
        global $DB;
        if ($this->mcq === null || $force) {
            $records = $DB->get_records('gradingform_mcq_fillings', array('instanceid' => $this->get_id()));
            $this->mcq = array('criteria' => array());
            foreach ($records as $record) {
                $level = $DB->get_records('gradingform_mcq_criteria', array('id' => $record->criterionid));
                $record->score = (float) $record->score; // Strip trailing 0.
                $this->mcq['criteria'][$record->criterionid] = (array) $record;
                $this->mcq['criteria'][$record->criterionid]['level'] = (array)$level[$record->criterionid];
            }
        }
        return $this->mcq;
    }

    /**
     * Updates the instance with the data received from grading form. This function may be
     * called via AJAX when grading is not yet completed, so it does not change the
     * status of the instance.
     *
     * @param array $data
     */
    public function update($data) {
        global $DB;
        $currentgrade = $this->get_mcq_filling();
        parent::update($data);

        foreach ($data['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $currentgrade['criteria'])) {
                $newrecord = array('instanceid' => $this->get_id(), 'criterionid' => $criterionid,
                    'score' => 0, // $record['score'], 
                    'mcqscore' =>  $record['mcqscore'], 
                    'mcqfails' =>  $record['mcqfails'], 
                    'nonmcqscore' =>  isset($record['nonmcqscore']) ? $record['nonmcqscore'] : 0, 
                    );

                $DB->insert_record('gradingform_mcq_fillings', $newrecord);
            } else {
                $newrecord = array('id' => $currentgrade['criteria'][$criterionid]['id']);

                foreach (array('score', 'mcqscore', 'mcqfails', 'nonmcqscore' ) as $key) {
                    if (isset($record[$key]) && $currentgrade['criteria'][$criterionid][$key] != $record[$key]) {
                        $newrecord[$key] = $record[$key];
                    }
                }
                if (count($newrecord) > 1) {
                    $DB->update_record('gradingform_mcq_fillings', $newrecord);
                }
            }
        }
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $data['criteria'])) {
                $DB->delete_records('gradingform_mcq_fillings', array('id' => $record['id']));
            }
        }
        $this->get_mcq_filling(true);
    }

    /**
     *
     * This is called from outside mcq grading so
     * it calls calculate_mcq_grade to allow for the
     * creation of unit tests
     *
     * @return int
     */
    public function get_grade() {
        $grade = $this->get_mcq_filling();
        return $this->calculate_mcq_grade($grade);
    }

    /**
     * Works out the overall grade
     *
     * X initialises the level to assume it is not present.
     * X is checked later on to see if the level should be
     * ignored for not existing. Then the letters are
     * walked through to be set to P M or D if they do exist
     *
     * @param array $grade
     * @return int
     */
    public function calculate_mcq_grade(array $grade) {
        global $CFG;
        
        $score = 0.0;
        
        // some reordering shortcuts 
        $grade = reset($grade['criteria']);
        $level = $grade['level'];
        
        $modinstance = $this->get_controller()->moduleinstance;
        if(isset($modinstance->grade)) {
            $maxgrade = $modinstance->grade;
        } elseif(isset($modinstance->scale)) {
            $maxgrade = $modinstance->scale;
        } else {
            $maxgrade = $CFG->gradepointdefault;
        }
    
        $maxscore = $level['mcqmaxscore'] + $level['nonmcqmaxscore'];
        
        $score = $grade['mcqscore'] - $grade['mcqfails'] / ($level['choices'] - 1 ) + $grade['nonmcqscore'];
        $score = $score * $maxgrade / $maxscore;
        
        return $score;
    }

    /**
     * Returns html for form element of type 'grading'.
     *
     * @param moodle_page $page
     * @param MoodleQuickForm_grading $gradingformelement
     * @return string
     */
    public function render_grading_element($page, $gradingformelement) {
        if (!$gradingformelement->_flagFrozen) {
            $mode = gradingform_mcq_controller::DISPLAY_EVAL;
        } else {
            if ($gradingformelement->_persistantFreeze) {
                $mode = gradingform_mcq_controller::DISPLAY_EVAL_FROZEN;
            } else {
                $mode = gradingform_mcq_controller::DISPLAY_REVIEW;
            }
        }
        
        $definition = $this->get_controller()->get_definition();
        $criteria = $definition->mcq_criteria;
        foreach($criteria as $key => $criterion) {
            $criteria[$key]['name'] = $definition->name;
            $criteria[$key]['description'] = $definition->description;
        }
        $options = $this->get_controller()->get_options();
        $options = array(); // this method has no options;
        $value = $gradingformelement->getValue();
        $html = '';
        if ($value === null) {
            $value = $this->get_mcq_filling();
        } else if (!$this->validate_grading_element($value)) {
            if (!empty($this->validationerrors)) {
                foreach ($this->validationerrors as $id => $errors) {
                    foreach($errors as $error => $val) {
                        $a = new stdClass();
                        $a->criterianame = get_string($error, 'gradingform_mcq');
                        $a->score = $val;
                        $a->maxscore = (strpos($error, 'mcq') === 0) ? $criteria[$id]['mcqmaxscore'] : $criteria[$id]['nonmcqmaxscore'];
                        $errorstr = ($val > 0) ? 'err_scoreinvalid' : 'err_scoreisnegative';
                        if(($error == 'mcqfails') && $val && ($val <= $a->maxscore)) {
                            $errorstr = 'err_failsinvalid';
                        }
                        $html .= html_writer::tag('div', get_string($errorstr, 'gradingform_mcq', $a),
                                array('class' => 'gradingform_mcq-error'));
                    }
                }
            }
        }
        
        $currentinstance = $this->get_current_instance();
        if ($currentinstance && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            $html .= html_writer::tag('div', get_string('needregrademessage', 'gradingform_mcq'),
                    array('class' => 'gradingform_mcq-regrade'));
        }
        $haschanges = false;
        
        /*
        if ($currentinstance) {
            $curfilling = $currentinstance->get_mcq_filling();
            foreach ($curfilling['criteria'] as $criterionid => $curvalues) {
                $value['criteria'][$criterionid]['score'] = $curvalues['score'];
                $value['criteria'][$criterionid]['mcqscore'] = $curvalues['mcqscore'];
                $value['criteria'][$criterionid]['nonmcqscore'] = $curvalues['nonmcqscore'];
                $value['criteria'][$criterionid]['mcqfails'] = $curvalues['mcqfails'];
                $newscore = null;
                $newmcqscore = null;
                $newnonmcqscore = null;
                $newmcqfails = null;
                if (isset($value['criteria'][$criterionid]['score'])) {
                    $newscore = $value['criteria'][$criterionid]['score'];
                }
                if (isset($value['criteria'][$criterionid]['mcqscore'])) {
                    $newmcqscore = $value['criteria'][$criterionid]['mcqscore'];
                }
                if (isset($value['criteria'][$criterionid]['nonmcqscore'])) {
                    $newnonmcqscore = $value['criteria'][$criterionid]['nonmcqscore'];
                }
                if (isset($value['criteria'][$criterionid]['mcqfails'])) {
                    $newmcqfails = $value['criteria'][$criterionid]['mcqfails'];
                }

                if ($newscore != $curvalues['score'] ||
                    $newmcqscore != $curvalues['mcqscore'] ||
                    $newnonmcqscore != $curvalues['nonmcqscore'] ||
                    $newmcqfails != $curvalues['mcqfails']) {
                    $haschanges = true;
                }
            }
        }
        */
        
        if ($this->get_data('isrestored') && $haschanges) {
            $html .= html_writer::tag('div', get_string('restoredfromdraft', 'gradingform_mcq'),
                    array('class' => 'gradingform_mcq-restored'));
        }
        
        if($mode != gradingform_mcq_controller::DISPLAY_EVAL) {
            $html .= html_writer::tag('div', $this->get_controller()->get_formatted_description(),
                    array('class' => 'gradingform_mcq-description'));
        }
        $html .= $this->get_controller()->get_renderer($page)->display_mcq($criteria, 
                $options, $mode, $gradingformelement->getName(), $value, $this->validationerrors);
                
        return $html;
    }

}
