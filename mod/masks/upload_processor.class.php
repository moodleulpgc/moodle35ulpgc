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
 * MASKS Activity Upload processor class used for uploading and splitting pdf files into pages
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

class upload_processor{

    // -------------------------------------------------------------------------
    // Public API - Basics
    // -------------------------------------------------------------------------

    /**
     * __construct()
     * @param object $policies - the interface object that will provide all of the back end interfacing
     * @param object $config   - the configuration object containing miscellaneous config parameters
     */
    public function __construct( $policies, $config ){
        $this->policies = $policies;
        $this->config   = $config;
    }


    // -------------------------------------------------------------------------
    // Public API - Main methods
    // -------------------------------------------------------------------------

    /**
     * process()
     * @param array $fileData - the $_FILES['file'] data sent by the web page (or it's equivalent in a test environmnet)
     * @param integer $cmid - the $cm->id value for the course module instance
     */
    public function process( $fileData, $cmid ){

        // Log start of the upload process
        $this->policies->logProgressTitle( 'Processing uploaded file: ' . $fileData['name'] );

        // setup a temp folder to work with
        $this->policies->logProgressHeading('Creating Work Path');
        $workPath = $this->policies->getWorkFolderName();
        flush();

        // save the pdf file to the work folder
        $this->policies->logProgressHeading('Saving PDF File to Work Path');
        $pdfName = "doc.pdf";
        $pdfFile = $workPath . "/" . $pdfName;
        $uploadLocation = $fileData['tmp_name'];
        move_uploaded_file( $uploadLocation, $pdfFile );
        flush();

        // convert the uploaded pdf file to a set of pages
        $cmdLine = $this->config->cmdline_pdf2svg;
        $this->policies->logProgressHeading('Converting PDF File to Pages');
        system($cmdLine." $pdfFile $workPath/page-%04d.svg all");
        flush();

        // retrieve the list of generated files
        $this->policies->logProgress("Retrieving Generated File List");
        $pageFiles = array_diff(scandir($workPath), array('..', '.', $pdfName));
        if ( empty( $pageFiles ) ){
            $this->policies->logWarning('No generated page files found');
            return -1;
        }

        // setup the documane node in the database to which the pages need to be attached
        $this->policies->logProgress("Creating doc node in Moodle");
        $this->policies->initDocument( $cmid, $fileData['name'], count( $pageFiles ) );

        // iterate over the page files to store them away
        $this->policies->logProgressHeading("Storing Generated Pages in Moodle");
        foreach( $pageFiles as $pageFile ){
            preg_match( '/([0-9]+)[^0-9]*$/', $pageFile, $matches );
            $pageNum  = $matches[ 1 ];
            $this->policies->logProgress('Storing Page: '.$pageNum);
            $pageId  = $this->policies->storePageImage( $pageNum, $workPath.'/'.$pageFile );
        }

        // finalise the upload, generating an exercise from the uploaded pages
        $this->policies->logProgress("Generating Moodle Exercise From PDF Pages");
        $this->policies->finaliseUpload( $cmid );
    }

    /**
     * test whether the action resulted in a change to the image pages behind the doc
     *
     * @return boolean true if process() resulted in an update of the document pages
     */
    public function docHasChanged(){
        return $this->policies->docHasChanged();
    }

    /**
     * Return true if pdf can be convert to svg with the command line in mod masks config
     *
     * @return boolean
     */
    public function testFileConvertionTool(){
        $cmdLine = $this->config->cmdline_pdf2svg;
        if($cmdLine === ''){
            return false;
        }else{
            // try to convert dummy pdf to svg
            $dummypdf = 'assets/dummypdf.pdf';
            $workPath = $this->policies->getWorkFolderName();
            system($cmdLine." $dummypdf $workPath/dummypdfconvert.svg all");

            return ( file_exists( "$workPath/dummypdfconvert.svg" ) && file_get_contents( "$workPath/dummypdfconvert.svg" ) );
        }
    }


    // -------------------------------------------------------------------------
    // Private Data
    // -------------------------------------------------------------------------

    // The policies object provides all of the interfacing to the server back end
    // It can be re-implemented for testing purposes
    private $policies = null;
}

