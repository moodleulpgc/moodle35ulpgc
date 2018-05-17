<?php
/*******************************************************************
* File: lib.php within block usermanagement
*
* @author Enrique Castro
* @version  0.95
*
* Copyright (c) 2008 Enrique Castro <ecastro@dbbf.ulpgc.es>
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of the
* License, or (at your option) any later version.
*
******************************************************************/

    require_once($CFG->dirroot.'/config.php');




    // use report for a single user
    function usermanagement_usereports_single($user, $categories, $returnarray=false) {
        global $CFG, $DB;

        if(!$user) {
          notify('no user');
          return false;
        }

        $config = get_config('block_usermanagement');
        $checkedroles = explode(',', $config->checkedroles);
        list($rolesql, $roleparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role_');

        $userid = $user->id;
        $report = array();
        if($courses = get_my_enroled_courses($user->id, '', false, $categories)){
            $select = " userid= :userid AND roleid $rolesql AND component='enrol_miulpgc' ";
            $params = array('userid'=>$user->id);
            $delcourses = array();
            foreach($courses as $course) {
                $context = context_course::instance($course->id);
                $params['contextid'] = $context->id;
                $assigned = $DB->record_exists_select('role_assignments', $select." AND contextid = :contextid ", $params);
                if(!$assigned OR ($course->credits <= 0) ) {
                    // do not count course if not enroled directly
                    // do not count course without credit charge (= non-official)
                    $delcourses[] = $course->id;
                }
            }

            if($delcourses) {
                foreach($delcourses as $del) {
                    unset($courses[$del]);
                }
            }

            if(!$courses) {
                return false;
            }

            $report['name'] = fullname($user, true, 'lastname firstname');
            $report['idnumber'] = $user->idnumber;
            $report['courses'] = array();
            $courseids = implode(',', array_keys($courses));

            /// calculate dialogue entries
            $sql = "SELECT d.course, COUNT(1) AS entries
                      FROM {dialogue_entries} de
                        JOIN {dialogue} d ON d.id = de.dialogueid AND d.course IN ( $courseids )
                      WHERE de.userid='$userid' GROUP BY d.course ";
            $dialogues = $DB->get_records_sql($sql);

            /// calculate forum posts
            $sql = "SELECT fd.course, COUNT(1) AS posts
                      FROM {forum_posts} fp
                        JOIN {forum_discussions} fd ON fd.id = fp.discussion AND fd.course IN ( $courseids )
                      WHERE fp.userid='$userid' GROUP BY fd.course ";
            $posts = $DB->get_records_sql($sql);

            /// calculate assignment califications
            $sql = "SELECT a.course, COUNT(1) AS subs
                      FROM {assign_grades sub
                        JOIN {assign} a ON a.id = sub.assignment AND a.course IN ( $courseids )
                      WHERE sub.grader='$userid' GROUP BY a.course ";
            $assignments = $DB->get_records_sql($sql);

            foreach($courses as $course){
              if($course->credits < 0 ) {
                continue; // do not count courses without credit charge (= non-official)
              }
              $data = array();
              $data['shortname'] = $course->shortname;
              $data['fullname'] = $course->fullname;
              $data['dialogues'] = 0;
              if(isset( $dialogues[$course->id])) {
                  $data['dialogues'] = $dialogues[$course->id]->entries;
              }
              $data['posts'] = 0;
              if(isset($posts[$course->id])) {
                  $data['posts'] = $posts[$course->id]->posts;
              }
              $data['assignments'] = 0;
              if(isset($assignments[$course->id])) {
                  $data['assignments'] = $assignments[$course->id]->subs;
              }
              $report['courses'][$course->id] = $data;
            }
        }

        if($returnarray) {
          return array("$user->idnumber" => $report);
        }
        return $report;
    }

    // use report for multiple users
    function usermanagement_usereports_multiple($categories) {
        global $CFG, $DB;

        $reports = array();

        $sql = "SELECT uu.userid AS id, u.firstname, u.lastname, u.idnumber, u.institution, u.department
                FROM {user_ulpgc} uu
                  JOIN {user} u ON uu.userid = u.id ORDER BY u.lastname ASC";
        if(!$userlist = $DB->get_recordset_sql($sql)) {
          notify('no ULPGC users');
          return false;
        }

        foreach($userlist as $user) {
            if($userreport = usermanagement_usereports_single($user, $categories)) {
                $reports[$user->id] = $userreport;
            }
        }
        $userlist->close();
        return $reports;
    }


    // mold user report  array into output format
    function usermanagement_usereports_format($reports, $format, $template='') {
        global $CFG, $SITE;


        $date = date('Y-m-d-Hi');
        $struserreports = get_string('usereports', 'block_usermanagement');
        $filename = clean_filename($SITE->shortname.'_'.$struserreports.'_'.$date.'.'.$format);
        $headers = array('name' => get_string('fullname'),
                          'idnumber' => get_string('idnumber'),
                          'courses' =>  get_string('courses'),
                          'dialogues' => get_string('messages','block_usermanagement').' ('.get_string('modulenameplural', 'dialogue').')',
                          'forums' => get_string('messages', 'block_usermanagement').' ('.get_string('modulenameplural', 'forum').')',
                          'assignments' => get_string('assesments', 'block_usermanagement').' ('.get_string('modulenameplural', 'assignment').')' );

        if(!$reports OR !$format) {
          return 'nothing to show';
        }

        switch($format) {
          case 'html' :
                        $table = new Object();
                        $table->head = $headers;
                        $table->align = array('left', 'left', 'left', 'center', 'center', 'center');
                        $table->width = '95%';
                        $table->tablealign = 'center';
                        $table->summary = get_string('usereports','block_usermanagement');
                        $table->data = array();
                        $sep = ' ';
                        foreach($reports as $report) {
                            $row = array();
                            $row[] = $report['name'];
                            $row[] = $report['idnumber'];
                            $courses = $report['courses'];
                            $cnames = array();
                            $dialogues = array();
                            $posts = array();
                            $assigns = array();
                            foreach($courses as $course) {
                                $cnames[] = '<div class="boxaligncenter centerpara" >'.$course['shortname'].'</div>';
                                $dialogues[] = '<div class="boxaligncenter centerpara" >'.$course['dialogues'].'</div>';
                                $posts[] = '<div class="boxaligncenter centerpara" >'.$course['posts'].'</div>';
                                $assigns[] = '<div class="boxaligncenter centerpara" >'.$course['assignments'].'</div>';

                            }
                            $row[] = implode($sep, $cnames);
                            $row[] = implode($sep, $dialogues);
                            $row[] = implode($sep, $posts);
                            $row[] = implode($sep, $assigns);
                            $table->data[] = $row;
                            unset($row);
                        }

                        print_table($table);
                        break;

          case 'xls' :
          case 'ods' :
                    /// Creating a workbook
                        if($format == 'xls') {
                            require_once($CFG->dirroot.'/lib/excellib.class.php');
                            $workbook = new MoodleExcelWorkbook("-");
                        } else {
                            require_once($CFG->dirroot.'/lib/odslib.class.php');
                            $workbook = new MoodleODSWorkbook("-");
                        }
                    /// Sending HTTP headers
                        $workbook->send($filename);
                    /// Adding the worksheet
                        $myxls =& $workbook->add_worksheet($struserreports);

                    /// Print names of all the fields
                        $column = 0;
                        foreach($headers as $field) {
                            $myxls->write_string(0,$column,$field);
                            $column +=1;
                        }
                        $row = 1;
                        foreach($reports as $report) {
                            $myxls->write_string($row,0, $report['name']);
                            $myxls->write_string($row,1, $report['idnumber']);
                            $courses = $report['courses'];
                            foreach($courses as $course) {
                                $myxls->write_string($row,2, $course['shortname']);
                                $myxls->write_string($row,3, $course['dialogues']);
                                $myxls->write_string($row,4, $course['posts']);
                                $myxls->write_string($row,5, $course['assignments']);
                                $row +=1;
                            }

                        }
                    /// Close the workbook
                        $workbook->close();
                        exit;
                        break;

          case 'csv' :
                        $separator = "\t";
                    /// Print header to force download
                        @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
                        @header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
                        @header('Pragma: no-cache');
                        header("Content-Type: application/download\n");
                        header("Content-Disposition: attachment; filename=\"$filename\"");

                    /// Print names of all the fields
                        $column = 0;
                        echo implode($separator, $headers);
                        echo "\n";

                        foreach($reports as $report) {
                            $row = $report['name'].$separator.$report['idnumber'];
                            $courses = $report['courses'];
                            $row .= $separator.implode($separator, $courses);
                        }
                        echo "\n";
                        exit;
                        break;

          case 'pdf' :
                        include_once($CFG->libdir.'/pdflib.php');
                        if($template) {
                            $contents = file_get_contents($CFG->dataroot.'/1/'.$template);
                        } else {
                           $contents = get_string('templatexample','block_usermanagement');
                        }

                        $pdf = new pdf;
                        $pdf->print_header = true;
                        $pdf->print_footer = false;
                        $pdf->SetMargins(10, 25, 20);
                        $pdf->SetAutoPageBreak(true, 15);
                        $pdf->setHeaderData('', 20);            // ($ln="", $lw=0, $ht="", $hs="")


                        $table = new Object();
                        $table->head = array('courses' =>  get_string('courses'),
                                                'dialogues' => get_string('messages','block_usermanagement').' ('.get_string('modulenameplural', 'dialogue').')',
                                                'forums' => get_string('messages', 'block_usermanagement').' ('.get_string('modulenameplural', 'forum').')',
                                                'assignments' => get_string('assesments', 'block_usermanagement').' ('.get_string('modulenameplural', 'assignment').')' );
                        $table->align = array('left', 'center', 'center', 'center');
                        $table->width = '95%';
                        $table->tablealign = 'center';
                        $table->summary = get_string('usereports','block_usermanagement');
                        $sep = '<br />';
                        foreach($reports as $report) {
                            $pdf->AddPage();
                            $table->data = array();
                            $text = ($contents);
                            $text = str_replace('%%NOMBRE%%', $report['name'], $text);
                            $text = str_replace('%%DNI%%', $report['idnumber'], $text);
                            $courses = $report['courses'];
                            $cnames = array();
                            $dialogues = array();
                            $posts = array();
                            $assigns = array();
                            foreach($courses as $course) {
                                $row = array();
                                $row[] = $course['shortname'];
                                $row[] = $course['dialogues'];
                                $row[] = $course['posts'];
                                $row[] = $course['assignments'];
                                $table->data[] = $row;
                                unset($row);
                            }
                            $tabletext = print_table($table, true);
                            $text = str_replace('%%TABLA%%', $tabletext, $text);
                            $pdf->WriteHTML($text);
                        }
                        $pdf->Output('test.pdf', 'I');
                        die;
                        break;
        }

    }





    // code from block useradmin
    /**
     * Get auth plugins available and used by some active user
     * @return array of plugin instance, keyed by $authtype
     */
    function usermanagement_get_available_auth_plugins() {
        global $CFG, $DB;
        // Get auth used by any user (retrieve only auth field from user table)
        $usedauths = $DB->get_records_sql("select distinct auth from {user} where deleted = 0");
        // get currently installed and enabled auth plugins
        $authsavailable = get_list_of_plugins('auth', 'tests');
        // Load all plugins
        $authplugins = array();
        foreach ($authsavailable as $auth) {
            $authplugin = get_auth_plugin($auth);
            if ( array_key_exists($authplugin->authtype, $usedauths)) {
                $authplugins[$authplugin->authtype] = $authplugin;
            }
        }
        return $authplugins;
    }

    /**
     * Similar to optional_param() but returns $previousvalue if param is not set at all,
     * and returns $clearvalue if param is set to empty string
     */
    function usermanagement_optional_param_clearing($paramname, $previousvalue=NULL, $clearvalue=NULL, $type=PARAM_CLEAN ) {
        // detect_unchecked_vars addition
        global $CFG;
        if (!empty($CFG->detect_unchecked_vars)) {
            global $UNCHECKED_VARS;
            unset ($UNCHECKED_VARS->vars[$paramname]);
        }

        // if is empty string, return clear value
        if ( array_key_exists($paramname, $_REQUEST) && $_REQUEST[$paramname] === '' ) {
            $param = $clearvalue;
        }
        // If not set at all, use previous value
        else if ( !array_key_exists($paramname, $_REQUEST) ) {
            $param = $previousvalue;
        }
        // Else use request
        else {
            $param = $_REQUEST[$paramname];
        }

        return clean_param($param, $type);
    }

    /**
     * Execute paged query on Users
     * In parameter $searchcount (passed by reference) returns the count of the users
     * retrieved by the query, WITHOUT taking account of paging
     * @return array of users
     */
    function usermanagement_get_manageusers_listing($tableview, $sqluserfiltering, &$searchcount, &$foundcount, $sort='lastaccess', $dir='ASC', $firstinitial='', $lastinitial='', $page=0, $recordsperpage=99999) {
        global $CFG, $DB, $SESSION;

        $userlist = $DB->get_records_select('user', $sqluserfiltering[0], $sqluserfiltering[1], '', 'id, idnumber', 0, MAX_BULK_USERS);
        $foundcount = is_array($userlist) ? count($userlist) : 0;

        $getusers = array();
        if($tableview == 'viewfilter') {
            if(is_array($userlist)) {
                $usersids = array_keys($userlist);
            }
        } else {
            if(is_array($SESSION->bulk_users)) {
                $usersids = array_keys($SESSION->bulk_users);
            }
        }

        $selectnousers = " u.id <> 1 AND u.deleted <> 1 AND u.username <> 'changeme' AND u.username <> 'guest' ";
        $selectlist = "u.*, mh.name AS mnethostname, mh.wwwroot AS mnethostwwwroot";

        $LIKE      = ' LIKE '; //sql_ilike(); // hack rápido sólo para MySQL
        //$fullname  = sql_fullname();

        $from = "{$CFG->prefix}user u, {$CFG->prefix}mnet_host mh";

        $where = "(u.mnethostid = mh.id OR u.mnethostid IS NULL)
                    AND $selectnousers ";

        if(!empty($usersids) && is_array($usersids)) {
            $list = "'".implode("', '", $usersids)."'";
            $where .= "AND u.id IN ( $list ) ";
        } else {
            return array();
        }

        if ($firstinitial) {
            $where .= ' AND u.firstname '. $LIKE .' \''. $firstinitial .'%\' ';
        }
        if ($lastinitial) {
            $where .= ' AND u.lastname '. $LIKE .' \''. $lastinitial .'%\' ';
        }
        if ($sort) {
            $sort = ' ORDER BY '. $sort .' '. $dir;
        }


        // SQL for paged query
        $sql = "SELECT $selectlist FROM $from WHERE $where  $sort"; // $limit ";


        // SQL for count query, w/o paging limit
        $sqlcount = "SELECT count(*) FROM $from WHERE $where ";


        // Execute Count query first
        $searchcount = $DB->count_records_sql($sqlcount);

        // Execute full (paged) query
        $users = $DB->get_records_sql($sql, null, $page, $recordsperpage);

        return $users;
    }


    /**
     * Returns an optionally collapsable text
     * If collapsed, text is replaced by ellipses with alt-text (if available) or full text,
     * as tooltip
     */
    function usermanagement_collapsable_text($text, $showfull = TRUE, $alttext = NULL) {
        // If string is empty, return empty string
        if ( !$text  ){
            return '';
        }
        // return full text
        else if ( $showfull ) {
            return $text;
        }
        // return ellipsed text
        else {
            $tooltiptext = ($alttext)?(s($alttext)):(s($text));
            return "<a class=\"tooltip\" hrep=\"#\" >...<span>$tooltiptext</span></a>";
        }
    }


    /**
     * Prints several popupforms for module instance selection
     *
     *
     */
    function usermanagement_print_modinstance_select($baseurl, $categoryid, $courseid, $modid, $modname) {
        global $DB;

        print_box_start();
        $categories = make_categories_options();
        echo '<div class="categories" >';
        echo get_string('category').':&nbsp;';
        popup_form($baseurl.'?cat=', $categories, 'categoryselect', $categoryid);
        echo '</div>';
        echo '<div class="courses" >';
        $courses = array();
        if($categoryid) {
            $courses = $DB->get_records_menu('course', array('category'=>$categoryid), '', 'id, fullname');
        }
        echo get_string('courses').':&nbsp;';
        popup_form($baseurl."?cat=$categoryid&amp;c=", $courses, 'courseselect', $courseid);
        echo '</div>';
        echo '<div class="trackers" >';
        $instances = array();
        if($courseid) {
            $instances = $DB->get_records_menu($modname, array('course'=>$courseid), '', 'id, name');
        }
        echo get_string('modulename', $modname).':&nbsp;';
        popup_form($baseurl."?cat=$categoryid&amp;c=$courseid&amp;mod=", $instances, 'instanceselect', $modid);
        echo '</div>';
        print_box_end();
        echo '<br />';
    }


    /**
     * Prints part of a form in bulk users actions
     * Select directory with user-paired files, & userfile identification format
     *
    */
    function usermanagement_print_usersdirectory_config($courseid, $usersfilesdir, $fileprefix, $filesuffix, $userfield) {
        global $CFG;

        print_box_start();
        $coursedirs = array();
        if($courseid) {
            $dirs = get_directory_list($CFG->dataroot . '/' . $courseid, array($CFG->moddata, 'backupdata', '_thumb'), true, true, false);
            $coursedirs = array();
            foreach ($dirs as $dir) {
                $coursedirs[$dir] = $dir;
            }
        }
        echo get_string('userattachmentsdir', 'block_usermanagement').'&nbsp;';
        choose_from_menu($coursedirs, 'dir', $usersfilesdir, 'choose', '', '' );
        echo '<br />';
        echo get_string('userfilenamehelp', 'block_usermanagement');
        echo '<br />';
        echo get_string('fileprefix', 'block_usermanagement').'&nbsp;';
        echo '<input type="text" name="prefix" size="6" value="'.$fileprefix.'">&nbsp;';
        echo get_string('userfield', 'block_usermanagement').'&nbsp;';
        $fields = array('userid' => get_string('id'),
                        'idnumber' => get_string('idnumber'),
                        'username' => get_string('username'),
                        'fullname' => get_string('fullname'));
        choose_from_menu($fields, 'ufield', $userfield, '');

        echo get_string('filesuffix', 'block_usermanagement').'&nbsp;';
        echo '<input type="text" name="suffix" size="6" value="'.$filesuffix.'">';
        echo '<br />';
        echo '<input type="checkbox" name="needuserfile" value="1">&nbsp;';
        echo get_string('needuserfile', 'block_usermanagement');
        print_box_end();
        echo '<br />';
    }


?>
