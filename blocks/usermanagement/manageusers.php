<?php // $Id: manageusers.php

    require_once('../../config.php');
    require_once('locallib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/user/filters/lib.php');
    require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');

    if (!defined('MAX_BULK_USERS')) {
        define('MAX_BULK_USERS', 2000);
    }

    // Login required
    require_login();

    // Requires capabilities to list users
    $systemcontext = context_system::instance();
    require_capability('block/usermanagement:manage', $systemcontext);
    $canmanage = true;

    $caneditupdate = (has_capability('moodle/user:update', $systemcontext) &&
                        has_capability('moodle/user:editprofile', $systemcontext));

    $canconfirm = has_capability('moodle/user:create', $systemcontext);
    $candelete = has_capability('moodle/user:delete', $systemcontext);
    $canenrol = has_capability('moodle/role:assign', $systemcontext);
    $canseedetails = has_capability('moodle/user:viewhiddendetails', $systemcontext);
    $cansend =  has_capability('moodle/course:bulkmessaging', $systemcontext);
	$cancreate =  has_capability('moodle/course:create', $systemcontext);

    if (empty($CFG->loginhttps)) {
        $securewwwroot = $CFG->wwwroot;
    } else {
        $securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
    }

    if (! $site = get_site()) {
        print_error("Could not find site-level course");
    }

    // Setup MNET enviromnent, if needed
    if (!isset($CFG->mnet_localhost_id)) {
        include_once $CFG->dirroot . '/mnet/lib.php';
        $env = new mnet_environment();
        $env->init();
        unset($env);
    }

    // Let's see if we have *any* mnet users. Just ask for a single record
    $mnet_users = $DB->get_records_select('user', " mnethostid != :hostid ", array('hostid'=>$CFG->mnet_localhost_id), '', '*', '0', '1');
    if(is_array($mnet_users) && count($mnet_users) > 0) {
        $mnet_auth_users_exists = true;
    } else {
        $mnet_auth_users_exists = false;
    }

    // Load all available auth plugins (array by authtype)
    $authplugins = usermanagement_get_available_auth_plugins();

    /// Get funcional parameters

    $newuser       = optional_param('newuser', 0, PARAM_BOOL);
    $delete        = optional_param('delete', 0, PARAM_INT);
    $confirm       = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
    $confirmuser   = optional_param('confirmuser', 0, PARAM_INT);
    $acl           = optional_param('acl', '0', PARAM_INT);           // id of user to tweak mnet ACL (requires $access)

    /// Get filter parameters from request

    unset($filterparams);
    $filterparams = array();
    if (isset($USER->manageusers_filterparams)) {
        $filterparams = $USER->manageusers_filterparams;
    }

    // Filter params Defaults
    $filterparams_default = array ( 'sort' => 'lastname', 'dir' => 'ASC',
                                    'page' => 0, 'perpage' => 25,
                                    'lastinitial' => '', 'firstinitial' => '');

    // Intilalize filters defaults
    foreach ($filterparams_default as $paramname =>$paramvalue) {
        if ( !isset($filterparams[$paramname]) )
        $filterparams[$paramname] = $paramvalue;
    }

    $clearallfilters = optional_param('clearallfilters', 0, PARAM_BOOL);
    // Clear all filters if needed (load defaults)
    if ( $clearallfilters ) {
        $sort = $filterparams_default['sort'];
        $page = $filterparams_default['page'];
        $lastinitial = $filterparams_default['lastinitial'];
        $firstinitial = $filterparams_default['firstinitial'];
    }
    // Get filter parameters
    else {
        $page         = usermanagement_optional_param_clearing('page', $filterparams['page'], 0, PARAM_INT);
        $lastinitial  = usermanagement_optional_param_clearing('lastinitial', $filterparams['lastinitial'], '', PARAM_CLEAN);
        $firstinitial = usermanagement_optional_param_clearing('firstinitial', $filterparams['firstinitial'], '', PARAM_CLEAN);
    }

    // Update $filterparams to save in Session, later
    $filterparams['page'] = $page;
    $filterparams['lastinitial'] = $lastinitial;
    $filterparams['firstinitial'] = $firstinitial;

    /// Get sorting and paging parameters from request
    $dir          = usermanagement_optional_param_clearing('dir', $filterparams['dir'],'ASC', PARAM_ALPHA);
    $sort         = usermanagement_optional_param_clearing('sort', $filterparams['sort'], 'firstname', PARAM_ALPHA);
    $perpage      = usermanagement_optional_param_clearing('perpage', $filterparams['perpage'], 25, PARAM_INT);        // how many per page

    // Sort by "name" means by "firstname"
    if ($sort == "name") {
        $sort = "lastname";
    }

    $filterparams['sort'] = $sort;
    $filterparams['dir'] = $dir;
    $filterparams['perpage'] = $perpage;

    $tableview = optional_param('tableview', 'viewfilter', PARAM_ALPHA);
    $usersmarked = array();
    $selectionaction = optional_param('selectionaction', 'none', PARAM_ALPHA);

    $baseurl = new moodle_url('/blocks/usermanagement/manageusers.php');

    $context = context_course::instance(SITEID);

    $PAGE->set_context($context);
    $PAGE->set_url($baseurl);


    /// Bulk_users selection management
    if(($formdata = data_submitted())  && confirm_sesskey()) {
        if(isset($formdata->marked)) {
            $usersmarked = $formdata->marked;
        }

        /// Action dispatcher
        if(isset($formdata->douseractions) && isset($formdata->useraction)) {
            $SESSION->return_url = $baseurl;
            $advuserurl = $CFG->wwwroot.'/'.$CFG->admin.'/tool/advuserbulk/actions';
            $blockurl = $CFG->wwwroot.'/blocks/usermanagement/manageusers_actions';
            switch($formdata->useraction) {
                case 'sendimmessage': redirect($advuserurl.'/message/index.php');
                                    break;
                case 'view'         : redirect($advuserurl.'/display/index.php');
                                    break;
                case 'download'     : redirect($advuserurl.'/download/index.php');
                                    break;
                case 'togglemail'   : redirect($advuserurl.'/emailactive/index.php');
                                    break;
                case 'enrol'        : redirect($advuserurl.'/enroltocourses/index.php');
                                    break;
                case 'unenrol'      : redirect($advuserurl.'/unenrolfromcourses/index.php');
                                    break;
                case 'userconfirm'  : redirect($advuserurl.'/confirm/index.php');
                                    break;
                case 'userdelete'   : redirect($advuserurl.'/delete/index.php');
                                    break;
                case 'assignrole'   : redirect($advuserurl.'/assignroleincourse/index.php');
                                    break;
                case 'unassignrole' : redirect($advuserurl.'/unassignfromcourses/index.php');
                                    break;
                case 'purge'        : redirect($advuserurl.'/purge/index.php');
                                    break;
                case 'forcepassword': redirect($advuserurl.'/forcepasswordchange/index.php');
                                    break;
                // local actions
                case 'sendtracker'  : redirect($blockurl.'/sendtracker/index.php');
                                    break;
                /////////////////AMARTIN
                /*
                 * ACCIONES PARA GESTIONAR MOROSOS
                 */
                case 'todefaulter'		: redirect($blockurl.'/managedefaulter/todefaulter.php');
                                    break;
                case 'fromdefaulter'	: redirect($blockurl.'/managedefaulter/fromdefaulter.php');
                                    break;
                case 'deletedefaulter'  : redirect($blockurl.'/managedefaulter/deletedefaulter.php');
                                    break;
                case 'updatedefaulter'  : redirect($blockurl.'/managedefaulter/updatedefaulter.php');
                                    break;
                /////////////////

            }
        }
    }

    /// Setting up user filterig
    $fieldnames = array('realname'=>0, 'idnumber'=>0, 'courserole'=>0, 'lastname'=>1, 'firstname'=>1, 'email'=>1, 'city'=>1, 'country'=>1,
                    'lastaccess'=>1, 'confirmed'=>1, 'systemrole'=>1, 'userfield' =>1);
    $pageparams = array('tableview' => 'viewfilter', 'sort' => 'lastname', 'dir' => 'ASC');

    $ufiltering = new user_filtering($fieldnames, $baseurl, $pageparams);

    //$guest = get_guest();
    $selectnousers = " id <> 1 AND deleted <> 1 AND username <> 'changeme' AND username <> 'guest' ";
    $sqluserfiltering = $ufiltering->get_sql_filter($selectnousers);

    if(!isset($SESSION->bulk_users) OR $selectionaction == 'delsel') {
        $SESSION->bulk_users = array();
    }

    if($selectionaction == 'add' || $selectionaction == 'del') {
        if($selectionaction == 'add') {
            foreach($usersmarked as $userid=>$set) {
                $SESSION->bulk_users[$userid] = $userid;
            }
        } else {
            foreach($usersmarked as $userid=>$set) {
                unset($SESSION->bulk_users[$userid]);
            }
        }
    }

    if($selectionaction == 'addall' || $selectionaction == 'delall') {
        // we need to get those "all" users from userfiltering
        $usersearchcount = 0;
        $foundcount = 0;
        $users = usermanagement_get_manageusers_listing($tableview, $sqluserfiltering, $usersearchcount, $foundcount, $sort, $dir, $firstinitial, $lastinitial);

        if($selectionaction == 'addall') {
                foreach($users as $userid=>$set) {
                    $SESSION->bulk_users[$userid] = $userid;
                }
        } else {
                foreach($users as $userid=>$set) {
                    unset($SESSION->bulk_users[$userid]);
                }
        }
    }
    unset($users);

    // Get  hide/show fields from Session (if any)

    // Show/Hide fields defaults
    $showfields_defaults = array ( 'idnumber' => 1,
                                    'username' => 0, // Other columns...
                                    'email' => 1,
                                    'institution' => 0,
                                    'department' => 0,
                                    'city' => 0,
                                    'country' => 0,
                                    'lastaccess' => 1,
                                    'auth' => 0,
                                    'mnethostname' => 0,
                                    'confirmed' => 0);

    unset($showfields);
    $showfields = array();
    if ( isset($USER->manageusers_showfields)) {
        $showfields = $USER->manageusers_showfields;
    }


    /// Setup defaults

    // Initialize Hide/show fieldsdefaults
    foreach ($showfields_defaults as $paramname => $paramvalue) {
        if ( !isset($showfields[$paramname]) )
        $showfields[$paramname] = $paramvalue;
    }
    // If any MNET user exists, enable mnethostname field regardless of default
    if ( $mnet_auth_users_exists ) {
        $showfields['mnethostname'] = 1;
    }

    /// Get Show/hide columns params
    // Columns
    $showfields['username'] = optional_param('showusername', $showfields['username'], PARAM_BOOL);
    $showfields['email'] = optional_param('showemail', $showfields['email'], PARAM_BOOL);
    $showfields['institution'] = optional_param('showinstitution', $showfields['institution'], PARAM_BOOL);
    $showfields['department'] = optional_param('showdepartment', $showfields['department'], PARAM_BOOL);
    $showfields['city'] = optional_param('showcity', $showfields['city'], PARAM_BOOL);
    $showfields['country'] = optional_param('showcountry', $showfields['country'], PARAM_BOOL);
    $showfields['lastaccess'] = optional_param('showlastaccess', $showfields['lastaccess'], PARAM_BOOL);
    $showfields['auth'] = optional_param('showauth', $showfields['auth'], PARAM_BOOL);
    $showfields['mnethostname'] = optional_param('showmnethostname', $showfields['mnethostname'], PARAM_BOOL);
    $showfields['confirmed'] = optional_param('showconfirmed', $showfields['confirmed'], PARAM_BOOL);

    // Save filter/search and show/hide params in user Session
    $USER->manageusers_filterparams = $filterparams;
    $USER->manageusers_showfields = $showfields;


    /// Load some language strings
    $stredituser = get_string("edituser");
    $stradministration = get_string("administration");
    $strusers = get_string("users");
    $stredit   = get_string("edit");
    $strdelete = get_string("delete");
    $strconfirm = get_string("confirm");


    /// Page headers
    $struseradmin = get_string('pluginname','block_usermanagement');

    $context = context_course::instance(SITEID);

    $PAGE->set_context($context);
    $PAGE->set_url($baseurl);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title($struseradmin);
    $PAGE->set_heading($struseradmin);
    $PAGE->set_cacheable( true);
    $PAGE->navbar->add(get_string('management', 'block_usermanagement'), $baseurl);
    $PAGE->navbar->add($struseradmin, null);

    echo $OUTPUT->header();
    //print_header($struseradmin, $struseradmin, $navigation);

    if ($confirmuser and confirm_sesskey()) {    /// Confirm the user, after confirmation
        if (!$user = $DB->get_record("user", array("id"=>"$confirmuser"))) {
            print_error("No such user!");
        }

        // Use auth plugin of the user to confirm
        $authplugin = $authplugins[$user->auth];
        // Check if confirm is available with this plugin
        if ( !$authplugin->can_confirm() ) {
            print_error('confirm not available with user\'s auth plugin','block_usermanagement');
        } else {
            $result = $authplugin->user_confirm(addslashes($user->username), addslashes($user->secret));

            if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
                notify(get_string('userconfirmed', '', fullname($user, true)) );
            } else {
                notify(get_string('usernotconfirmed', '', fullname($user, true)));
            }
        }

    } else if ($delete and confirm_sesskey()) {              /// Delete a selected user, after confirmation
        if (!$user = $DB->get_record("user", array("id", "$delete"))) {
            print_error("No such user!");
        }

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            print_error("You are not allowed to delete the primary admin user!");
        }
        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);
            echo $OUTPUT->heading(get_string('deleteuser'));
            $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());
