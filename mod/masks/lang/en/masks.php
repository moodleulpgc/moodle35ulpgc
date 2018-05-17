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
 * Strings for component 'masks', language 'en'
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


// general strings - for use selecting a module type, or listing module types, etc
$string['modulename']                   = 'Mask';
$string['modulenameplural']             = 'Mask Instances';
$string['modulename_help']              = 'Upload PDF files, mask out zones and add questions for students to answer in order to see the full page';

// plugin administration strings
$string['pluginadministration']         = 'Masks module administration';
$string['pluginname']                   = 'Mask';

// plugin capacities
$string['masks:addinstance']            = 'Add a new masks activity' ;
$string['masks:view']                   = 'View masks activty';

// admin settings
$string['settinghead_basics']           = 'Principle Settings';
$string['settinghead_configuration']    = 'Advanced Options';
$string['settinghead_advanced']         = 'Developper Options';
$string['settingname_cmdline_pdf2svg']  = 'Command line for executing pdf2svg utility (that must be installed on the system for this plugin to work)';
$string['settingname_debug']            = 'Enable debugging options';
$string['settingname_maskedit']         = 'Additional Fields to show in question editing';
$string['setting_fields_none']          = 'none';
$string['setting_fields_h']             = 'hint';
$string['setting_fields_hf']            = 'hint, feedback';
$string['settingname_showghosts']       = 'Show imprints of passed masks';

// instance settings
$string['name']                         = 'Activity Name';

// Misc strings
$string['page-mod-masks-x']             = 'Any Masks view';
$string['modulename_link']              = 'mod/masks/view';

// Messages displayed in notification area
$string['notReadyMsg']                  = 'Document not ready. Please try again later.';

// Texts for menus
$string['page']                         = 'Page';
$string['options']                      = 'Options';
$string['full-size']                    = 'Zoom 100%';
$string['reshow-masks']                 = 'Show Passed Masks';
$string['rehide-masks']                 = 'Hide Passed Masks';
$string['page-hidden']                  = 'Hide Page';
$string['reupload']                     = 'Re-Upload Document';
$string['add-mask-menu']                = 'ADD';
$string['mask-actions-group']           = ''; // 'Mask: ';
$string['edit-question']                = 'Edit';
$string['mask-style-menu']              = 'Style';
$string['mask-hidden']                  = 'Hide';
$string['mask-deleted']                 = 'Delete';
$string['layout-save-group']            = ''; // 'Unsaved Changes: ';
$string['save-layout']                  = 'SAVE';
$string['left-group']                   = '';
$string['right-group']                  = '';
$string['gradeNamePass']                = 'Perfect<br>Answers';
$string['gradeNameToGo']                = 'Questions<br>Remaining';
$string['header_congratulations_text']  = 'STRIKE!';
$string['masks-shift-right']            = 'Ripple masks on';
$string['masks-shift-left']             = 'Ripple masks back';
$string['masks-retrieve-masks']         = 'Retrieve lost masks';

// Texts for congratulation frame
$string['frame_congratulation']         = 'Congratulation';
$string['frame_congratulation_text']    = 'You have answered all the questions';

// Text for mask-type-related frames
$string['label_title']                  = 'Title';
$string['label_note']                   = 'Note text';
$string['label_question']               = 'The question';
$string['label_answer']                 = 'Correct answer (one valid alternative per line)';
$string['label_valid_answers']          = 'Valid answers';
$string['label_response']               = 'Answer';
$string['label_goodanswer']             = 'The correct answer';
$string['label_badanswer0']             = 'Incorrect answers';
$string['label_badanswer1']             = 'Another incorrect answer';
$string['label_badanswer2']             = 'Another incorrect answer';
$string['label_badanswer3']             = 'Another incorrect answer';
$string['label_badanswers']             = 'Incorrect answers';
$string['label_goodanswerhint']         = 'Correct answer response';
$string['label_badanswerhint']          = 'Incorrect answer response';
$string['label_userhint']               = 'Hint';
$string['label_showhint']               = 'Show Hint';
$string['label_hidehint']               = 'Hide Hint';
$string['label_showhelp']               = 'Show Help';
$string['label_hidehelp']               = 'Hide Help';
$string['label_submit']                 = 'Submit';
$string['label_cancel']                 = 'Cancel';
$string['label_close']                  = 'Close';
$string['label_style']                  = 'Style';
$string['passanswer_title']             = 'Correct';
$string['passanswer_text']              = 'That is the correct answer';
$string['goodanswer_title']             = 'Perfect';
$string['goodanswer_text']              = 'Well done. That is the correct answer.';
$string['finalanswer_title']            = 'Congratulations';
$string['finalanswer_text']             = 'All questions in this exercise have been answered<br>'
                                        . 'More than one attempt was required to find the correct answer for one or more questions';
$string['perfectanswer_title']          = 'Strike!';
$string['perfectanswer_text']           = 'Well done!<br>'
                                        . 'All questions were answered correctly at the first attempt';
$string['wronganswer_title']            = 'Incorrect';
$string['wronganswer_text']             = 'First incorrect attempt<br>'
                                        . 'You will not score any marks for this question but you you should try again as you must give the correct answer in order to clear this mask';
$string['badanswer_title']              = 'Incorrect';
$string['badanswer_text']               = 'Please try again';

// Text for layour auto-save frames
$string['save-confirm-title']           = 'Save Layout Changes?';
$string['save-confirm-text']            = 'You have made changes to the document layout that have not been saved. If you do not save them now then they may be lost';
$string['label_save']                   = 'Save';
$string['label_nosave']                 = 'Do Not Save';

