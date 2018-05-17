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
 * @package    mod_makeexam
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/questionlib.php');
if ($ADMIN->fulltree) {

    // required during installation
    $dbman = $DB->get_manager();
    $table = new xmldb_table('examregistrar');
    $examregs = 0;
    if($dbman->table_exists($table)) {
        $examregs = $DB->get_records_select_menu('examregistrar', " primaryidnumber <> ''  ", null , 'name', 'id, name');
    }
    if(!$examregs) {
        $examregs = array(0 => get_string('none'));
    }

    $settings->add(new admin_setting_configcheckbox('quiz_makeexam/enabled', get_string('enabled', 'quiz_makeexam'),
                       get_string('configenabled', 'quiz_makeexam'), 0));

    $settings->add(new admin_setting_configselect('quiz_makeexam/examregistrar', get_string('registrarinuse', 'quiz_makeexam'),
                       get_string('configregistrarinuse', 'quiz_makeexam'), 0, $examregs));

    $qtypes = question_bank::get_creatable_qtypes();
    foreach($qtypes as $qtype => $qtypeobj) {
        $qtypes[$qtype] = question_bank::get_qtype_name($qtype);
    }
    $settings->add(new admin_setting_configmultiselect('quiz_makeexam/validquestions', get_string('validquestions', 'quiz_makeexam'), get_string('configvalidquestions', 'quiz_makeexam'), array('multichoice'), $qtypes));

    $qformats = get_import_export_formats('import');
    $settings->add(new admin_setting_configmultiselect('quiz_makeexam/validformats', get_string('validformats', 'quiz_makeexam'), get_string('configvalidformats', 'quiz_makeexam'), array('ulpgctf'), $qformats));

    $settings->add(new admin_setting_configtext('quiz_makeexam/numquestions', get_string('numquestions', 'quiz_makeexam'),
                       get_string('confignumquestions', 'quiz_makeexam'), 30, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('quiz_makeexam/uniquequestions', get_string('uniquequestions', 'quiz_makeexam'),
                       get_string('configuniquequestions', 'quiz_makeexam'), 1));

    $settings->add(new admin_setting_configtext('quiz_makeexam/questionspercategory', get_string('questionspercategory', 'quiz_makeexam'),
                       get_string('configquestionspercategory', 'quiz_makeexam'), 3, PARAM_INT));

    $settings->add(new admin_setting_configtext('quiz_makeexam/categorysearch', get_string('categorysearch', 'quiz_makeexam'),
                       get_string('configcategorysearch', 'quiz_makeexam'), '', PARAM_TEXT));

    $levels = array(CONTEXT_SYSTEM=>context_helper::get_level_name(CONTEXT_SYSTEM),
                    CONTEXT_COURSECAT=>context_helper::get_level_name(CONTEXT_COURSECAT),
                    CONTEXT_COURSE=>context_helper::get_level_name(CONTEXT_COURSE),
                    CONTEXT_MODULE=>context_helper::get_level_name(CONTEXT_MODULE));
    $settings->add(new admin_setting_configselect('quiz_makeexam/contextlevel', get_string('contextlevel', 'quiz_makeexam'),
                       get_string('configcontextlevel', 'quiz_makeexam'), CONTEXT_COURSE, $levels));

    $settings->add(new admin_setting_configcheckbox('quiz_makeexam/excludesubcats', get_string('excludesubcats', 'quiz_makeexam'),
                       get_string('configexcludesubcats', 'quiz_makeexam'), 1));

    $settings->add(new admin_setting_configcheckbox('quiz_makeexam/excludeunused', get_string('excludeunused', 'quiz_makeexam'),
                       get_string('configexcludeunused', 'quiz_makeexam'), 1));


    $settings->add(new admin_setting_configtext('quiz_makeexam/tex_density', get_string('tex_density', 'quiz_makeexam'),
                       get_string('configtex_density', 'quiz_makeexam'), 300, PARAM_INT));

    $settings->add(new admin_setting_configtext('quiz_makeexam/tex_imagescale', get_string('tex_imagescale', 'quiz_makeexam'),
                       get_string('configtex_imagescale', 'quiz_makeexam'), 4, PARAM_INT));

}

