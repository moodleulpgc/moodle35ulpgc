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
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot."/question/engine/bank.php");


if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtime('examregistrar/runtimestarthour', 'runtimestartminute', get_string('cronruntimestart', 'examregistrar'), get_string('configcronruntimestart', 'examregistrar'), array('h' => 3, 'm' => 30)));

    $settings->add(new admin_setting_configcheckbox('examregistrar/pdfwithteachers',
        get_string('pdfwithteachers', 'examregistrar'), get_string('configpdfwithteachers', 'examregistrar'), 0));

    $settings->add(new admin_setting_configcheckbox('examregistrar/pdfaddexamcopy',
        get_string('pdfaddexamcopy', 'examregistrar'), get_string('configpdfaddexamcopy', 'examregistrar'), 0));


    $settings->add(new admin_setting_configtext('examregistrar/selectdays', get_string('selectdays', 'examregistrar'),
                       get_string('configselectdays', 'examregistrar'), 30, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/cutoffdays', get_string('cutoffdays', 'examregistrar'),
                       get_string('configcutoffdays', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/extradays', get_string('extradays', 'examregistrar'),
                       get_string('configextradays', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/lockdays', get_string('lockdays', 'examregistrar'),
                       get_string('configlockdays', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/approvalcutoff', get_string('approvalcutoff', 'examregistrar'),
                       get_string('configapprovalcutoff', 'examregistrar'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/printdays', get_string('printdays', 'examregistrar'),
                       get_string('configprintdays', 'examregistrar'), 3, PARAM_INT));

    $categories =  make_categories_options();
    $settings->add(new admin_setting_configmultiselect('examregistrar/staffcats', get_string('staffcategories', 'examregistrar'), get_string('configstaffcategories', 'examregistrar'), null, $categories));

    $settings->add(new admin_setting_configcheckbox('examregistrar/excludecourses', get_string('excludecourses', 'examregistrar'),
                    get_string('configexcludecourses', 'examregistrar'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('examregistrar/venuelocationtype', get_string('venuelocationtype', 'examregistrar'),
                       get_string('configvenuelocationtype', 'examregistrar'), '', PARAM_ALPHANUMEXT, '8'));

    $settings->add(new admin_setting_configtext('examregistrar/defaultrole', get_string('defaultrole', 'examregistrar'),
                       get_string('configdefaultrole', 'examregistrar'), '', PARAM_ALPHANUMEXT, '8'));


    $settings->add(new admin_setting_configtext('examregistrar/extanswers', get_string('extensionanswers', 'examregistrar'),
                       get_string('configextensionanswers', 'examregistrar'), '', PARAM_FILE, 10));

    $settings->add(new admin_setting_configtext('examregistrar/extkey', get_string('extensionkey', 'examregistrar'),
                       get_string('configextensionkey', 'examregistrar'), '', PARAM_FILE, 10));

    $settings->add(new admin_setting_configtext('examregistrar/extresponses', get_string('extensionresponses', 'examregistrar'),
                       get_string('configextensionresponses', 'examregistrar'), '', PARAM_FILE, 10));

    $settings->add(new admin_setting_configtext('examregistrar/responsesfolder', get_string('responsesfolder', 'examregistrar'),
                       get_string('configresponsesfolder', 'examregistrar'), '', PARAM_PATH, 20));

    $settings->add(new admin_setting_configtext('examregistrar/responsessheeturl', get_string('responsessheeturl', 'examregistrar'),
                       get_string('configresponsessheeturl', 'examregistrar'), '', PARAM_INT, 10));

    $settings->add(new admin_setting_configtext('examregistrar/sessionsfolder', get_string('sessionsfolder', 'examregistrar'),
                       get_string('configsessionsfolder', 'examregistrar'), '', PARAM_PATH, 20));

    $settings->add(new admin_setting_configtext('examregistrar/distributedfolder', get_string('distributedfolder', 'examregistrar'),
                       get_string('configdistributedfolder', 'examregistrar'), 'distributed', PARAM_PATH, 20));


}

