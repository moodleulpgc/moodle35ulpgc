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
 * MASKS Activity Module - class for abstracting database access
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

// Flags for pages
define( 'mod_masks\PAGE_FLAG_HIDDEN', 1 );

// Individual flags for masks
define( 'mod_masks\MASK_FLAG_HIDDEN', 0x01 );
define( 'mod_masks\MASK_FLAG_GRADED', 0x02 );
define( 'mod_masks\MASK_FLAG_CLOSABLE', 0x04 );
define( 'mod_masks\MASK_FLAG_DELETED', 0x80 );
// Combined flag sets for masks
define( 'mod_masks\MASK_FLAGS_NOTE', 0 );
define( 'mod_masks\MASK_FLAGS_CLOSABLE', MASK_FLAG_CLOSABLE );
define( 'mod_masks\MASK_FLAGS_QUESTION', MASK_FLAG_CLOSABLE | MASK_FLAG_GRADED );

// User question state mask state flags
// NOTE: the order of the constants is important as state changes will only be applied if the state value increases
// NOTE: the js code assumes that the state flags occupy no more than the 16 low bits of an integer value
define( 'MASKS_STATE_SEEN', 0x10 ); // seen but not done
define( 'MASKS_STATE_DONE', 0x20 ); // seen and closed
define( 'MASKS_STATE_FAIL', 0x40 ); // at least one wrong answer given
define( 'MASKS_STATE_PASS', 0x80 ); // correct answer given with no wrong answers


class database_interface {
    private $haveChangedDoc = false; // true after successfull re-upload

    /**
     * Fetch document data - that is the information regarding the pages that comprise the document
     *
     * @param integer $cmid The course module instance id ($cm->id)
     * @return struct the document representation
     */
    public function fetchDocData( $cmid ) {
        global $DB;

        // initalise the result container
        $result = new \stdClass;

        // fetch the set of records from the database
        $query =
            'SELECT page.orderkey, page.id, page.flags, page.docpage, docpage.doc, docpage.pagenum, docpage.imagename, docpage.w, docpage.h'.
            ' FROM {masks_page} page'.
            ' JOIN {masks_doc_page} docpage ON docpage.id = page.docpage'.
            ' WHERE page.parentcm = :cmid'.
            ' ORDER BY page.orderkey'.
            '';
        $result->pages = $DB->get_records_sql($query, array( 'cmid' => $cmid ));
        $result->isInitialised = ! empty( $result->pages );

        // generate image urls from image names
        $context = \context_module::instance( $cmid );
        foreach ( $result->pages as $page ) {
            $url = \moodle_url::make_pluginfile_url( $context->id, 'mod_masks', 'masks_doc_page', $page->docpage, '/', $page->imagename );
            $page->imageurl = strval( $url );
        }

        return $result;
    }

    /**
     * Fetch mask data - that is the information regarding the set of masks overlaid over document pages
     *
     * @param integer $cmid The course module instance id ($cm->id)
     * @param bool $isTeacher if true, reset current user questions states
     * @return struct the mask set representation
     */
    public function fetchMaskData( $cmid , $isTeacher = false ) {
        require_once(dirname(__FILE__).'/mask_types_manager.class.php');
        global $DB, $USER;

        // initalise the result container
        $result = new \stdClass;

        // Reset teacher questions states
        if($isTeacher){
            $this->resetUserQuestionsStates($cmid);
        }

        $query =
            'SELECT mask.*, question.type , us.state AS userstate'.
            ' FROM {masks_page} page'.
            ' JOIN {masks_mask} mask ON page.id = mask.page'.
            ' LEFT JOIN {masks_user_state} us ON us.question = mask.question AND us.userid = :userid'.
            ' LEFT JOIN {masks_question} question ON question.id = mask.question ' .
            ' WHERE page.parentcm = :cmid'.
            ' AND (mask.flags & '.MASK_FLAG_DELETED.') = 0'.
            ' ORDER BY page.id, mask.id'.
            '';
        $masks = $DB->get_records_sql( $query, array( 'cmid' => $cmid, 'userid' => $USER->id ) );

        // construct the result, group the masks by page
        $result->pages = array();
        $result->count = count( $masks );
        foreach ($masks as $mask) {
            $page = $mask->page;
            if ( ! array_key_exists( $page, $result->pages ) ) {
                $result->pages[$page] = array();
            }
            $mask->family    = mask_types_manager::getTypeFamily($mask->type);
            $mask->userstate = ($mask->userstate == null) ? 0 : $mask->userstate;
            $result->pages[$page][] = $mask;
        }

        return $result;
    }

