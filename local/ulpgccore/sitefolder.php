<?php
      // This script fetches files from specific dataroot directory used as file repository
      // Syntax:      sitefile.php?file=fileid&dir=directory&forcedownload=x
      //              fileid : code for course files
      //              dir: directory for  repository
      //              forcedownload : 0/1
      // ecastro ULPGC

    require_once('../../config.php');
    require_once($CFG->dirroot.'/lib/filelib.php');


    $id       = optional_param('id', 0, PARAM_INT);        // Course module ID
    $u        = optional_param('u', 0, PARAM_INT);         // URL instance id
    $redirect = optional_param('redirect', 0, PARAM_BOOL);

    if ($u) {  // Two ways to specify the module
        $url = $DB->get_record('url', array('id'=>$u), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('url', $url->id, $url->course, false, MUST_EXIST);

    } else {
        $cm = get_coursemodule_from_id('url', $id, 0, false, MUST_EXIST);
        $url = $DB->get_record('url', array('id'=>$cm->instance), '*', MUST_EXIST);
    }

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

    require_course_login($course, true, $cm);
    $context = context_module::instance($cm->id);
    require_capability('mod/url:view', $context);

    add_to_log($course->id, 'url', 'view', 'view.php?id='.$cm->id, $url->id, $cm->id);

    $PAGE->set_url('/mod/url/view.php', array('id' => $cm->id));
    $PAGE->set_title($course->shortname.': '.$url->name);
    $PAGE->set_heading($course->fullname);
    //$PAGE->set_activity_record($url);
    echo $OUTPUT->header();

    $repository = optional_param('dir', 'manuales', PARAM_PATH);
    $folder = optional_param('sub', '', PARAM_PATH);

    $path = 'repository/'.$repository.'/'.$folder;

    $filenames = get_directory_list($CFG->dataroot."/$path", '', false);

    if (!$filenames) {
        echo $OUTPUT->heading(get_string("nofilesyet"));
        echo $OUTPUT->footer($course);
        exit;
    }

    //now perform name translation
    $files = array();
    foreach($filenames as $key=> $file) {
        $name = $file;
        $files[$file] = $name;
        if(($codes = explode('-', $file)) and (count($codes)>2))  {
            $shortname = $codes[2];
            //$select = " ( idnumber LIKE '%$subcodes[1]%' ) AND  (idnumber LIKE '%$codes[2]%' ) ";
            //if($names = $DB->get_records_select('course', $select, null, '', 'fullname')) {
         // JoseLuis 09/10/12: si el shortname tiene duplicados, no encuentra los sufijos, buscar con like
         // if($name = $DB->get_field('course', 'fullname', array('shortname'=>$shortname))) {
            if($name = $DB->get_field_select('course', 'fullname', "shortname like '$shortname%'")) {
                //$temp = array_shift($names);
                //$name = $temp->fullname;
                $files[$file] = $name;
            }
        }
    }
    natcasesort($files);

    $strftime = get_string('strftimedatetime');
    $strname = get_string("name");
    $strsize = get_string("size");
    $strmodified = get_string("modified");
    $strfolder = get_string("folder");
    $strfile = get_string("file");

    echo '<table cellpadding="4" cellspacing="1" class="files" summary="">';
    echo "<tr><th class=\"header name\" scope=\"col\">$strname</th>".
         "<th align=\"right\" colspan=\"2\" class=\"header size\" scope=\"col\">$strsize</th>".
         "<th align=\"right\" class=\"header date\" scope=\"col\">$strmodified</th>".
         "</tr>";
    foreach ($files as $file=>$name) {
        if (is_dir($CFG->dataroot."/$path/$file")) {          // Must be a directory
            $icon = "folder.gif";
            $relativeurl = "/view.php?blah";
            $filesize = display_size(get_directory_size($CFG->dataroot."/$path/$file"));

        } else {
            $icon = mimeinfo("icon", $file);
            $relativeurl = new moodle_url("/local/ulpgccore/repofile.php/$path/$file");
            $filesize = display_size(filesize($CFG->dataroot."/$path/$file"));
        }

        if ($icon == 'folder.gif') {
            echo '<tr class="folder">';
            echo '<td class="name">';
            echo "<a href=\"view.php?id={$cm->id}&amp;subdir=$subdir/$file\">";
            echo "<img src=\"$CFG->pixpath/f/$icon\" class=\"icon\" alt=\"$strfolder\" />&nbsp;$file</a>";
        } else {
            echo '<tr class="file">';
            echo '<td class="name">';
            echo $OUTPUT->action_link($relativeurl, $name);
            //link_to_popup_window($relativeurl, "resourcedirectory{$resource->id}", "<img src=\"$CFG->pixpath/f/$icon\" class=\"icon\" alt=\"$strfile\" />&nbsp;$name", 450, 600, '');
        }
        echo '</td>';
        echo '<td>&nbsp;</td>';
        echo '<td align="right" class="size">';
        echo $filesize;
        echo '</td>';
        echo '<td align="right" class="date">';
        echo userdate(filemtime("$CFG->dataroot/$path/$file"), $strftime);
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';

    //print_simple_box_end();

    echo $OUTPUT->footer();