// Text for upload frames
$string['upload-input-title']           = 'Upload a PDF document';
$string['upload-input-text']            = ''
    . 'Choose a pdf document to upload<br><br>'
    . 'NOTE: The file size limit for your server will be configured by your system administrators. If you are having trouble uploading a very large file then please check with your administrators.<br>'
    ;
$string['upload-wait-title']            = 'Uploading Document';
$string['upload-wait-text']             = ''
    . 'Your file is being uploaded to the server.<br>'
    . 'This operation may take a little time.<br><br>'
    . 'Once uploaded the file will be processed on the server.<br><br>'
    . 'This message may vanish for a moment while the server is processing.<br<br>'
    . 'Please do not refresh your browser page or navigate to a different page while the upload is in progress.<br>'
    ;
$string['label_upload']                 = 'Upload';
$string['label_upload_complete']        = 'Done';
$string['failed-upload-title']          = 'Error';
$string['failed-upload-text']           = 'Upload failed - please try again or contact your system administrator';

// cmdline_pdf2svg isn't correct
$string['failedcmdline-title']          = 'Incorrect pdf2svg command line';
$string['failedcmdline-text']           = ''
        . 'Command line for executing pdf2svg utility is incorrect.<br>'
        . 'Please contact your system administrator';

// Alert texts
$string['alert_uploadnofile']           = 'To get started please upload a PDF file';
$string['alert_uploadsuccess']          = 'Congratulations. Your document has been uploaded.';
$string['alert_reuploadsuccess']        = 'Your document has been uploaded<br>If pages have been inserted or removed then the Ripple Masks On and Ripple Masks Back menu options can be used to move existing masks to the pages where they belong.';
$string['alert_uploadfailed']           = 'Upload failed - please try again or contact your system administrator';
$string['alert_firstMaskAdded']         = 'Drag the mask to move and resize it';
$string['alert_questionSaved']          = 'Changes have been saved';
$string['alert_changesSaved']           = 'Changes have been saved';
$string['alert_saveStyleChange']        = 'Click the Save button to save the style change';
$string['alert_savePageHidden']         = 'Hide page from students: Click the Save to save this change';
$string['alert_saveMaskHidden']         = 'Hide mask from students: Click the Save to save this change';
$string['alert_saveDeletion']           = 'Delete mask: Click the Save to delete it forever';
$string['alert_saveChanges']            = 'There are unsaved changes';
$string['alert_studentGradePass']       = 'Correct Answer';
$string['alert_studentGradeDone']       = '';
$string['alert_studentGradeFail']       = 'Incorrect Answer';
$string['alert_gradeNamePass']          = 'Correct Answers';
$string['alert_gradeNameToGo']          = 'Questions Remaining';
$string['alert_shiftRight']             = 'Masks from this page on have have been moved to the following page';
$string['alert_shiftLeft']              = 'Masks from this page on have been moved to the previous page';
$string['alert_falsePage']              = 'Masks beyond the end of the document can be recovered by rippling them back';

// Textes sent down to the javascript for dynamic use in browser
$string['navigateaway']                 = 'You have made changs that have not been saved\nTo save them click on \"'.$string['label_save'].'\"';

// Text strings for different mask types
$string['add-mask-qcm']                 = 'Multiple Choice Question';
$string['add-mask-qtxt']                = 'Simple Question';
$string['add-mask-basic']               = 'Dismissable Note';
$string['add-mask-note']                = 'Permanent Note';

$string['title_new_qcm']                = 'New Multiple Choice Question';
$string['title_new_qtxt']               = 'New Simple Question';
$string['title_new_basic']              = 'New Dismissable Note';
$string['title_new_note']               = 'New Permanent Note';

$string['title_edit_qcm']               = 'Multiple Choice Question';
$string['title_edit_qtxt']              = 'Simple Question';
$string['title_edit_basic']             = 'Dismissable Note';
$string['title_edit_note']              = 'Permanent Note';

$string['edithelpfeedback']             = ''
    . $string['label_goodanswerhint'] . ' / ' . $string['label_badanswerhint']
    . '<br>'
    . 'Optional fields that overeride default feedback messages that student sees after answering a question.'
    ;
$string['edithelphint']                 = ''
    . $string['label_userhint']
    . '<br>'
    . 'An optional extra information shown to students who have give one or more incorrect answers and have not yet found the correct answer to the question.<br><br>'
    ;
$string['edithelp_qcm']                 = ''
    . 'This is a multiple choice question with alternative answers displayed in random order'
    ;
$string['edithelp_qtxt']                = ''
    . 'This is a short answer question, requiring the student to type an answer of not more than a few words in length.'
    . '<br><br>'
    . 'It is possible to provide alternative valid answers to the question by entering each on a new line in the answer field.'
    ;
$string['edithelp_basic']               = ''
    . 'Display a pop-up once, typically to give initial instructions before beginning an exercise.'
    ;
$string['edithelp_note']                = ''
    . 'Display an annotation that can be viewed by the student at will and never vanishes'
    ;

$string['settingname_disable_qcm']      = 'Disable Multiple Choice Questions';
$string['settingname_disable_qtxt']     = 'Disable Simple Questions';
$string['settingname_disable_basic']    = 'Disable Dismissable Note';
$string['settingname_disable_note']     = 'Disable Permanent Note';