    /**
     * Fetch question data
     *
     * @param integer $questionId The question id
     * @return the decode question data record
     */
    public function fetchQuestionData( $questionId ) {
        global $DB;

        // initalise the result container
        $result = new \stdClass;

        // fetch the question record from the database
        $record = $DB->get_record('masks_question', array('id' => $questionId), 'id,type,data' );

        // construct the result
        $result = json_decode( $record->data );

        return $result;
    }

    /**
     * Fetch the question type property that is required to identify question family and select appropriate styling for masks
     *
     * @param integer $questionId The question id
     * @return the question type value
     */
    public function fetchQuestionType( $questionId ) {
         global $DB;

        // initalise the result container
        $result = new \stdClass;

        // fetch the question record from the database
        $record = $DB->get_record('masks_question', array('id' => $questionId), 'type' );

        // construct the result
        $result = $record->type;

        return $result;
    }

    /**
     * Instantiate a new masks_doc database row
     *
     * @param integer $cmid The course module id ($cm->id) of the cm object representing the masks activity instance
     * @param string $fileName The name of the file that has been uploaded
     * @param integer $pageCount The number of page images that have been extracted from the uploaded file
     * @return integer identifier of new row
     */
    public function getNewDoc( $cmid, $fileName, $pageCount ) {
        global $DB;
        $row = new \stdClass;
        $row->parentcm  = $cmid;
        $row->created   = time();
        $row->filename  = $fileName;
        $row->pages     = $pageCount;
        $newRow = $DB->insert_record( 'masks_doc', $row );
        return $newRow;
    }

    /**
     * Instantiate a new masks_doc database row
     *
     * @param integer $docId The identifier of the masks_doc that the page belongs to (as returned by getNewDoc())
     * @param integer $pageNumber The pdf file page number that the new object is intended to represent
     * @return integer identifier of new row
     */
    public function getNewDocPage( $docId, $pageNumber ) {
        global $DB;
        $row = new \stdClass;
        $row->doc       = $docId;
        $row->pagenum   = $pageNumber;
        $newRow = $DB->insert_record( 'masks_doc_page', $row );
        return $newRow;
    }

    /**
     * Fill in the doc page parameters that are not supplied in the call to getNewDocPage()
     *
     * @param integer $docPageId The identifier of the row to update
     * @param string $imageName The file name of the image that should be rendered to display this page
     * @param integer $width The width of the image that represents the page
     * @param integer $height The height of the image that represents the page
     */
    public function populateDocPage( $docPageId, $imageName, $width, $height ) {
        global $DB;
        $row = new \stdClass;
        $row->id        = $docPageId;
        $row->imagename = strval( $imageName );
        $row->w         = $width;
        $row->h         = $height;
        $DB->update_record( 'masks_doc_page', $row );
    }

