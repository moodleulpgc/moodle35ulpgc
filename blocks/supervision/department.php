<?php
      // Displays list of courses of a given department
      // No editing neither rearranging
      // Based on: category.php,v 1.119.2.12 2008/12/11 09:21:53 tjhunt Exp $
      // ULPGC ecastro

    require_once("../config.php");
    //require_once("lib.php");

    $id = required_param('id', PARAM_INT);          // department id
    $page = optional_param('page', 0, PARAM_INT);     // which page to show
    $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT); // how many per page

    $baseparams = array('id' => $id,
                        'perpage' => $perpage,
                        'page' => $page
                        );

    $baseurl = new moodle_url('/blocks/supervision/editholidays.php', $baseparams);

    if ($CFG->forcelogin) {
        require_login();
    }

    if (!$site = get_site()) {
        print_error('Site isn\'t defined!');
    }

    if (empty($id)) {
        print_error("Department not known!");
    }

    if (!$department = get_record("departamentos", "id", $id)) {
        print_error("Department not known!");
    }


/// Print headings

    $stradministration = get_string('administration');
    $strdepartments = get_string('departments');
    $strdepartment = get_string('department');
    $strcourses = get_string('courses');

    $PAGE->navbar->add(get_string('management', 'block_supervision'), $baseurl);
    $PAGE->navbar->add($title, null);

    $navlinks = array();
    $navlinks[] = array('name' => $strdepartment, 'link' => '', 'type' => 'misc');
    $navlinks[] = array('name' => format_string($department->departamento), 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header("$site->shortname: $department->departamento", "$site->fullname: $strcourses", $navigation, '', '', true);


/// Print out all the courses
    $totalcount = count_records('course', 'coddept', $department->codigo);
    $courses = get_records('course', 'coddept', $department->codigo, 'fullname ASC',
            'id, sortorder, shortname, fullname, summary, visible, teacher, guest, password',
            $page*$perpage, $perpage);
    $numcourses = count($courses);

    if (!$courses) {
        if (empty($subdepartmentswereshown)) {
            print_heading(get_string("nocoursesyet"));
        }

    } else {
        print_paging_bar($totalcount, $page, $perpage, "category.php?id=$department->id&amp;perpage=$perpage&amp;");

        $strcourses = get_string('courses');
        $strsummary = get_string('summary');

        echo '<table border="0" cellspacing="2" cellpadding="4" class="generalbox boxaligncenter"><tr>';
        echo '<th class="header" scope="col">'.$strcourses.'</th>';
        echo '<th class="header" scope="col">&nbsp;</th>';
        echo '</tr>';

        $count = 0;

        // Checking if we are at the first or at the last page, to allow courses to
        // be moved up and down beyond the paging border
        if ($totalcount > $perpage) {
            $atfirstpage = ($page == 0);
            if ($perpage > 0) {
                $atlastpage = (($page + 1) == ceil($totalcount / $perpage));
            } else {
                $atlastpage = true;
            }
        } else {
            $atfirstpage = true;
            $atlastpage = true;
        }

        $spacer = '<img src="'.$CFG->wwwroot.'/pix/spacer.gif" class="iconsmall" alt="" /> ';
        foreach ($courses as $acourse) {
            if (isset($acourse->context)) {
                $coursecontext = $acourse->context;
            } else {
                $coursecontext = context_course::instance($acourse->id);
            }

            $count++;
            $up = ($count > 1 || !$atfirstpage);
            $down = ($count < $numcourses || !$atlastpage);

            $linkcss = $acourse->visible ? '' : ' class="dimmed" ';
            echo '<tr>';
            echo '<td><a '.$linkcss.' href="../course/view.php?id='.$acourse->id.'">'. format_string($acourse->fullname) .'</a></td>';

            echo '<td align="right">';
            if (!empty($acourse->guest)) {
                echo '<a href="view.php?id='.$acourse->id.'"><img title="'.
                        $strallowguests.'" class="icon" src="'.
                        $CFG->pixpath.'/i/guest.gif" alt="'.$strallowguests.'" /></a>';
            }
            if (!empty($acourse->password)) {
                echo '<a href="view.php?id='.$acourse->id.'"><img title="'.
                        $strrequireskey.'" class="icon" src="'.
                        $CFG->pixpath.'/i/key.gif" alt="'.$strrequireskey.'" /></a>';
            }
            if (!empty($acourse->summary)) {
                link_to_popup_window ("/course/info.php?id=$acourse->id", "courseinfo",
                                        '<img alt="'.get_string('info').'" class="icon" src="'.$CFG->pixpath.'/i/info.gif" />',
                                        400, 500, $strsummary);
            }
            echo "</td>";

            echo "</tr>";
        }

        echo '</table>';
        echo '</div></form>';
        echo '<br />';
    }

    print_course_search();

    print_footer();

?>
