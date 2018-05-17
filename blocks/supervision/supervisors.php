<?php

/**
 * This file contains a block_supervision page
 *
 * @package   block_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->dirroot."/blocks/supervision/locallib.php");
    require_once($CFG->dirroot."/blocks/supervision/editpermissionform.php");
    require_once($CFG->libdir.'/tablelib.php');


    $cid = required_param('cid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
    $pagetype = required_param('type', PARAM_ALPHA);
    $departmentassign = optional_param('department', '0', PARAM_INT);
    $itemid       = optional_param('item', 0, PARAM_INT);
    $page         = optional_param('page', 0, PARAM_INT);                     // which page to show
    $perpage      = optional_param('perpage', 25, PARAM_INT);

    $baseparams = array('cid' => $cid,
                        'type' => $pagetype,
                        'department' => $departmentassign,
                        'item' => $itemid,
                        'page' => $page,
                        'perpage' => $perpage,
                        );

    $baseurl = new moodle_url('/blocks/supervision/supervisors.php', $baseparams);

    // Force user login in course (SITE or Course)
    if ($course->id == SITEID) {
        require_login();
    } else {
        require_login($course);
    }
    if ($course->id == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($course->id);
    }

    $config = get_config('block_supervision');

    if(!$canadd = supervision_can_add_supervisors($USER->id)) {
        require_capability('block/supervision:manage', $context);
    }
    $canmanage = has_capability('block/supervision:manage', $context);

    if(!$departmentassign) {
        $title = get_string('bycategory', 'block_supervision');
    } else {
        $title = get_string('bydepartment', 'block_supervision');
    }

    $PAGE->set_context($context);
    $PAGE->set_url($baseurl);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);
    $PAGE->navbar->add(get_string('management', 'block_supervision'), $baseurl);
    $PAGE->navbar->add($title, null);

    /// confirm delete supervision record
    $delete = optional_param('del', 0, PARAM_INT);
    $delete = $DB->get_record('supervision_permissions', array('id'=>$delete));
    if($delete) {
        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        $delay = 5;
        if(!$confirm) {
            $PAGE->navbar->add(get_string('deletepermission', 'block_supervision'), null);
            echo $OUTPUT->header();
            $confirmurl = new moodle_url($baseurl, array('del' => $delete->id, 'confirm' => 1));
            $data = new StdClass;
            $data->name = supervision_format_instancename($delete);
            $fields = get_all_user_name_fields(true, '');
            $user = $DB->get_record('user', array('id'=>$delete->userid), 'id, username, idnumber, email,'.$fields);
            $data->user = fullname($user);
            $message = get_string('deletepermission_confirm', 'block_supervision', $data);
            echo $OUTPUT->confirm($message, $confirmurl, $baseurl);
            echo $OUTPUT->footer();
            die;
        } else if(confirm_sesskey()){
            /// confirmed, proceed with deletion
            if ($DB->delete_records('supervision_permissions', array('id'=>$delete->id))) {
                supervision_assign_supervisor($delete, true);
                redirect($baseurl, get_string('changessaved'), $delay);
            }
        }
    }

    /// create/edit supervision record
    $edit = optional_param('edit', 0, PARAM_INT);
    if($edit) {

        $mform = new supervision_editpermission_form(null, array('params' => $baseparams, 'canmanage'=>$canmanage, 'canadd'=>$canadd, 'edit'=>$edit, 'config'=>$config) );
        $permission = false;
        if($edit > 0) {
            if($permission = $DB->get_record('supervision_permissions', array('id' => $edit))) {
                $mform->set_data($permission);
            }
        }

        if ($mform->is_cancelled()) {
            redirect($baseurl);
        } elseif ($formdata = $mform->get_data()) {
            /// process form & store permission in database
            $data = new StdClass;
            $data->userid = $formdata->userid;
            $data->scope = $formdata->scope;
            $data->instance = $formdata->instance;
            $data->review = $formdata->review;
            $data->warnings = implode(',',$formdata->warnings);
            $data->assigner = $formdata->assigner;
            $data->adduser = $formdata->adduser;
            $data->timemodified = time();
            if($permission) { // this means edit > 0 and record exists, over-write & update
                $data->id = $permission->id;
                $DB->update_record('supervision_permissions', $data);
                supervision_assign_supervisor($data);
            } else {
                if($existing = $DB->record_exists('supervision_permissions', array('userid'=>$data->userid, 'scope'=>$data->scope, 'instance'=>$data->instance))) {
                    redirect($baseurl, get_string('permissionexists', 'block_supervision'));
                }
                $data->id = $DB->insert_record('supervision_permissions', $data);
                supervision_assign_supervisor($data);
            }
            redirect($baseurl, get_string('changessaved'));
        }

        $PAGE->navbar->add(get_string('supervisor', 'block_supervision'), null);
        $PAGE->navbar->add(get_string('addpermission', 'block_supervision'), null);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('editsupervisor', 'block_supervision'));

        $mform->display();

        echo $OUTPUT->footer();

        die;
    }

    /// print the main supervisors table

    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);

    echo '<div class="supervisedscopeform">';
    $itemlist = array(0=>get_string('category'), 1=>get_string('department'));
    $label = get_string('itemscope', 'block_supervision');
    $select = new single_select($baseurl, 'department', $itemlist, $departmentassign,  null, 'scopeform');
    $select->set_label($label.':&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';


    /// print item selector form
    $itemlist = array();
    if($departmentassign) {
        $itemlist =  array (); /// TODO
    } else {
        $itemlist =  array(0=>'any') + make_categories_options();
    }
    echo '<div class="superviseditemform">';
    $select = new single_select($baseurl, 'item', $itemlist, $itemid, null, 'itemsform');
    $label = get_string('itemfilter', 'block_supervision');
    $select->set_label($label.':&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';

    if($canmanage or $canadd) {
        $editurl = new moodle_url($baseurl, array('edit'=>-1));
        echo $OUTPUT->heading(html_writer::link($editurl, get_string('addpermission', 'block_supervision')));
    }

    /// Define a table showing a list of items (categories, departments) and users with supervision permissions in them

    $table = new flexible_table('block-supervision-supervisors-'.$itemid);

    $tablecolumns = array('itemname', 'fullname', 'review', 'warning', 'assigner', 'adduser', 'action');
    $tableheaders = array(get_string('itemname', 'block_supervision'),
                            get_string('fullnameuser'),
                            get_string('review', 'block_supervision'),
                            get_string('supervisionwarnings', 'block_supervision'),
                            get_string('assigner', 'block_supervision'),
                            get_string('adduser', 'block_supervision'),
                            get_string('action'));
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl->out());

    $table->sortable(true, 'itemname', SORT_ASC);

    //$table->no_sorting('review');
    $table->no_sorting('warning');
    $table->no_sorting('action');

    //$table->column_style('review', 'align', 'center');
    $table->set_attribute('id', 'supervisors');
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('class', 'generaltable generalbox supervisorstable');

    $table->setup();


    $params = array();
    $fields = get_all_user_name_fields(true, 'u');
    $select = "SELECT sp.*, u.username, u.idnumber, u.email, $fields, ";
    $from = " FROM {supervision_permissions} sp
                JOIN {user} u ON sp.userid = u.id ";
    if($departmentassign) {
        $scope = 'department';
        $select .= " u.name AS itemname ";
        $from .= "\n JOIN {local_sinculpgc_units} u ON sp.instance = u.id AND u.type = '".SINCULPGC_UNITS_DEPARTMENT."'";
    } else {
        $scope = 'category';
        $select .= " cc.name AS itemname ";
        $from .= "\n JOIN {course_categories} cc ON sp.instance = cc.id ";
    }

    $where = "WHERE sp.scope = :scope ";
    $params['scope'] = $scope;
    if($itemid) {
        $where .= " AND sp.instance = :item ";
        $params['item'] = $itemid;
    }
    if($canadd) {
        $where .= " AND sp.assigner = :assigner ";
        $params['assigner'] = $canadd['user'];
    }

    $totalcount = $DB->count_records_sql("SELECT COUNT(sp.id) $from $where", $params);

    $table->initialbars(false);
    $table->pagesize($perpage, $totalcount);

    if ($table->get_sql_sort()) {
        $sort = ' ORDER BY '.$table->get_sql_sort();
    } else {
        $sort = '';
    }

    $supervisions = $DB->get_records_sql("$select $from $where $sort", $params, $table->get_page_start(), $table->get_page_size());

    // ecastro ULPGC to allow name formatting by lastname, firstname
    $sortorder = trim(substr(trim($sort), 8));
    $p = strpos($sortorder, ' ');
    $sortorder = trim(substr($sortorder, 0, $p));
    $nameformat= '';
    if ($sortorder == 'firstname' ) {
        $nameformat = 'firstname lastname';
    } else if ($sortorder == 'lastname' ) {
        $nameformat = 'lastname firstname';
    }

    $target = array('category'=>'/course/index.php',
                    'department'=>'/blocks/supervision/department.php');
    $param = array('category'=>'categoryid',
                    'department'=>'id');
    $yesno = array(get_string('no'), get_string('yes'));
    $stredit   = get_string('edit');
    $strdelete = get_string('delete');
    if($supervisions) {
        foreach($supervisions as $supervision) {
            $data = array();
            $url = new moodle_url($target[$supervision->scope], array($param[$supervision->scope]=>$supervision->instance));
            $data[] = $OUTPUT->action_link($url, $supervision->itemname);
            $url = new moodle_url('/user/view.php', array('id'=>$supervision->userid));
            $data[] = $OUTPUT->action_link($url, fullname($supervision, false, $nameformat));
            $data[] = $yesno[$supervision->review];
            if($supervision->warnings) {
                $warnings = explode(',', $supervision->warnings);
                foreach($warnings as $key=>$warning) {
                    /// TODO perform this with classes ????
                    $warnings[$key] = get_string('pluginname', 'supervisionwarning_'.$warning);
                }
                $data[] = implode('<br />', $warnings);
            } else {
                $data[] = '';
            }
            $assigner = $DB->get_record('user', array('id'=>$supervision->assigner));
            $data[] = fullname($assigner);
            $data[] = $yesno[$supervision->adduser];
            $action = '';
            $buttons = array();
            if($canmanage or $USER->id == (int)$supervision->assigner) {
                $url = new moodle_url($baseurl, $baseparams + array('edit'=>$supervision->id));
                $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
                $url = new moodle_url($baseurl, $baseparams + array('del'=>$supervision->id));
                $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
                //$buttons[] = html_writer::link(new moodle_url($returnurl, array('delete'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
                $action = implode('&nbsp;&nbsp;', $buttons);
            }
            $data[] = $action;

            $table->add_data($data);
        }
        $table->print_html();

    } else {
        echo $OUTPUT->heading(get_string('nothingtodisplay'));
    }

    echo '<br />';
    if ($course->id == SITEID) {
        $url = $CFG->wwwroot.'/my';
        if($pagetype == 'site-index') {
            $url = $CFG->wwwroot.'/?redirect=0';
        }
    } else {
        $url = new moodle_url('/course/view.php', array('id' => $course->id));
    }
    echo $OUTPUT->continue_button($url);

    echo $OUTPUT->footer();


