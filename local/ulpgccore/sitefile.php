<?php
      // This script fetches files from specific dataroot directory used as file repository
      // Syntax:      sitefile.php?file=fileid&dir=directory&forcedownload=x
      //              fileid : code for course files
      //              dir: directory for  repository
      //              forcedownload : 0/1
      // ecastro ULPGC

    require_once (dirname ( __FILE__ ) . '/../../config.php');
    require_once($CFG->dirroot.'/lib/filelib.php');

    if (!isset($CFG->filelifetime)) {
        $lifetime = 86400;     // Seconds for files to remain in caches
    } else {
        $lifetime = $CFG->filelifetime;
    }

    $fileid = required_param('file', PARAM_FILE);
    $repository = optional_param('dir', 'manuales', PARAM_FILE);
    $forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);

    $ext = optional_param('ext', 'pdf', PARAM_FILE);
    $ext = clean_filename(core_text::strtolower(trim($ext)));

    $conv = optional_param('conv', 'ord', PARAM_FILE);
    $conv = clean_filename(core_text::strtolower(trim($conv)));

    $year = optional_param('year', '', PARAM_FILE);
    $year = clean_filename(core_text::strtolower(trim($year)));
    if ($year !== '' ) {
        $year = '-'.$year;
    }

    $name =  optional_param('name', '', PARAM_FILE);

    if(!$course = $DB->get_record('course', array('idnumber'=>$fileid))) {
        print_error('Invalid course ID');
    }

    require_login();

    $catdata = explode('_', $course->idnumber);
    $faculty = $catdata[6];
    $title = $catdata[0];
    $shortname = $catdata[5];

    // can be replaced with a switch for future expansion if needed
    switch ($repository) {
        case 'manuales' :
            if (!$course_category = $DB->get_record('course_categories', array('id'=>$course->category))) {
              print_error('Invalid course_category ID');
            }
            if(isset($course_category->faculty_degree)) {
                $faculty_degree = $course_category->faculty_degree;
            } else {
                $faculty_degree = $course_category->idnumber;
            }

            $dirname = 'manuales/'.clean_filename($faculty_degree);
            $filename = clean_filename('M-'.$faculty_degree.'-'.$shortname.$year.'.'.$ext);
            $pattern = clean_filename('M-'.$faculty_degree.'-'.$shortname.'-[0-9][0-9][0-9]-20[0-9][0-9]{,'.$year.'}.'.$ext);
            break;
        case 'calendarios' :
            $dirname = 'calendarios/'.clean_filename($title);
            $filename = clean_filename('C-'.$title.$year.'.'.$ext);
            break;
        case 'general' :
            $dirname = 'general';
            $filename = $name.$year.'.'.$ext;
            break;

        case 'respuestas' :
            $dirname = 'respuestas/'.clean_filename($title);
            $filename = clean_filename('R-'.$title.'-'.$shortname.'-'.$conv.$year.'.'.$ext);
            break;
        default :
            $dirname = clean_filename($repository);
            $filename = clean_filename($title.'-'.$shortname.'.'.$ext);
            break;
    }

    $relativepath = '/repository/'.$dirname.'/';
    $pathname = $CFG->dataroot.$relativepath.$filename;
    
    // check that file exists
    if (!file_exists($pathname)) {
        if($files = glob($CFG->dataroot.$relativepath.$pattern, GLOB_BRACE)) {
            natsort($files);
            $pathname = end($files);
            $filename = basename($pathname);
        }

        if (!file_exists($pathname)) {
            not_found($course, $filename);
        }
    }

    // ========================================
    // finally send the file
    // ========================================
    session_write_close(); // unlock session during fileserving
    send_file($pathname, $filename, $lifetime, $CFG->filteruploadedfiles, false, $forcedownload);


    function not_found($course, $filename) {
        global $CFG, $OUTPUT, $PAGE;

        $context = context_course::instance($course->id);
        $title = $course->fullname;
        $baseurl = new moodle_url('/course/view.php', array('id'=>$course->id));
        $PAGE->set_context($context);
        $PAGE->set_url($baseurl);
        $PAGE->set_pagelayout('report');
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        $PAGE->set_cacheable( true);

        header('HTTP/1.0 404 not found');
        $OUTPUT->heading(get_string('file').': '.$filename);
        print_error('filenotfound', 'error', $CFG->wwwroot.'/course/view.php?id='.$course->id, $filename); //this is not displayed on IIS??
    }
?>
