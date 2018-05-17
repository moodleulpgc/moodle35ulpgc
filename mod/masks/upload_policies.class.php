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
 * MASKS Main variant of Policies class for specialising file upload sub-systems
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

class upload_policies{

    // -------------------------------------------------------------------------
    // Public API - Basics
    // -------------------------------------------------------------------------

    /**
     * __construct()
     * @param integer $cm the course module identifier
     */
    public function __construct( $cm ) {
        // instantiate the database interface
        require_once(dirname(__FILE__).'/database_interface.class.php');
        $this->databaseInterface = new database_interface;

        // construct the context object
        $this->context = \context_module::instance( $cm->id );

        // initialise logging
        $this->outputCSSRules();
    }

    /**
     * __destruct()
     *
     * Destructor, responsible for deleting local temp files etc
     */
    public function __destruct() {
        if($this->workFolder){
            self::delTree($this->workFolder);
        }
    }


    // -------------------------------------------------------------------------
    // Public API - Main API
    // -------------------------------------------------------------------------

    /**
     * Create a new record for the document object
     *
     * @param integer $cmid - the $cm->id value for the course module instance howsing the masks activity
     * @param integer $fileName - the name of the uploaded pdf file
     * @param integer $pageCount - the number of pages extracted from the file
     *
     * @return integer row number of newly created entry in masks_doc table
     */
    public function initDocument( $cmid, $fileName, $pageCount ) {
        // add a record to the database to hold the new page record and store it away for later use
        $docId = $this->databaseInterface->getNewDoc( $cmid, $fileName, $pageCount );
        $this->docId = $docId;
        return $docId;
    }

    /**
     * Store image file in definitive location
     *
     * @param integer $pageNumber - the page number (corresponding to page numbers in the uploaded pdf file)
     * @param integer $pageFileName - the name of the page file in its current (temporary) location
     */
    public function storePageImage( $pageNumber, $pageFileName ) {
        // start by analysing the image file contents to extract the page width and height
        list( $width, $height ) = $this->getPageImageSize( $pageFileName );

        // add a record to the database to hold the new page record
        $docPageId = $this->databaseInterface->getNewDocPage( $this->docId, $pageNumber );

        // store the file away in the Moodle internal file storage
        $fs = get_file_storage();
        $fileRecord = array(
            'contextid' => $this->context->id,
            'component' => 'mod_masks',
            'filearea'  => 'masks_doc_page',
            'itemid'    => $docPageId,
            'filepath'  => '/',
            'filename'  => basename( $pageFileName )
        );
        $fs->create_file_from_pathname( $fileRecord, $pageFileName );

        // update the doc page, writing the url to it
        $this->databaseInterface->populateDocPage( $docPageId, $fileRecord['filename'], $width, $height );

        // store away the result
        $this->pageIds[ $pageNumber ] = $docPageId;
    }

    /**
     * Instantiate database records to generate an exercise corresponding to the uploaded document
     *
     * @param integer $cmid - the $cm->id value for the course module instance that we're populating
     */
    public function finaliseUpload( $cmid ){
        $this->databaseInterface->assignPages( $cmid, $this->pageIds );
        $this->haveChangedDoc = $this->databaseInterface->haveReuploadedDoc();
    }

    /**
     * test whether the action resulted in a change to the image pages behind the doc
     *
     * @return boolean true if finaliseUpload() resulted in an update of the document pages
     */
    public function docHasChanged(){
        return $this->haveChangedDoc;
    }


    // -------------------------------------------------------------------------
    // Public API - Utilities - File System
    // -------------------------------------------------------------------------

    /**
     * getWorkFolderName()
     *
     * Generate a new local temp folder name for used for operations within the AJAX call
     * Note that subsequent calls to the same method will return the same foilder name
     * Note that the folder contents will be deleted by the destructor of the ajax_policies object
     *
     * @return string file name
     */
    public function getWorkFolderName() {
        // if we don't have a work folder yet then create one
        if ( ! $this->workFolder ){
            $this->workFolder = self::newTempDir();
        }
        return $this->workFolder;
    }


    // -------------------------------------------------------------------------
    // Public API - Utilities - Logging
    // -------------------------------------------------------------------------

    private function outputCSSRules(){
        echo '<style>';
        echo 'log-title {color:light-blue;font-size:xx-large;font-weight:bold}';
        echo 'log-heading {color:blue;font-size:x-large;font-weight:bold}';
        echo 'log-msg {color:black;font-size:large}';
        echo 'log-warn {color:red;font-size:large;font-weight:bold}';
        echo '</style>';
    }

    public function logProgressTitle( $msg ){
        echo '<div class="log-title">' . $msg . "</div>\n";
        flush();
    }

    public function logProgressHeading( $msg ){
        echo '<div class="log-heading">' . $msg . "</div>\n";
        flush();
    }

    public function logProgress( $msg ){
        echo '<div class="log-msg">' . $msg . "</div>\n";
        flush();
    }

    public function logWarning( $msg ){
        echo '<div class="log-warn">' . $msg . "</div>\n";
        flush();
    }


    // -------------------------------------------------------------------------
    // Private Utility Routines - Standard PHP
    // -------------------------------------------------------------------------

    static private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if ( is_dir( "$dir/$file" ) ){
                self::delTree("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        return rmdir($dir);
    }

    static private function newTempDir() {
        // create a temp file and delete it immediately (just to generate the unique file name)
        $tempName = tempnam( sys_get_temp_dir(), 'masks-' );
        if ( file_exists( $tempName ) ) {
            unlink( $tempName );
        }

        // create a directory with the same name as the genarated temp file had
        mkdir( $tempName, 0775, true );
        if (! is_dir( $tempName ) ){
            throw new \Exception( "tempdir(): Failed to create the new temp directory" );
        }

        return $tempName;
    }


    // -------------------------------------------------------------------------
    // Private Utility Routines - Image File Analysis
    // -------------------------------------------------------------------------

    private function getPageImageSize( $fileName ){
        // this method works on the assumption that, given that the svg files are written
        // by a single piece of software (ie pdf2svg) the construction of the output files
        // can be guaranteed to be systematic
        // an svg file is an xml file of which the first line is the <xml...> clause and the
        // second line is an <svg...> clause with parameters that interest us
        $inf = fopen($fileName, 'r');
        while( $inf && ! feof( $inf ) ){
            $line = fgets( $inf );
            // We're looking for a line something like this:
            // <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="720pt" height="540pt" viewBox="0 0 720 540" version="1.1">
            preg_match( '/viewBox[^=]*=[ \t]*[^ \t]0[ \t]+0[ \t]+([0-9]+)[ \t]+([0-9]+)/', $line, $matches );
            if ( count( $matches ) == 3 ){
                // match found!
                fclose($inf);
                $width = intval( $matches[1] );
                $height = intval( $matches[2] );
                if ( $width <= 0 || $height <= 0 ){
                    // there's something wrong with the matches - they appear not to be numeric
                    $this->logWarning( 'Failed to extract width and height from svg file: ' . $fileName . ': <' . $width . '> <' . $height . '>' );
                    return array( 1024, 1448 ); // assume A4
                }
                return array( $width, $height );
            }
        }
        fclose($inf);
        // no matches were found so yell and return
        $this->logWarning( 'Failed to locate image width and height in svg file: ' . $fileName );
        return array( 1024, 1448 ); // assume A4
    }


    // -------------------------------------------------------------------------
    // Private Data
    // -------------------------------------------------------------------------

    private $context            = null;
    private $workFolder         = null;
    private $databaseInterface  = null;
    private $docId              = null;
    private $pageIds            = array();
    private $haveChangedDoc     = false;
}