//            notice_yesno(get_string('deletecheckfull', '', "'$fullname'"), "$securewwwroot/$CFG->admin/user.php", 'manageusers.php', $optionsyes, NULL, 'post', 'get');
            notice_yesno(get_string('deletecheckfull', '', "'$fullname'"), "./deleteuser.php", 'manageusers.php', $optionsyes, NULL, 'post', 'get');
            echo $OUTPUT->footer();
            die;
        } else if (data_submitted() and !$user->deleted) {
            //following code is also used in auth sync scripts
            $updateuser = new object();
            $updateuser->id           = $user->id;
            $updateuser->deleted      = 1;
            $updateuser->username     = addslashes("$user->email.".time());  // Remember it just in case
            $updateuser->email        = '';               // Clear this field to free it up
            $updateuser->idnumber     = '';               // Clear this field to free it up
            $updateuser->timemodified = time();
            if ($DB->update_record('user', $updateuser)) {
                // not sure if this is needed. unenrol_student($user->id);  // From all courses
                $DB->delete_records('role_assignments', array('userid'=>$user->id)); // unassign all roles
                // remove all context assigned on this user?
                notify(get_string('deletedactivity', '', fullname($user, true)) );
            } else {
                notify(get_string('deletednot', '', fullname($user, true)));
            }
        }
    } else if ($acl and confirm_sesskey()) {             /// Allow/Deny a selected user, after confirmation
        if (!$user = $DB->get_record('user', array('id', $acl))) {
            error("No such user.", '', true);
        }
        if (!is_mnet_remote_user($user)) {
            error('Users in the MNET access control list must be remote MNET users.');
        }
        $accessctrl = strtolower(required_param('accessctrl', PARAM_ALPHA));
        if ($accessctrl != 'allow' and $accessctrl != 'deny') {
            error('Invalid access parameter.');
        }
        $aclrecord = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid));
        if (empty($aclrecord)) {
            $aclrecord = new object();
            $aclrecord->mnet_host_id = $user->mnethostid;
            $aclrecord->username = $user->username;
            $aclrecord->accessctrl = $accessctrl;
            if (!$DB->insert_record('mnet_sso_access_control', $aclrecord)) {
                error("Database error - Couldn't modify the MNET access control list.", '', true);
            }
        } else {
            $aclrecord->accessctrl = $accessctrl;
            if (!$DB->update_record('mnet_sso_access_control', $aclrecord)) {
                error("Database error - Couldn't modify the MNET access control list.", '', true);
            }
        }
        $mnethosts = $DB->get_records('mnet_host', null, '', '', 'id', 'id,wwwroot,name');
        notify("MNET access control list updated: username '$user->username' from host '"
                    . $mnethosts[$user->mnethostid]->name
                    . "' access now set to '$accessctrl'.");
    }

    /// Carry on with the user listing

    /// Now display user filters
    $ufiltering->display_add();
    $ufiltering->display_active();

    // Execute query
    $usersearchcount = 0;
    $foundcount = 0;

    $users = usermanagement_get_manageusers_listing($tableview, $sqluserfiltering, $usersearchcount, $foundcount, $sort, $dir, $firstinitial, $lastinitial, $page*$perpage, $perpage);

    $totalusers  = $DB->count_records_select('user', $selectnousers, null);
    $userpagedsearchcount = 0;
    if(is_array($users)) {
        $userpagedsearchcount = count($users);
    }
    $selectedcount = isset($SESSION->bulk_users) ? count($SESSION->bulk_users) : 0;

    $userresultcount = new stdClass;
    $userresultcount->searchcount = $usersearchcount;
    $userresultcount->pagecount = $userpagedsearchcount;
    $userresultcount->totalcount = $totalusers;  //
    $userresultcount->selectedcount = $selectedcount; // from $SESSION->bulk_users
    $userresultcount->perpage = $perpage;

    if($tableview == 'viewfilter') {
        $headmessage = get_string('usersontotalusers', 'block_usermanagement', $userresultcount);
        $subheadmessage = get_string('selectedusers', 'block_usermanagement', $userresultcount->selectedcount);
    } else {
        $headmessage = get_string('selectedusers', 'block_usermanagement', $userresultcount->selectedcount);
        $userresultcount->searchcount = $foundcount;
        $subheadmessage = get_string('usersontotalusers', 'block_usermanagement', $userresultcount);
        $userresultcount->searchcount = $usersearchcount;
    }

    echo $OUTPUT->heading(get_string('tablehead', 'block_usermanagement', $headmessage).'  ('.get_string('inthispage','block_usermanagement', $userpagedsearchcount).')');
    notify(get_string('tablesubhead', 'block_usermanagement', $subheadmessage));

    // print paging bar & usersperpage form
    echo '<table width="100%"><tr>
            <td align="left" width="15%" nowrap="nowrap">
            </td><td width="70%" align="center">
            <div id="pagingbar" >';
    // Paging bar
    $pagingbar = new paging_bar($usersearchcount, $page, $perpage, $baseurl);
    echo $OUTPUT->render($pagingbar);

    echo '</div></td>';
    //   Per-page form
    echo '<td align="right" width="15%" nowrap="nowrap">';
    $choices = array();
    $choices["10"] = '10';
    $choices["20"] = '20';
    $choices["25"] = '25';
    $choices["50"] = '50';
    $choices["100"] = '100';
    $choices["150"] = '150';
    $choices["200"] = '200';
    $choices["250"] = '250';
    echo '<div class="perpageform">';
    $url = new moodle_url($baseurl, array('page'=>0, 'sesskey'=>$USER->sesskey));
    $select = new single_select($baseurl, 'perpage', $choices, $perpage, null, 'perPageForm');
    $label = get_string('userperpage','block_usermanagement');
    $select->set_label($label.':&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';
    echo '</td></tr>';
    echo '<tr><td align="left" width="15%" nowrap="nowrap"></td>';
    echo '<td width="70%" align="center">';
        $alphabet = explode(',', get_string('alphabet', 'block_usermanagement'));
        $strall = get_string("all");

        /// Bar of first initials
        echo get_string("firstname")." : ";
        if ($firstinitial) {
            echo " <a href=\"manageusers.php?sort=firstname&amp;dir=ASC&amp;".
                "perpage=$perpage&amp;firstinitial=&amp;lastinitial=$lastinitial\">$strall</a> ";
        } else {
            echo " <b>$strall</b> ";
        }
        foreach ($alphabet as $letter) {
            if ($letter == $firstinitial) {
                echo " <b>$letter</b> ";
            } else {
                echo " <a href=\"manageusers.php?sort=firstname&amp;dir=ASC&amp;".
                    "perpage=$perpage&amp;lastinitial=$lastinitial&amp;firstinitial=$letter\">$letter</a> ";
            }
        }
        /// Bar of last initials
        echo '<br />'.get_string("lastname")." : ";
        if ($lastinitial) {
            echo " <a href=\"manageusers.php?sort=lastname&amp;dir=ASC&amp;".
                "perpage=$perpage&amp;lastinitial=&amp;firstinitial=$firstinitial\">$strall</a> ";
        } else {
            echo " <b>$strall</b> ";
        }
        foreach ($alphabet as $letter) {
            if ($letter == $lastinitial) {
                echo " <b>$letter</b> ";
            } else {
                echo " <a href=\"manageusers.php?sort=lastname&amp;dir=ASC&amp;".
                    "perpage=$perpage&amp;firstinitial=$firstinitial&amp;lastinitial=$letter\">$letter</a> ";
            }
        }
    echo '</td><td></td></tr>';
    echo '</table>';
    echo '<br />';
    flush();

    /// Prepare column headers
    $columns = array("firstname", "lastname", "idnumber", "email",
                        "institution", "department", "city", "country",
                        "lastaccess",
                        "auth", "mnethostname",
                        "confirmed");

    $stringmanager = get_string_manager();
    foreach ($columns as $column) {
        // Column names (looks in global lang file and than in block lang file)
        if ( $column == 'confirmed' ) {
            $string[$column] = get_string( $column, 'block_usermanagement');
        } else {
            /*
            $string[$column] = get_string("$column");string_exists($identifier, $component)
            if ( $string[$column] == "[[$column]]" ) {
                $string[$column] = get_string( $column, 'block_usermanagement');
            }*/
            $string[$column] = $stringmanager->string_exists("$column", 'moodle') ? get_string("$column") :  get_string( $column, 'block_usermanagement');
        }
        if ($sort != $column) {
            $columnicon = "";
            if ($column == "lastaccess") {
                $columndir = "DESC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC":"ASC";
            if ($column == "lastaccess") {
                $columnicon = $dir == "ASC" ? "up":"down";
            } else {
                $columnicon = $dir == "ASC" ? "down":"up";
            }
            $columnicon = '<img src="' . $OUTPUT->pix_url("t/$columnicon") . '" class="icron" alt="" />';
        }

        /// Hide and show column headers
        // Get column label (if undef in global lang file, look in block lang file)
        $columnlabel = $stringmanager->string_exists("$column", 'moodle') ? get_string("$column") :  get_string( $column, 'block_usermanagement');
        /*
        $columnlabel = get_string($column);
        if ( $columnlabel == "[[$column]]" ) {
            $columnlabel = get_string($column, 'block_usermanagement');
        }*/
        // Force getting confirmed label from block lang file
        if ( $column == 'confirmed' ) {
            $columnlabel = get_string($column, 'block_usermanagement');
        }
        // No hide for last and first name
        if ($column == 'firstname' ||  $column == 'lastname' ) {
            $$column = "<a href=\"manageusers.php?sort=$column&amp;dir=$columndir&amp;firstinitial=$firstinitial&amp;lastinitial=$lastinitial\">"
                        .$string[$column]."</a>$columnicon";
        }
        // for other column, if column is hidden show only 'show' icon w/ tooltip
        else if ( !$showfields[$column] ) {
            $showcolumnlabel = get_string('showfield','block_usermanagement')." ".$columnlabel;
            $$column = "<a href=\"manageusers.php?show${column}=1\" class=\"tooltip\">"
                        .'<img src="' . $OUTPUT->pix_url('t/switch_plus') . '" class="icron" alt="'.$showcolumnlabel.'" title="'.$showcolumnlabel.'"  />'
                        ."<span>$columnlabel</span></a>";
        }
        // otherwise show column and 'hide' icon
        else {
            $hidecolumnlabel = get_string('hidefield','block_usermanagement')." ".$columnlabel;
            $$column = "<a href=\"manageusers.php?show${column}=0\">"
                        .'<img src="' . $OUTPUT->pix_url('t/switch_minus') . '" class="icron" alt="'.$hidecolumnlabel.'" title="'.$hidecolumnlabel.'"  />'
                        ."<a href=\"manageusers.php?sort=$column&amp;dir=$columndir&amp;firstinitial=$firstinitial&amp;lastinitial=$lastinitial\">"
                        .$string[$column]."</a>$columnicon";
        }
    }

    $countries = $stringmanager->get_list_of_countries();

    foreach ($users as $key => $user) {
        if (!empty($user->country)) {
            $users[$key]->country = $countries[$user->country];
        }
    }
    if ($sort == "country") {  // Need to resort by full country name, not code
        foreach ($users as $user) {
            $susers[$user->id] = $user->country;
        }
        asort($susers);
        foreach ($susers as $key => $value) {
            $nusers[] = $users[$key];
        }
        $users = $nusers;
    }

    // Setup table
    $table = new html_table();
    $table->head = array ("$firstname / $lastname", $idnumber, $email,
                $institution, $department, $city, $country,
                $lastaccess,
                $auth, $mnethostname,
                $confirmed, "");
    $table->align = array ("left", "left", "left",
                "left", "left", "left", "left",
                "left",
                "left", "left",
                "center", "right");
    $table->wrap = array ('nowrap', '', '',
                '', '', '', '',
                '',
                '', '',
                '', 'nowrap');
    $table->width = "98%";
    $table->attributes['class'] = " userstable boxaligncenter ";


    if($tableview == 'viewselection' ) {
             $table->attributes['class'] = ' generaltable boxaligncenter ';
    }

    // Spacer for icons
    $iconspacer = "<img src=\"$securewwwroot/pix/spacer.gif\" width=\"11\" height=\"11\" border=\"0\" />";

    foreach ($users as $user) {
        // is the user remote?
        $isremoteuser = ($user->mnethostid != $CFG->mnet_localhost_id);
        // User Auth plugin instance
        $authplugin = $authplugins[$user->auth];
        // last access
        if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
        } else {
            $strlastaccess = get_string("never");
        }
        // Edit icon (only if user is local and has capabilities)
        $editbutton = '';
        if ($caneditupdate) {
            if ( $isremoteuser ) {
                $editbutton = $iconspacer;
            } else {
                    $editbutton = "<a href=\"$securewwwroot/user/editadvanced.php?id=$user->id&amp;course=$site->id\"><img src=\"$securewwwroot/pix/t/edit.png\" alt=\"$stredit\" title=\"$stredit\" /></a>";
            }
        }

        // Delete icon
        $deletebutton = '';
        if ($candelete) {
            if ($user->id == $USER->id or $user->username == "changeme") {
                $deletebutton = $iconspacer;
            } else {
                $deletebutton = "<a href=\"?delete=$user->id&amp;sesskey=$USER->sesskey\"><img src=\"$securewwwroot/pix/t/delete.png\" alt=\"$strdelete\" title=\"$strdelete\" /></a>";
            }
        }

        // Confirm icon and confirm string(only if local and user's auth allow confirm)
        $confirmbutton = '';
        $strisconfirmed = '';
        if ($canconfirm) {
            if ( !$isremoteuser && $authplugin->can_confirm() ) {
                if ( $user->confirmed == 0 ) {
                    $strisconfirmed = get_string('no');
                    $confirmbutton = "<a href=\"?confirmuser=$user->id&amp;sesskey=$USER->sesskey\"><img src=\"$securewwwroot/pix/t/approve.png\" alt=\"$strconfirm\" title=\"$strconfirm\" /></a>";
                } else {
                    $strisconfirmed = get_string('yes');
                    $confirmbutton = $iconspacer;
                }
            } else {
                $strisconfirmed = get_string('n_a','block_usermanagement');
                $confirmbutton = $iconspacer;
            }
        }

        // Only if remote users exists...
        $strremotehost = '';
        if ($caneditupdate) {
            if ( $mnet_auth_users_exists ) {
                if ( $isremoteuser ) {
                    // Allow/Deny button (form remote users only)
                    $accessctrl = 'allow';
                    if ($acl = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid))) {
                        $accessctrl = $acl->accessctrl;
                    }
                    $strallowdeny = get_string( $accessctrl ,'mnet');
                    $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
                    $strchangeto =  s(($changeaccessto == 'deny')?(get_string('allow_denymnetaccess', 'block_usermanagement')):(get_string('deny_allowmnetaccess', 'block_usermanagement')));
                    $allowdenyiconurl = "$securewwwroot/pix/t/". (($accessctrl == 'allow')?'go.png':'stop.png') ;
                    $allowdenybutton = "<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey={$USER->sesskey}\"><img src=\"$allowdenyiconurl\" alt=\"$strchangeto\" title=\"$strchangeto\" /></a>";
                    // Remote Host
                    $strremotehost .= s($user->mnethostname) . " $allowdenybutton";
                }
            }
        }

        // Select checkbox (if current user has capabilities to assign roles)
        $selectcheck = '';
        if ($canmanage) {
            $selectcheck = '<input type="checkbox" name="marked['.$user->id.']" value="'.$user->id.'"/>';
        }
        // Icons
        $actionbuttons = $editbutton.' '.$deletebutton.' '.$confirmbutton;

        // Full name
        $format = "lastname firstname";
        if($sort == 'firstname') {
            $format = 'firstname lastname';
        }
        $fullname = fullname($user, true, $format);
        $rowclass= '';
        if(in_array($user->id, $SESSION->bulk_users)) {
            $rowclass = ' selected ';
        }

        // Edit user link (link only if user can edit)
        $edituserlink =  s($fullname);
        if ($canmanage) {
            $edituserlink = " <a  href=\"$securewwwroot/user/view.php?id=$user->id&amp;course=$site->id\">$edituserlink</a>";
        }

        $table->data[] = array ($selectcheck.$edituserlink,
                    usermanagement_collapsable_text( s($user->idnumber), $showfields['idnumber'] ),
                    usermanagement_collapsable_text( obfuscate_mailto($user->email, '', $user->emailstop), $showfields['email'], $user->email ),
                    usermanagement_collapsable_text( s($user->institution), $showfields['institution'] ),
                    usermanagement_collapsable_text( s($user->department), $showfields['department'] ),
                    usermanagement_collapsable_text( s($user->city), $showfields['city'] ),
                    usermanagement_collapsable_text( s($user->country), $showfields['country'] ),
                    usermanagement_collapsable_text( s($strlastaccess), $showfields['lastaccess'] ),
                    usermanagement_collapsable_text( s($user->auth), $showfields['auth'] ),
                    // Displays MNET host only if not localhost
                    usermanagement_collapsable_text( $strremotehost, $showfields['mnethostname'] ),
                    usermanagement_collapsable_text( $strisconfirmed, $showfields['confirmed'] ),
                    $actionbuttons);
         $table->rowclasses[] = $rowclass;
    }

    if (!empty($table)  ) {
        // Form
        $strnouserselected = get_string("nouserselected","block_usermanagement");
        $strnoactionselected = get_string("noactionselected","block_usermanagement");

        $changeview = 'viewselection';
        if($tableview == 'viewselection') {
            $changeview = 'viewfilter';
        }

        echo "  <script Language=\"JavaScript\">
                <!--
                function checksubmit(form) {
                    if ( !checkchecked(form) ) {
                        alert ('$strnouserselected');
                        return false;
                    }
                    document.getElementById('selectusersformid').submit();
                    return true;
                }

                function checkchecked(form) {
                    var inputs = document.getElementsByTagName('INPUT');
                    var checked = false;
                    inputs = filterByParent(inputs, function() {return form;});
                    for(var i = 0; i < inputs.length; ++i) {
                        if(inputs[i].type == 'checkbox' && inputs[i].checked) {
                            checked = true;
                        }
                    }
                    return checked;
                }

                function addtoselection(form) {
                    if ( !checkchecked(form) ) {
                        alert ('$strnouserselected');
                        return false;
                    }
                    document.getElementById('selectionactionid').value = 'add';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }

                function delfromselection(form) {
                    if ( !checkchecked(form) ) {
                        alert ('$strnouserselected');
                        return false;
                    }
                    document.getElementById('selectionactionid').value = 'del';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }

                function addalltoselection(form) {
                    document.getElementById('selectionactionid').value = 'addall';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }

                function delallfromselection(form) {
                    document.getElementById('selectionactionid').value = 'delall';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }

                function delselection(form) {
                    document.getElementById('selectionactionid').value = 'delsel';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }


                function changeview(form) {
                    document.getElementById('tableviewid').value = '$changeview';
                    document.getElementById('pageid').value = '0';
                    document.getElementById('selectusersformid').submit();
                    return true;
                }



                function submitMultiDelete(form) {
                    if ( checkchecked(form) ) {
                        document.getElementById('multi_action').value = 'delete';
                        document.forms['usersform'].submit();
                        return true;
                    } else {
                        alert ('$strnouserselected');
                        return false;
                    }
                }
                //-->
                </script>";

        echo '<form action="manageusers.php" method="post" name="selectusersform" id="selectusersformid" >'; //onSubmit="return checksubmit(this);"
        echo '<input type="hidden" name="returnto" value="'.$_SERVER['REQUEST_URI'].'" />';
        echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';

        echo  html_writer::table($table); //print_table($table);

        if(!$users) {
            $message = 'nousermatchingconditions';
            if($tableview == 'viewselection') {
                $message = 'noselectedusers';
            }
            echo $OUTPUT->heading(get_string($message, 'block_usermanagement'));
        }

        echo '<br />';

        if ($canmanage) {
            /// Multi-user action
            echo '<table width="100%"><tr><td align="left" valign="top">';
            // Select links
            $checkall = get_string('checkall', 'block_usermanagement');
            $uncheckall = get_string('uncheckall', 'block_usermanagement');
            echo '<a href="javascript:checkall()" onclick="checkall()" ><img class=icon" style="vertical-align: middle;" src="'.$CFG->wwwroot.'/blocks/usermanagement/pix/checked.gif" alt="'.$checkall.'"/>'.$checkall.'</a>&nbsp;&nbsp;';
            echo '<a href="javascript:checknone()" onclick="checknone()" ><img class=icon" style="vertical-align: middle;" src="'.$CFG->wwwroot.'/blocks/usermanagement/pix/unchecked.gif" alt="'.$uncheckall.'"/>'.$uncheckall.'</a>';
            echo '</td><td valign="top">';
            echo '<input type="hidden" name="firstinitial"  value="'.$firstinitial.'" />';
            echo '<input type="hidden" name="lastinitial"  value="'.$lastinitial.'" />';
            echo '<input type="hidden" name="perpage"  value="'.$perpage.'" />';
            echo '<input type="hidden" name="dir"  value="'.$dir.'" />';
            echo '<input type="hidden" name="sort"  value="'.$sort.'" />';
            echo '<input type="hidden" id="selectionactionid" name="selectionaction" value="none" />';
            echo '<input type="hidden" id="tableviewid" name="tableview" value="'.$tableview.'" />';
             echo '<input type="hidden" id="pageid" name="page" value="'.$page.'" />';

            echo '</td><td align="left">';
            if($tableview == 'viewfilter') {
                echo '<input type="button" name="addmarkedtoselection" value="'.get_string('addmarkedtoselection', 'block_usermanagement').'" onClick="return addtoselection(this);" /><br />';
            }
            echo '<input type="button" name="delmarkedfromselection" value="'.get_string('delmarkedfromselection', 'block_usermanagement').'" onClick="return delfromselection(this);" />';
            echo '</td><td align="right">';
            //echo '<br />';
            if($tableview == 'viewfilter') {
                echo '<input type="button" name="alltoselection" value="'.get_string('addalltoselection', 'block_usermanagement', $userresultcount->searchcount).'" onClick="return addalltoselection(this);" /><br />';
            }
            echo '<input type="button" name="allfromselection" value="'.get_string('delallfromselection', 'block_usermanagement', $userresultcount->searchcount).'" onClick="return delallfromselection(this);" /><br />';
            echo '<input type="button" name="delallselection" value="'.get_string('delselection', 'block_usermanagement', $userresultcount->searchcount).'" onClick="return delselection(this);" /><br />';
            echo '<input type="button" name="updatedefaulter" value="'.get_string('actionupdatedefaulter','block_usermanagement').'" onClick="location.href=\''.$CFG->wwwroot.'/blocks/usermanagement/manageusers_actions/managedefaulter/updatedefaulter.php\'"; />';
            echo '</td></tr></table>';
        }
        // view mode
        echo '<div align="center">';
        echo '<input type="button" value="'.get_string($changeview.'users','block_usermanagement', $userresultcount->selectedcount).'" onClick="return changeview(this);" />';
        echo '</div>';
        echo '</form>';

        $pagingbar = new paging_bar($usersearchcount, $page, $perpage, $baseurl);
        echo $OUTPUT->render($pagingbar);
    }

        $actions = array();
        if($canmanage && $cansend) {
            $actions['sendtracker'] = get_string('actionsendtracker','block_usermanagement');
            $actions['sendimmessage'] = get_string('sendimmessage','block_usermanagement');
        }
        if($canmanage && $canseedetails) {
            $actions['view'] = get_string('actionview','block_usermanagement');
            $actions['download'] = get_string('actiondownload','block_usermanagement');
        }
        if($canmanage && $caneditupdate) {
            $actions['togglemail'] = get_string('actiontogglemail','block_usermanagement');
            $actions['forcepassword'] = get_string('actionforcepassword','block_usermanagement');
        }
        if($canmanage && $canenrol) {
            $actions['enrol'] = get_string('actionenrol','block_usermanagement');
            $actions['unenrol'] = get_string('actionunenrol','block_usermanagement');
            $actions['assignrole'] = get_string('actionassignrole','block_usermanagement');
            $actions['unassignrole'] = get_string('actionunassignrole','block_usermanagement');
        }
        if($canmanage && $canconfirm) {
            $actions['userconfirm'] = get_string('actionuserconfirm','block_usermanagement');
        }
        /*
        if($canmanage && $candelete) {
            $actions['userdelete'] = get_string('actionuserdelete','block_usermanagement');
            $actions['purge'] = get_string('actionpurge','block_usermanagement');
        }*/
        /////////////////AMARTIN
        /*
         * OPCIONES NUEVAS PARA GESTIONAR MOROSOS
         */
        //if($canmanage && $candelete) {
		if($canmanage) {
            $actions['todefaulter'] = get_string('actiontodefaulter','block_usermanagement');
        }
        //if($canmanage && $candelete) {
		if($canmanage) {
            $actions['fromdefaulter'] = get_string('actionfromdefaulter','block_usermanagement');
        }
        //if($canmanage && $candelete) {
		if($canmanage) {
            $actions['deletedefaulter'] = get_string('actiondeletedefaulter','block_usermanagement');
        }
        /*
        if($canmanage && $cancreate) {
            $actions['updatedefaulter'] = get_string('actionupdatedefaulter','block_usermanagement');
        }
        */
        /////////////////


        echo '<br /><div align="center">';
        echo '<form action="manageusers.php" method="post" name="manageuseractions">';
        echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
        //echo '<input type="hidden" name="removeall" value="removeall"  id="id_removeall2" />';
        echo '<input name="_qf__user_active_filter_form" type="hidden" value="1" />';

        echo get_string('selecteduserswith', 'block_usermanagement', $userresultcount->selectedcount).'&nbsp;';
        //choose_from_menu($actions, 'useraction', '', 'choose', '', '0');
        echo html_writer::select($actions, 'useraction', '', array(0=>'choose'));
        if($userresultcount->selectedcount > 0) {
            echo '&nbsp;<input type="submit" name="douseractions" value="'.get_string('goaction','block_usermanagement').'" />';
        }
        echo '</form>';
        echo '</div>';

    echo $OUTPUT->footer();

