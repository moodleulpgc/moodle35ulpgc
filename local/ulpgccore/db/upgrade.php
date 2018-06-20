<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_local_ulpgccore_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    /// just a mockup
    if ($oldversion < 0) {
        throw new upgrade_exception('local_ulpgccore', $oldversion, 'Can not upgrade such an old plugin');
    }

    /// Cleaning up in ULPGC
    if ($oldversion < 2016060104) {
        $DB->delete_records_list('config_plugins', 'plugin', array('ULPGCassignment_online', 
                                                                    'assignment_uploadon', 
                                                                    'assignment_groupupload',
                                                                    'auth/miulpgc',
                                                                    'mod_choicegroup',
                                                                    'mod_exmedia',
                                                                    'gradingform_mcqexam',
                                                                    'theme_afterburner', 
                                                                    'theme_anomaly', 
                                                                    'theme_arialist', 
                                                                    'theme_binarius', 
                                                                    'theme_boxxie', 
                                                                    'theme_brick', 
                                                                    'theme_formal_white', 
                                                                    'theme_formfactor', 
                                                                    'theme_fusion', 
                                                                    'theme_leatherbound', 
                                                                    'theme_magazine', 
                                                                    'theme_nimble', 
                                                                    'theme_nonzero', 
                                                                    'theme_overlay', 
                                                                    'theme_serenity', 
                                                                    'theme_sky_high', 
                                                                    'theme_splash', 
                                                                    'theme_standard', 
                                                                    'theme_standardold', 
                                                                    ));
        $DB->delete_records_select('config', " name LIKE '%ULPGC%' ");
        
        $DB->delete_records_list('modules', 'name', array('ULPGCassignment', 'ULPGCdialogue', 'choicegroup', 'exmedia')); 
        
        $DB->delete_records_list('capabilities', 'component', array('mod_choicegroup',
                                                                    'mod_exmedia',
                                                                    'local_ciceiconditional',
                                                                    'assignsubmission_history',
                                                                    'assignsubmission_resubmission'));

        $mods = array('ULPGCassignment', 'ULPGCassignment_submissions',  
                        'ULPGCdialogue', 'ULPGCdialogue_conversations', 'ULPGCdialogue_entries', 'ULPGCdialogue_read',);
        $prefix = $DB->get_prefix();
        foreach($mods as $mod) {
            $sql = "DROP TABLE IF EXISTS ".$prefix.$mod." ";
            $DB->execute($sql, array());
            
            $sql = "DROP TABLE IF EXISTS ".$mod." ";
            $DB->execute($sql, array());
        }
                        
        $mods = array('choicegroup', 'exmedia', 'exmedia_options',
                        'gradingform_mcqexam',  'gradingform_mcqexam_criteria', 'gradingform_mcqexam_fillings', 'gradingform_mcqexam_comments',  
                        'cicei_conditional', 'cicei_conditional_member',  
                        'assignsubmission_history', 'qtype_regexp', 'question_regexp',
                        'question_order',  'question_order_sub', 
                        'question_dragdrop', 'question_dragdrop_hotspot', 'question_dragdrop_media', 
                        'question_imagetarget', 'question_multichoicetf',
                        );
        foreach($mods as $mod) { 
            $table = new xmldb_table($mod);
            if($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }
        upgrade_plugin_savepoint(true, 2016060104, 'local', 'ulpgccore');
    }
    
    if ($oldversion < 2013062100) {

    /// mods to groups table
    /// Define table groups to be modified
        $table = new xmldb_table('groups');

        // Define field component to be added to groups
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // Conditionally launch add field component
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field itemid to be added to groups
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Conditionally launch add field itemid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         upgrade_plugin_savepoint(true, 2013062100, 'local', 'ulpgccore');
    }

    if ($oldversion < 2016060100) {
    // create new course helper table
        $table = new xmldb_table('local_ulpgccore_course');
        // Conditionally launch create table for local_ulpgccore_course
        if (!$dbman->table_exists($table)) {
            // Adding fields to table local_ulpgccore_course.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('term', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('credits', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('department', XMLDB_TYPE_CHAR, '5', null, null, null, '');
            $table->add_field('ctype', XMLDB_TYPE_CHAR, '5', null, null, null, '');
            
            // Adding keys to table local_ulpgccore_course.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

            $dbman->create_table($table);
        }
    
    /// Revert course table 
    /// Define table course to be modified
        $table = new xmldb_table('course');
    
        // Define field term added to course
        $field = new xmldb_field('term', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // copy existing fields to new table
        if ($dbman->field_exists($table, $field)) {
            $courses = $DB->get_recordset('course', null, '', 'id AS courseid, term, credits, department, ctype');
            if($courses->valid()) {
                foreach($courses as $course) {
                    if(!$old = $DB->get_record('local_ulpgccore_course', array('courseid'=>$course->courseid))) {
                        $DB->insert_record('local_ulpgccore_course', $course);
                    } else {
                        $course->id = $old->id;
                        $DB->update_record('local_ulpgccore_course', $course);
                    }
                }
            }
            $courses->close();
        }

        // Conditionally launch drop field term
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define field credits added to course
        $field = new xmldb_field('credits', XMLDB_TYPE_NUMBER, '5,2', null, XMLDB_NOTNULL, null, '0');

        // Conditionally launch drop field credits
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define field department added to course
        $field = new xmldb_field('department', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'); 

        // Conditionally launch drop field department
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field ctype added to course
        $field = new xmldb_field('ctype', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        
        // Conditionally launch drop field ctype
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
    
    // create new course_categories helper table
        $table = new xmldb_table('local_ulpgccore_categories');
        // Conditionally launch create table for local_ulpgccore_categories
        if (!$dbman->table_exists($table)) {
            // Adding fields to table local_ulpgccore_categories.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('faculty', XMLDB_TYPE_CHAR, '10', null, null, null, null);
            $table->add_field('degree', XMLDB_TYPE_CHAR, '10', null, null, null, null);
            
            // Adding keys to table local_ulpgccore_categories.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'course_categories', array('id'));

            $dbman->create_table($table);
        }
    
    /// Revert course_categories table 
    /// Define table course_categories to be modified
        $table = new xmldb_table('course_categories');

        // Define field term added to course_categories
        $field = new xmldb_field('faculty', XMLDB_TYPE_CHAR, '10', null, null, null, null);

        // copy existing fields to new table
        if ($dbman->field_exists($table, $field)) {
            $categories = $DB->get_recordset('course_categories', null, '', 'id AS categoryid, faculty, degree');
            if($categories->valid()) {
                foreach($categories as $category) {
                    if(!$old = $DB->get_record('local_ulpgccore_categories', array('categoryid'=>$category->categoryid))) {
                        $DB->insert_record('local_ulpgccore_categories', $category);
                    } else {
                        $category->id = $old->id;
                        $DB->update_record('local_ulpgccore_categories', $category);
                    }
                }
            }
            $categories->close();
        }
        
        // Conditionally launch drop field faculty
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    
         // Define field term added to course_categories
        $field = new xmldb_field('degree', XMLDB_TYPE_CHAR, '10', null, null, null, null);

        // Conditionally launch drop field faculty
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }   
    
    
    // create new grade_categories helper table
        $table = new xmldb_table('local_ulpgccore_gradecats');
        // Conditionally launch create table for local_ulpgccore_gradecats
        if (!$dbman->table_exists($table)) {
            // Adding fields to local_ulpgccore_gradecats.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('lockedit', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

            // Adding keys to table local_ulpgccore_gradecats.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'grade_categories', array('id'));

            $dbman->create_table($table);
        }
    
    /// Revert grade_categories table 
    /// Define table grade_categories to be modified
        $table = new xmldb_table('grade_categories');

        // Define field department added to grade_categories
        $field = new xmldb_field('lockedit', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'); 
        
        // copy existing fields to new table
        if ($dbman->field_exists($table, $field)) {
            $categories = $DB->get_recordset('grade_categories', null, '', 'id AS categoryid, lockedit');
            if($categories->valid()) {
                foreach($categories as $category) {
                    if(!$old = $DB->get_record('local_ulpgccore_gradecats', array('categoryid'=>$category->categoryid))) {
                        $DB->insert_record('local_ulpgccore_gradecats', $category);
                    } else {
                        $category->id = $old->id;
                        $DB->update_record('local_ulpgccore_gradecats', $category);
                    }
                }
            }
            $categories->close();
        } 

        // Conditionally launch drop field lockedit
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    // create new questions helper table
        $table = new xmldb_table('local_ulpgccore_questions');
        // Conditionally launch create table for local_ulpgccore_questions
        if (!$dbman->table_exists($table)) {
            // Adding fields to local_ulpgccore_questions.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('qsource', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('sourceqid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('creatoridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('modifieridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table local_ulpgccore_questions.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', array('id'));
            // Adding indexes to table .
            $table->add_index('qsource-id', XMLDB_INDEX_NOTUNIQUE, array('qsource', 'sourceqid'));
            $table->add_index('creatoridnumber', XMLDB_INDEX_NOTUNIQUE, array('creatoridnumber'));
            $table->add_index('modifieridnumber', XMLDB_INDEX_NOTUNIQUE, array('modifieridnumber'));
            $dbman->create_table($table);
        }

    /// Revert question table 
    /// Define table questions to be modified
        $table = new xmldb_table('question');

        // Define field component added to groups
        $field = new xmldb_field('creatoridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        
        // copy existing fields to new table
        if ($dbman->field_exists($table, $field)) {
            $questions = $DB->get_recordset('question', null, '', 'id AS questionid, creatoridnumber, modifieridnumber');
            if($questions->valid()) {
                foreach($questions as $question) {
                    $question->sourceid = $question->questionid;
                    $question->qsource = $CFG->wwwroot;
                    if(!$old = $DB->get_record('local_ulpgccore_questions', array('questionid'=>$question->questionid))) {
                        $DB->insert_record('local_ulpgccore_questions', $question);
                    } else {
                        $question->id = $old->id;
                        $DB->update_record('local_ulpgccore_questions', $question);
                    }
                }
            }
            $questions->close();
        } 

        // Conditionally launch drop field creatoridnumber
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field component added to groups
        $field = new xmldb_field('modifieridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Conditionally launch drop field modifieridnumber
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
         upgrade_plugin_savepoint(true, 2016060100, 'local', 'ulpgccore');
    }
    
    
    if ($oldversion < 2018021000) {
        // gradecats table deprecated. Grade category locking controled by depth & gradeitem info field
        $table = new xmldb_table('local_ulpgccore_gradecats');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        upgrade_plugin_savepoint(true, 2018021000, 'local', 'ulpgccore');
    }
    
    if ($oldversion < 2018061800) {
        // update  table for local_ulpgccore_questions
        $table = new xmldb_table('local_ulpgccore_questions');

        $index1 = new xmldb_index('qsource-id', XMLDB_INDEX_NOTUNIQUE, array('qsource', 'sourceqid'));
        if ($dbman->index_exists($table, $index1)) {
            $dbman->drop_index($table, $index1);
        }
        
        $index1 = new xmldb_index('creatoridnumber', XMLDB_INDEX_NOTUNIQUE, array('creatoridnumber'));
        if ($dbman->index_exists($table, $index1)) {
            $dbman->drop_index($table, $index1);
        }

        $index1 = new xmldb_index('modifieridnumber', XMLDB_INDEX_NOTUNIQUE, array('modifieridnumber'));
        if ($dbman->index_exists($table, $index1)) {
            $dbman->drop_index($table, $index1);
        }
        
        $field = new xmldb_field('sourceqid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        } else {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('qsource', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        } else {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('creatoridnumber', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        } else {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('modifieridnumber', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        } else {
            $dbman->add_field($table, $field);
        }

        $index1 = new xmldb_index('qsource-id', XMLDB_INDEX_NOTUNIQUE, array('qsource', 'sourceqid'));
        if (!$dbman->index_exists($table, $index1)) {
            $dbman->add_index($table, $index1);
        }
        
        $index1 = new xmldb_index('creatoridnumber', XMLDB_INDEX_NOTUNIQUE, array('creatoridnumber'));
        if (!$dbman->index_exists($table, $index1)) {
            $dbman->add_index($table, $index1);
        }
        
        $index1 = new xmldb_index('modifieridnumber', XMLDB_INDEX_NOTUNIQUE, array('modifieridnumber'));
        if (!$dbman->index_exists($table, $index1)) {
            $dbman->add_index($table, $index1);
        }
        
        upgrade_plugin_savepoint(true, 2018061800, 'local', 'ulpgccore');
    }

    
    return true;
}