    /**
     * Assign a set of images to the pages of an masks exercise
     *
     * @param integer $cmid The course module instance id ($cm->id)
     * @param array $docPageIds An array of integer masks_doc_page identifiers that identify the images to be used
     */
    public function assignPages( $cmid, $docPageIds ) {
        global $DB;
        // start by retrieving the identifiers of any existing masks_page records
        // that exist for this module instance
        $oldPages = $DB->get_records( 'masks_page', array('parentcm' => $cmid), 'orderkey', 'orderkey,id,flags' );
        $this->haveChangedDoc = $this->haveChangedDoc || !empty( $oldPages );

        // sort the new page set by key
        ksort( $docPageIds );

        // iterate over the new pages and old pages together
        $idx = 0;
        $oldCount = count( $oldPages );
        foreach ( $docPageIds as $docPage ) {
            if ( array_key_exists( $idx, $oldPages ) ) {
                // we have a spare record to use so go for it
                $row = new \stdClass;
                $row->id        = $oldPages[ $idx ]->id;
                $row->orderkey  = $idx;
                $row->docpage   = $docPage;
print_object( $row );
                $DB->update_record( 'masks_page', $row, true );
            } else {
                // we need to add a new record
                $row = new \stdClass;
                $row->orderkey  = $idx;
                $row->docpage   = $docPage;
                $row->parentcm  = $cmid;
                $DB->insert_record( 'masks_page', $row );
            }
            ++$idx;
        }

        // consider deleting any leftover pages
        $rowsToDelete = array();
        for (; $idx < $oldCount; ++$idx ) {
            // look to see if any masks exist for this page
            $maskCount = $DB->count_records( 'masks_mask', array( 'page' => $oldPages[ $idx ]->id ) );
            if ( $maskCount > 0 ) {
                // we have masks for this page so just update the flags
                $row = new \stdClass;
                $row->id        = $oldPages[ $idx ]->id;
                $row->orderkey  = $idx;
                $row->flags     = $oldPages[ $idx ]->flags;
                $row->docpage   = 0;
                $DB->update_record( 'masks_page', $row, true );
            } else {
                // have no masks so mark for deletion
                $rowsToDelete[] = $oldPages[ $idx ]->id;
            }
        }

        // if we have any rows to delete then go for it
        if ( ! empty( $rowsToDelete ) ) {
            $DB->delete_records_list( 'masks_page', 'id', $rowsToDelete );
        }
    }

    /**
     * Check if document has been updated
     *
     * @return boolean
     */
    public function haveReuploadedDoc(){
        return $this->haveChangedDoc;
    }

    /**
     * Check if page has masks
     *
     * @param integer $pageId
     * @return boolean
     */
    public function pageHasMasks($pageId){
        global $DB;
        $query =
            'SELECT mask.id'.
            ' FROM {masks_page} page'.
            ' JOIN {masks_mask} mask ON page.id = mask.page'.
            ' WHERE mask.page = :pageid'.
            ' AND (mask.flags & '.MASK_FLAG_DELETED.') = 0'.
            '';

        return count($DB->get_records_sql($query, array( 'pageid' => $pageId ))) > 0 ? true : false;
    }

    /**
     * Get all pages of cm, including pages without docpage
     *
     * @param integer $cmid
     * @param integer $startOrderKey
     * @return array of pages
     */
    public function getPages($cmid, $startOrderKey = 0){
        global $DB;
        $query =
            'SELECT page.orderkey, page.id, page.parentcm, page.docpage, page.flags'.
            ' FROM {masks_page} page'.
            ' WHERE page.parentcm = :cmid'.
            ' AND page.orderkey >= :orderkey'.
            ' ORDER BY page.orderkey'.
            '';
        return $DB->get_records_sql( $query, array( 'cmid' => $cmid , 'orderkey' => $startOrderKey) );
    }

    /**
     *
     * @param integer $cmid
     * @param integer $orderKey
     * @return page object
     */
    public function getPageByOrder($cmid, $orderKey){
        global $DB;
        $query =
            'SELECT page.orderkey, page.id, page.parentcm, page.docpage, page.flags'.
            ' FROM {masks_page} page'.
            ' WHERE page.parentcm = :cmid'.
            ' AND page.orderkey = :orderkey'.
            '';
        return $DB->get_record_sql( $query, array( 'cmid' => $cmid , 'orderkey' => $orderKey) );
    }

    /**
     *
     * @param interger $cmid
     * @return boolean
     */
    public function docHasFalsePageWithMasks($cmid){
        global $DB;

        $query =
                'SELECT * '
                . ' FROM {masks_page} page '
                . ' JOIN {masks_mask} m ON m.page = page.id'
                . ' WHERE page.docpage = 0';

        return $DB->get_records_sql( $query, array( 'cmid' => $cmid ) ) != false ? true : false;
    }

