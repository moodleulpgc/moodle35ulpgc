<?php // $Id: file.php,v 1.46.2.5 2009/04/09 09:30:32 skodak Exp $
      // This script fetches files from the dataroot directory
      //
      // You should use the get_file_url() function, available in lib/filelib.php, to link to file.php.
      // This ensures proper formatting and offers useful options.
      //
      // Syntax:      file.php/courseid/dir/dir/dir/filename.ext
      //              file.php/courseid/dir/dir/dir/filename.ext?forcedownload=1 (download instead of inline)
      //              file.php/courseid/dir (returns index.html from dir)
      // Workaround:  file.php?file=/courseid/dir/dir/dir/filename.ext
      // Test:        file.php/testslasharguments


      //TODO: Blog attachments do not have access control implemented - anybody can read them!
      //      It might be better to move the code to separate file because the access
      //      control is quite complex - see bolg/index.php
      // ecastro ULPGC

    require_once('../../config.php');
    require_once($CFG->dirroot.'/lib/filelib.php');

    if (!isset($CFG->filelifetime)) {
        $lifetime = 86400;     // Seconds for files to remain in caches
    } else {
        $lifetime = $CFG->filelifetime;
    }

    // disable moodle specific debug messages
    //disable_debugging();

    $relativepath = get_file_argument('file.php');
    $forcedownload = 1;

    // relative path must start with '/', because of backup/restore!!!
    if (!$relativepath) {
        error('No valid arguments supplied or incorrect server configuration');
    } else if ($relativepath{0} != '/') {
        error('No valid arguments supplied, path does not start with slash!');
    }

    $pathname = $CFG->dataroot.$relativepath;

    // extract relative path components
    $args = explode('/', trim($relativepath, '/'));
    if (count($args) == 0) { // always at least courseid, may search for index.html in course root
        error('No valid arguments supplied');
    }

    // security: limit access to existing repository subdirectories
    if (($args[0]!='repository') and ($args[0] != trim($CFG->examenes, '/'))) {
        error('Invalid course ID');
    }

    if((!$course) AND ($args[0] == trim($CFG->examenes, '/'))) {
        $course = get_site();
    }

    // security: login to course if necessary
    // Note: file.php always calls require_login() with $setwantsurltome=false
    //       in order to avoid messing redirects. MDL-14495
    if ($course->id != SITEID) {
        require_login($course->id, true, null, false);
    } else if ($CFG->forcelogin) {
        if (!empty($CFG->sitepolicy)
            and ($CFG->sitepolicy == $CFG->wwwroot.'/file.php'.$relativepath
                 or $CFG->sitepolicy == $CFG->wwwroot.'/file.php?file='.$relativepath)) {
            //do not require login for policy file
        } else {
            require_login(0, true, null, false);
        }
    }

    if (is_dir($pathname)) {
        if (file_exists($pathname.'/index.html')) {
            $pathname = rtrim($pathname, '/').'/index.html';
            $args[] = 'index.html';
        } else if (file_exists($pathname.'/index.htm')) {
            $pathname = rtrim($pathname, '/').'/index.htm';
            $args[] = 'index.htm';
        } else if (file_exists($pathname.'/Default.htm')) {
            $pathname = rtrim($pathname, '/').'/Default.htm';
            $args[] = 'Default.htm';
        } else {
            // security: do not return directory node!
            not_found($course->id);
        }
    }


    // check that file exists
    if (!file_exists($pathname)) {
        not_found($course->id);
    }

    // ========================================
    // finally send the file
    // ========================================
    session_write_close(); // unlock session during fileserving
    $filename = $args[count($args)-1];
    send_file($pathname, $filename, $lifetime, $CFG->filteruploadedfiles, false, $forcedownload);

    function not_found($courseid) {
        global $CFG;
        header('HTTP/1.0 404 not found');
        print_error('filenotfound', 'error', $CFG->wwwroot.'/course/view.php?id='.$courseid); //this is not displayed on IIS??
    }
?>
