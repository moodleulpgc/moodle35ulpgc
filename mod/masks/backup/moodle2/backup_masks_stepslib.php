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
 * MASKS module standard backup / restore implementation
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_masks_activity_task
 */

/**
 * Define the complete masks structure for backup, with file and id annotations
 */
class backup_masks_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // The masks module stores user info.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $masks = new backup_nested_element('masks', array('id'), array('name'));

        // docs
        $docs = new backup_nested_element('docs');

        $doc = new backup_nested_element('doc', array('id'), array(
            'parentcm', 'created', 'filename', 'pages'
        ));

        $doc_pages = new backup_nested_element('doc_pages');

        $doc_page = new backup_nested_element('doc_page', array('id'), array(
            'pagenum', 'imagename', 'w', 'h'
        ));

        // pages
        $pages = new backup_nested_element('pages');
        $page = new backup_nested_element('page', array('id'), array(
            'orderkey', 'docpage', 'flags'
        ));
        $page_masks = new backup_nested_element('page_masks');
        $page_mask = new backup_nested_element('page_mask', array('id'), array(
            'x', 'y', 'w', 'h', 'style', 'question', 'flags'
        ));

        // question
        $questions = new backup_nested_element('questions');
        $question = new backup_nested_element('question', array('id'), array(
            'type', 'data'
        ));

        // Build the tree.
        $masks->add_child($questions);
        $questions->add_child($question);

        $masks->add_child($docs);
        $docs->add_child($doc);
        $doc->add_child($doc_pages);
        $doc_pages->add_child($doc_page);

        $masks->add_child($pages);
        $pages->add_child($page);
        $page->add_child($page_masks);
        $page_masks->add_child($page_mask);

        // user state
        $user_states = new backup_nested_element('user_states');
        $user_state = new backup_nested_element('user_state', array('id'), array(
            'userid', 'failcount', 'state', 'firstview', 'lastupdate'
        ));
        $question->add_child($user_states);
        $user_states->add_child($user_state);

        // Define sources.
        $masks->set_source_table('masks', array('id' => backup::VAR_ACTIVITYID));
        $doc->set_source_table('masks_doc', array('parentcm' => backup::VAR_MODID));

        $doc_page->set_source_table('masks_doc_page', array('doc' => backup::VAR_PARENTID));
        $page->set_source_table('masks_page', array('parentcm' => backup::VAR_MODID));
        $page_mask->set_source_table('masks_mask', array('page' => backup::VAR_PARENTID));
        $question->set_source_table('masks_question', array('parentcm' => backup::VAR_MODID));

        if ($userinfo) {
            $user_state->set_source_table('masks_user_state', array('question' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $page_mask->annotate_ids('masks_question', 'question');
        $page->annotate_ids('masks_doc_page', 'docpage');

        // annotate files
        $doc_page->annotate_files('mod_masks', 'masks_doc_page', 'id');

        // Return the root element (masks), wrapped into standard activity structure.
        return $this->prepare_activity_structure($masks);
    }
}