    /**
     * Shift all masks of pages after current page to the left or the right
     * If right shift create false page, return true
     *
     * @param integer $cmid
     * @param integer $currentOrderKey
     * @param boolean $toRight
     * @return boolean
     */
    public function shiftPageMasks($cmid, $currentOrderKey, $toRight = true){
        global $DB;

        if($toRight) {
            $nextPages = $this->getPages($cmid, $currentOrderKey);

            // return if new fase page is created
            $newFalsePage = false;

            // add new first page
            $newFirstPage = new \stdClass;
            $newFirstPage->parentcm = $cmid;
            $newFirstPage->orderkey = $currentOrderKey;
            $newFirstPage->docpage = $nextPages[$currentOrderKey]->docpage;
            $newFirstPage->flags = $nextPages[$currentOrderKey]->flags;
            $DB->insert_record('masks_page', $newFirstPage);

            foreach($nextPages as $order => $page) {

                if(isset($nextPages[$order + 1])){
                    $page->orderkey = $page->orderkey + 1;
                    $page->docpage = $nextPages[$order + 1]->docpage;
                    $DB->update_record( 'masks_page', $page );
                } else {
                    $hasMasks = $DB->record_exists('masks_mask',array('page' => $page->id));
                    if($hasMasks){
                        $page->orderkey = $page->orderkey + 1;
                        $page->docpage = 0;
                        $DB->update_record( 'masks_page', $page );
                        $newFalsePage = true;
                    }else{
                        $DB->delete_records('masks_page',array('id' => $page->id));
                    }
                }
            }
            return $newFalsePage;
        }else{
            $nextPages = $this->getPages($cmid, $currentOrderKey);
            // shift only if there is no mask in current page
            $hasMasks = $this->pageHasMasks($nextPages[$currentOrderKey]->id);
            $shiftPreviousPage = false;
            if($hasMasks){
                // check if previous page has masks
                $previousPage = $this->getPageByOrder($cmid, ($currentOrderKey-1));
                if($previousPage){
                    $shiftPreviousPage = !$this->pageHasMasks($previousPage->id);
                    if($shiftPreviousPage){
                        $nextPages[$previousPage->orderkey] = $previousPage;
                        ksort($nextPages);
                    }
                }
            }

            if(!$hasMasks || $shiftPreviousPage){
                $prevPage = false;
                foreach($nextPages as $order => $page){
                    if ($prevPage) {
                        $updatePage = new \stdClass();
                        $updatePage->id = $page->id;
                        $updatePage->parentcm = $page->parentcm;
                        $updatePage->orderkey = $prevPage->orderkey;
                        $updatePage->docpage = $prevPage->docpage;
                        $updatePage->flags = $prevPage->flags;
                        $DB->update_record( 'masks_page', $updatePage );
                        $prevPage = $page;
                    } else {
                        // is first page;
                        $DB->delete_records('masks_page',array('id' => $page->id));
                        $prevPage = $page;
                    }
                }

                // create last page if it wasn't false page
                $lastPage = end($nextPages);
                if($lastPage->docpage != 0){
                    $newLastPage = new \stdClass();
                    $newLastPage->parentcm = $cmid;
                    $newLastPage->orderkey = $lastPage->orderkey;
                    $newLastPage->docpage = $lastPage->docpage;
                    $newLastPage->flags = $lastPage->flags;
                    $DB->insert_record('masks_page', $newLastPage);
                }
            }

            return $shiftPreviousPage;
        }

        return false;
    }

    /**
     * Shift masks to left while page hasn't masks
     * @param integer $cmid
     * @param integer $currentOrderKey
     * @return boolean
     */
    public function retrieveMasks($cmid, $currentOrderKey){

        $currentPage = $this->getPageByOrder($cmid, ($currentOrderKey));

        // check if doc has false page with masks
        $hasFalsePageWithMasks = $this->docHasFalsePageWithMasks($cmid);

        if($hasFalsePageWithMasks){
            while(!($this->pageHasMasks($currentPage->id))){
                $this->shiftPageMasks($cmid, $currentOrderKey, false);
                $currentPage = $this->getPageByOrder($cmid, ($currentOrderKey));
            }
        }

        return false;
    }

