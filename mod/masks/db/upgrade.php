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
 * masks module version upgrade code
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_masks_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    // include handy utility functions for setting up database fields with standardised settings
    require_once(dirname(__FILE__).'/upgradelib.php');

    // Upgrade to initial version by creating tables and adding fields
    $dbversion = 2016010100;
    if ($oldversion < $dbversion) {

        // Create the doc database table
        $newTable = new xmldb_table('masks_doc');
        mod_masks\add_db_id_field( $newTable, 'id' );
        mod_masks\add_db_int_field( $newTable, 'parentcm' );  // a $cm->id value
        mod_masks\add_db_date_field( $newTable, 'created' );
        mod_masks\add_db_txt_field( $newTable, 'filename' );  // name of the uploaded doc file
        mod_masks\add_db_int_field( $newTable, 'pages'    );  // number of doc pages extracted from the uploaded file
        $dbman->create_table($newTable);

        // Create the doc pages database table (a doc page repreesents the bitmap image corresponding to a given page of an uploaded doc)
        $newTable = new xmldb_table('masks_doc_page');
        mod_masks\add_db_id_field( $newTable, 'id' );
        mod_masks\add_db_int_field( $newTable, 'doc'             );  // identifier of uploaded 'doc' from which this page was extracted
        mod_masks\add_db_int_field( $newTable, 'pagenum'         );  // page number of this page within the doc from which it was uploaded
        mod_masks\add_db_chr_field( $newTable, 'imagename', 16   );  // filename of the image to display when showing this page (typically: "page-0001.svg")
        mod_masks\add_db_int_field( $newTable, 'w'               );  // width of the image in pixels
        mod_masks\add_db_int_field( $newTable, 'h'               );  // height of the image in pixels
        $newTable->add_index('doc', XMLDB_INDEX_NOTUNIQUE, array('doc'));
        $dbman->create_table($newTable);

        // Create the pages database table
        $newTable = new xmldb_table('masks_page');
        mod_masks\add_db_id_field( $newTable, 'id' );
        mod_masks\add_db_int_field( $newTable, 'parentcm'    );  // a $cm->id value
        mod_masks\add_db_int_field( $newTable, 'orderkey'    );  // a key that allows one to order pages correctly
        mod_masks\add_db_int_field( $newTable, 'docpage'     );  // this is the underlying page to display
        mod_masks\add_db_int_field( $newTable, 'flags'       );  // a bitmask of flags such as 'HIDDEN'
        $newTable->add_index('parentcm', XMLDB_INDEX_NOTUNIQUE, array('parentcm'));
        $dbman->create_table($newTable);

        // Create the questions database table - note that questions are polymorphic - the description
        // is stored as a json-encoded blob on the assumption that a factory will be able to make sense of it
        $newTable = new xmldb_table('masks_question');
        mod_masks\add_db_id_field( $newTable, 'id' );
        mod_masks\add_db_int_field( $newTable, 'parentcm'    );  // a $cm->id value
        mod_masks\add_db_txt_field( $newTable, 'data'        );  // json encoded data representing the question
        $newTable->add_index('parentcm', XMLDB_INDEX_NOTUNIQUE, array('parentcm'));
        $dbman->create_table($newTable);

        // Create the mask zones database table
        $newTable = new xmldb_table('masks_mask');
        mod_masks\add_db_id_field( $newTable, 'id' );
        mod_masks\add_db_int_field( $newTable, 'page'        );  // identifier of the exercise page (masks_page) record
        mod_masks\add_db_int_field( $newTable, 'x'           );  // left coordinate of mask zone
        mod_masks\add_db_int_field( $newTable, 'y'           );  // top coordinate of mask zone
        mod_masks\add_db_int_field( $newTable, 'w'           );  // width of mask zone
        mod_masks\add_db_int_field( $newTable, 'h'           );  // height of mask zone
        mod_masks\add_db_int_field( $newTable, 'style'       );  // style selector (an integer from 0 to n)
        mod_masks\add_db_int_field( $newTable, 'question'    );  // question identifier or -1
        mod_masks\add_db_int_field( $newTable, 'flags'       );  // flags such as 'BLOCK_PROGRESS' meaning user can not view further pages until this question has been answered
        $newTable->add_index('page', XMLDB_INDEX_NOTUNIQUE, array('page'));
        $newTable->add_index('question', XMLDB_INDEX_NOTUNIQUE, array('question'));
        $dbman->create_table($newTable);

        // create the user-tracking database table
        // NOTE: this table will grow fast and access will be reasonably time critical so close attention should be paid
        // to indexes. The table is intended to aggregate records that are temporaly compatible and reference the same
        // event.
        $newTable = new xmldb_table('masks_user_state');
        mod_masks\add_db_id_field ( $newTable, 'id'          );
        mod_masks\add_db_int_field( $newTable, 'userid'        );  // a $user->id value
        mod_masks\add_db_int_field( $newTable, 'question'    );  // an masks_question row reference
        mod_masks\add_db_int_field( $newTable, 'failcount'   );  // the number of times that this question has been failed
        mod_masks\add_db_int_field( $newTable, 'state'       );  // flag indicating current state (see locallib.php for the flag list)
        mod_masks\add_db_date_field( $newTable, 'firstview'  );  // the time stamp of the last action
        mod_masks\add_db_date_field( $newTable, 'lastupdate' );  // the time stamp of the last action
        $newTable->add_index( 'user', XMLDB_INDEX_NOTUNIQUE, array( 'userid' ) );
        $newTable->add_index( 'userquestion', XMLDB_INDEX_NOTUNIQUE, array( 'userid', 'question' ) );
        $newTable->add_index( 'question', XMLDB_INDEX_NOTUNIQUE, array( 'question' ) );
        $dbman->create_table( $newTable );

        // Add missing 'AUTO_INCREMENT' propertirs for id fields
        mod_masks\fix_id_field( 'masks_doc'       , 'id' );
        mod_masks\fix_id_field( 'masks_doc_page'  , 'id' );
        mod_masks\fix_id_field( 'masks_page'      , 'id' );
        mod_masks\fix_id_field( 'masks_question'  , 'id' );
        mod_masks\fix_id_field( 'masks_mask'      , 'id' );
        mod_masks\fix_id_field( 'masks_user_state', 'id' );

        upgrade_mod_savepoint(true, $dbversion, 'masks');
    }

    $dbversion = 2016102002;
    if ( $oldversion < $dbversion ) {
        $table = new xmldb_table( 'masks_question' );
        mod_masks\add_db_chr_field( $table, 'type', 32   );  // the question type - used to determine which handler object type to instantiate in frames

        // update masks type
        $allmasksquestions = $DB->get_records( 'masks_question' );
        foreach( $allmasksquestions as $question ){
            $question->type  = json_decode( $question->data )->type;
            $DB->update_record( 'masks_question', $question );
        }
        upgrade_mod_savepoint(true, $dbversion, 'masks');
    }

    $dbversion = 2017041301;
    if ( $oldversion < $dbversion ) {
        // user is keyword for pgsql and can't be used in sql
        try {
            if($CFG->dbtype == 'pgsql'){
                $sql = 'ALTER TABLE ' . $CFG->prefix  . 'masks_user_state RENAME COLUMN "user" TO "userid" ';
                $DB->change_database_structure( $sql );
            }else{
                $sql = 'ALTER TABLE ' . $CFG->prefix  . 'masks_user_state CHANGE COLUMN `user` `userid` INT(10) NOT NULL;';
                $DB->change_database_structure( $sql );
            }
        } catch (Exception $ex) {
        }
        upgrade_mod_savepoint(true, $dbversion, 'masks');
    }

    $dbversion = 2017050103;
    if ($oldversion < $dbversion) {

        // Define field toto to be added to masks.
        $table = new xmldb_table('masks');
        $field = new xmldb_field('config', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');

        // Conditionally launch add field toto.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Masks savepoint reached.
        upgrade_mod_savepoint(true, $dbversion, 'masks');
    }

    return true;
}

