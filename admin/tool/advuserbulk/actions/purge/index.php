<?php
/**
* user bulk action script for batch deleting user activity and full unenroling
*/

require_once('../../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/lib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/lib.php');
require_once($CFG->dirroot.'/mod/assignment/lib.php');

$reset    = optional_param('reset', 0, PARAM_BOOL);
$accept    = optional_param('accept', 0, PARAM_BOOL);
$cancel      = optional_param('cancel', 0, PARAM_BOOL);
$allcourses  = optional_param_array('allcourses', '', PARAM_INT); // ecastro ULPGC
$selcourses  = optional_param_array('selcourses', '', PARAM_INT);
$searchtext  = optional_param('searchtext', '', PARAM_RAW);
$showall     = optional_param('showall', 0, PARAM_BOOL);
$listadd     = optional_param('add', 0, PARAM_BOOL);
$listremove  = optional_param('remove', 0, PARAM_BOOL);
$removeall   = optional_param('removeall', 0, PARAM_BOOL);

admin_externalpage_setup('tooladvuserbulk');
check_action_capabilities('purge', true);

if ($showall) {
    $searchtext = '';
}

$strsearch = get_string('search');
$return = $CFG->wwwroot.'/'.$CFG->admin.'/tool/advuserbulk/user_bulk.php';
$langdir = $CFG->dirroot.'/admin/tool/advuserbulk/actions/purge/lang/';
$pluginname = 'bulkuseractions_purge';

if ($reset) {
    unset($SESSION->purge_progress);
}

if (empty($SESSION->bulk_users) || $cancel) {
    redirect($return);
}

if (!isset($SESSION->bulk_courses) || $removeall)
    $SESSION->bulk_courses = array();

// course selection add/remove actions
if ($listadd && !empty($allcourses)) {
    foreach ($allcourses as $course) {
        if (!in_array( $course, $SESSION->bulk_courses )) {
            $SESSION->bulk_courses[] = $course;
        }
    }
}

if ($listremove && !empty($selcourses)) {
    foreach ($selcourses as $course) {
        unset($SESSION->bulk_courses[ array_search($course, $SESSION->bulk_courses) ]);
    }
}


// generate full and selected course lists
$availablecourses = array();
foreach ($SESSION->bulk_users as $userid) {
    $usercourses = enrol_get_users_courses($userid);
    foreach($usercourses as $key=>$junk) {
        $availablecourses[$key] = 0;
    }

    $usercourses = get_user_capability_course('moodle/course:view', $userid);
    // get_user_capability_course can return false
    if ($usercourses) {
        foreach($usercourses as $course) {
            $availablecourses[$course->id] = 0;
        }
    }
}
$availablecourses = array_keys($availablecourses);

$coursenames =  advuserbulk_gen_course_list($searchtext, array_diff($availablecourses, $SESSION->bulk_courses));
$selcoursenames =  advuserbulk_gen_course_list('', array_intersect($availablecourses, $SESSION->bulk_courses));

// print the page
if ($accept) {
    if (empty($SESSION->bulk_courses)) {
        redirect($return);
        die;
    }
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));

    $buttonyes = new single_button(new moodle_url('iterator.php', array('confirm' => true)), get_string('yes'));
    $buttonno = new single_button(new moodle_url($return), get_string('no'), 'get');
    echo $OUTPUT->confirm(advuserbulk_get_string('confirmation', $pluginname), $buttonyes, $buttonno);

    echo $OUTPUT->footer();
    die;
}

// print the general page
echo $OUTPUT->header();
?>
<div id="addmembersform">
    <h3 class="main"><?php echo advuserbulk_get_string('title', $pluginname) ?></h3>

    <form id="addform" method="post" action="index.php">
        <table cellpadding="6" class="selectcourses generaltable generalbox boxaligncenter" summary="">
            <tr>
                <td id="existingcell">
                    <p>
                        <label for="allcourses"><?php echo advuserbulk_get_string('allcourses', $pluginname) ?></label>
                    </p>
                    <select name="allcourses[]" size="20" id="allcourses" multiple="multiple"
                            onfocus="document.getElementById('addform').add.disabled=false;
                                document.getElementById('addform').remove.disabled=true;
                                document.getElementById('addform').selcourses.selectedIndex=-1;"
                            onclick="this.focus();">
                        <?php echo $coursenames ?>
                    </select>

                    <br />
                    <label for="searchtext" class="accesshide"><?php p($strsearch) ?></label>
                    <input type="text" name="searchtext" id="searchtext" size="21" value="<?php p($searchtext, true) ?>"
                           onfocus ="getElementById('addform').add.disabled=true;
                               getElementById('addform').remove.disabled=true;
                               getElementById('addform').allcourses.selectedIndex=-1;
                               getElementById('addform').selcourses.selectedIndex=-1;"
                           onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                               if (keyCode == 13) {
                                   getElementById('addform').previoussearch.value=1;
                                   getElementById('addform').submit();
                               } " />
                    <input name="search" id="search" type="submit" value="<?php p($strsearch) ?>" />
                    <?php
                    if (!empty($searchtext)) {
                        echo '<br /><input name="showall" id="showall" type="submit" value="' . get_string('showall') . '" />' . "\n";
                    }
                    ?>
                </td>
                <td id="buttonscell">
                    <div id="addcontrols">
                        <input name="add" id="add" type="submit" disabled value="<?php echo '&nbsp;' . $OUTPUT->rarrow() . ' &nbsp; &nbsp; ' . get_string('add'); ?>" title="<?php print_string('add'); ?>" />
                    </div>
                    <div id="removecontrols">
                        <input name="remove" id="remove" type="submit" disabled value="<?php echo '&nbsp; ' . $OUTPUT->larrow() . ' &nbsp; &nbsp; ' . get_string('remove'); ?>" title="<?php print_string('remove'); ?>" />
                    </div>
                </td>
                <td id="potentialcell">
                    <p>
                        <label for="selcourses"><?php echo advuserbulk_get_string('selectedcourses', $pluginname) ?></label>
                    </p>
                    <select name="selcourses[]" size="20" id="selcourses" multiple="multiple"
                            onfocus="document.getElementById('addform').remove.disabled=false;
                                      document.getElementById('addform').add.disabled=true;
                                      document.getElementById('addform').allcourses.selectedIndex=-1;"
                            onclick="this.focus();">
                        <?php echo $selcoursenames; ?>
                    </select>
                    <br />
                    <input name="removeall" id="removeall" type="submit" value="<?php echo get_string('removeall', 'bulkusers') ?>" />
                </td>
            </tr>
            <tr><td></td><td align="center">
                <p><input type="submit" name="cancel" value="<?php echo get_string('cancel') ?>" />
                    <input type="submit" name="accept" value="<?php echo advuserbulk_get_string('accept', $pluginname) ?>" /></p>
            </td>
            </tr>

        </table>
    </form>
</div>
<?php
echo $OUTPUT->footer();
?>