    /**
     * Instantiate a new question object and an associated mask
     *
     * @param integer $cmId The course module instance id ($cm->id)
     * @param integer $pageId the masks_page on which the mask is being added
     * @param string $maskType the masks_type identifier
     * @param string $questionData the json encoded question data blob
     * @param integer $flags - a bitmask of flags composed from the mask flags constants defined above
     * @param integer $style - represents the id of the style
     * @return integer mask id for newly created mask
     */
    public function addMask( $cmId, $pageId, $maskType, $questionData, $flags, $style = 0 ) {
        global $DB;

        // start by instantiating the new question record
        $dbRecord           = new \stdClass;
        $dbRecord->parentcm = $cmId;
        $dbRecord->type     = $maskType;
        $dbRecord->data     = $questionData;
        $questionId         = $DB->insert_record( 'masks_question', $dbRecord );

        // now add the mask record
        $dbRecord           = new \stdClass;
        $dbRecord->flags    = $flags;
        $dbRecord->question = $questionId;
        $dbRecord->page     = $pageId;
        $dbRecord->x        = 20;
        $dbRecord->y        = 20;
        $dbRecord->w        = 1000;
        $dbRecord->h        = 1000;
        $dbRecord->style    = $style;
        $maskId             = $DB->insert_record( 'masks_mask', $dbRecord );

        // return the new mask id
        return $maskId;
    }

    /**
     * Update a question object
     *
     * @param integer $questionId The masks_question row for the question being updated
     * @param object $questionChanges the question content that has changed
     */
    public function updateQuestion( $questionId, $questionChanges ) {
        global $DB;

        $questionData = $this->fetchQuestionData( $questionId );

        foreach ( $questionChanges as $field => $value ) {
            $questionData->$field = $value;
        }
        $jsonData = json_encode( $questionData );

        // update the question record
        $dbRecord           = new \stdClass;
        $dbRecord->id       = $questionId;
        $dbRecord->data     = $jsonData;
        $questionId         = $DB->update_record( 'masks_question', $dbRecord );
    }

    /**
     * Update style of masks object with id = $maskid
     *
     * @param integer $maskid
     * @param integer $style
     */
    public function updateStyle($maskid, $style) {
        global $DB;
        $data = new \stdClass;
        $data->id = $maskid;
        $data->style = $style;
        $DB->update_record('masks_mask', $data);
    }

    /**
     * Check whether the user has submitted an answer to this question yet
     *
     * @param integer $userId The $USER->id value for the user in question
     * @param integer $questionId The masks_question row for the question being updated
     * @return boolean true if the user has submitted no wrong for this question yet, else false
     */
    public function isFirstQuestionAttempt( $userId, $questionId ) {
        global $DB;
        $failCount = $DB->get_field( 'masks_user_state', 'failcount', array( 'userid' => $userId, 'question' => $questionId ) );
        return ( $failCount == null ) || ( $failCount == 0 );
    }

    /**
     * Update the state of a question as a result of user interaction
     * This routine will check to see whether the new state value is greater than the old state value
     * and only store changes if they actually make sense.
     * It will also update the gradebook as required
     *
     * @param object $cm The course module instance that houses the question
     * @param integer $userId The $USER->id value for the user in question
     * @param integer $questionId The masks_question row for the question being updated
     * @param integer $stateName One of:
     *      'NONE' - the mask popup has not even been seen
     *      'VIEW' - the mask popup has been viewed but but the mask should be left visible
     *      'DONE' - the mask popup did not contain a graded question but the mask should now be hidden
     *      'FAIL' - the mask popup contained a graded question - the supplied answer was incorrect - the mask should be left visible
     *      'PASS' - the mask popup contained a graded question - the supplied answer was correct - the mask should now be hidden
     * @return integer $stateValue if the state was updated, 0 if it was not
     */
    public function updateUserQuestionState( $cm, $userId, $questionId, $stateName ) {
        global $DB;

        // convert the state name to a flag set value
        $stateNameValues = array(
            'NONE' => 0,
            'VIEW' => MASKS_STATE_SEEN,
            'DONE' => MASKS_STATE_SEEN | MASKS_STATE_DONE,
            'FAIL' => MASKS_STATE_SEEN | MASKS_STATE_FAIL,
            'PASS' => MASKS_STATE_SEEN | MASKS_STATE_DONE | MASKS_STATE_PASS
        );
        $stateValue = $stateNameValues[ $stateName ];

        // fetch the existing state record (if there is one)
        $record = $DB->get_record('masks_user_state', array('question' => $questionId, 'userid' => $userId), 'id,state,failcount' );
        if ( $record ) {
            // Look for a state regression
            $oldStateValue  = $record->state;
            if ( $stateValue <= $oldStateValue ) {
                // the state change goes backwards so ignore it
                return 0;
            }

            // If we have previously failed then convert a new 'pass,done,seen' to just 'done,seen'
            if ( ( $record->state & MASKS_STATE_FAIL ) != 0 ) {
                $stateValue = $stateValue & ~MASKS_STATE_PASS;
            }

            // the state has progressed so update the database
            $record->lastupdate = time();
            $record->state      = $record->state | $stateValue;
            $record->failcount  += ( $stateValue & MASKS_STATE_FAIL ) / MASKS_STATE_FAIL;
            $record->lastupdate = time();
            $DB->update_record( 'masks_user_state', $record );

            // if we've failed then we're done as there is no chance of needing to update the PASS count
            if ( $stateName == 'FAIL' ) {
                return $record->state;
            }
            $haveFailed         = ( $record->failcount > 0 );

        } else {
            // set an initial 'old state' value to indicate that this is the first visit
            $haveFailed         = false;

            // no previous record exists so insert a new one
            $record             = new \stdClass;
            $record->userid     = $userId;
            $record->question   = $questionId;
            $record->state      = $stateValue;
            $record->failcount  = ( $stateValue & MASKS_STATE_FAIL ) / MASKS_STATE_FAIL;
            $record->firstview  = time();
            $record->lastupdate = $record->firstview;
            $DB->insert_record( 'masks_user_state', $record );
        }

        return $record->state;
    }

    /**
     * Reset all user questions states
     * @param type $cm
     * @param type $userId
     */
    public function resetUserQuestionsStates( $cmid ){
        global $DB, $USER;

         $select =
                ' userid = :userid '.
                ' AND question IN('.
                ' SELECT id '.
                ' FROM {masks_question}'.
                ' WHERE parentcm = :cmid '.
                ' )'.
                '';
        return $DB->delete_records_select( 'masks_user_state', $select , array( 'cmid' => $cmid, 'userid' => $USER->id ) );
    }

    /**
     * Calculate the user's score and update the moodle gradebook
     *
     * @param object $cm The course module instance that's being graded
     * @param integer $userId The $USER->id value for the user in question
     * @return number $gradeValue in the range 0.0 .. 100.0
     */
    public function gradeUser( $cm, $userId ) {
        global $CFG, $DB;
        require_once($CFG->libdir.'/gradelib.php');

        // count the number of questions
        $query = ''
            .'SELECT count(*) AS result'
            .' FROM {masks_page} p'
            .' JOIN {masks_mask} m ON m.page = p.id'
            .' WHERE p.parentcm = :cmid'
            .' AND (m.flags & '.(MASK_FLAG_GRADED|MASK_FLAG_HIDDEN).')='.MASK_FLAG_GRADED
            ;
        $numQuestions = $DB->get_field_sql( $query, array( 'cmid' => $cm->id ) );

        // count the number of correct answers
        $query = ''
            .'SELECT count(*) as result'
            .' FROM {masks_question} q'
            .' JOIN {masks_user_state} s ON q.id = s.question'
            .' WHERE q.parentcm = :cmid'
            .' AND s.userid = :userid'
            .' AND (s.state & '.MASKS_STATE_PASS.') > 0'
            ;
        $passes = $DB->get_field_sql( $query, array( 'cmid' => $cm->id, 'userid' => $userId ) );

        // calculate and apply the grade
        $gradeValue     = ( $passes == $numQuestions) ? 100.0 : ( $passes * 100.0 / $numQuestions );
        $gradeRecord    = array( 'userid' => $userId, 'rawgrade' => $gradeValue );
        $gradeResult    = \grade_update('mod/masks', $cm->course, 'mod', 'masks', $cm->instance, 0, $gradeRecord, null);
        if ( $gradeResult != GRADE_UPDATE_OK ) {
            throw new \moodle_exception( 'Failed to update gradebook' );
        }

        // return the grade value
        return $gradeValue;
    }
}

