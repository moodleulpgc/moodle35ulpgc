<?php
/**
 * Links to backuprestore tools
 *
 * @package    tool
 * @subpackage backuprestore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/backup/util/interfaces/checksumable.class.php');
require_once($CFG->dirroot.'/backup/backup.class.php');

defined('MOODLE_INTERNAL') || die;

$systemcontext = context_system::instance();
if (has_capability('moodle/site:config', $systemcontext)) {
    $ADMIN->add('courses', new admin_category('tool_backuprestore', get_string('pluginname', 'tool_backuprestore')));

    $temp = new admin_settingpage('tool_backuprestore_backupsettings', get_string('backupsettings', 'tool_backuprestore'), 'moodle/site:config');

    //roles
    $roles = get_all_roles();
    $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);

    $temp->add(new admin_setting_configselect('tool_backuprestore/coordinatorrole', get_string('coordinatorrole', 'tool_backuprestore'), get_string('coordinatorrole_desc', 'tool_backuprestore'), 3, $options ));

    $temp->add(new admin_setting_configtext('tool_backuprestore/backupdir', get_string('backupdir', 'tool_backuprestore'), get_string('backupdir_desc', 'tool_backuprestore'), '', PARAM_NOTAGS));

    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_users', get_string('generalusers','backup'), get_string('configgeneralusers','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_anonymize', get_string('generalanonymize','backup'), get_string('configgeneralanonymize','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_role_assignments', get_string('generalroleassignments','backup'), get_string('configgeneralroleassignments','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_activities', get_string('generalactivities','backup'), get_string('configgeneralactivities','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_blocks', get_string('generalblocks','backup'), get_string('configgeneralblocks','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_filters', get_string('generalfilters','backup'), get_string('configgeneralfilters','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_comments', get_string('generalcomments','backup'), get_string('configgeneralcomments','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_badges', new lang_string('generalbadges','backup'), new lang_string('configgeneralbadges','backup'), array('value'=>1,'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_userscompletion', get_string('generaluserscompletion','backup'), get_string('configgeneraluserscompletion','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_logs', get_string('generallogs','backup'), get_string('configgenerallogs','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_histories', get_string('generalhistories','backup'), get_string('configgeneralhistories','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_questionbank', new lang_string('generalquestionbank','backup'), new lang_string('configgeneralquestionbank','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/backup_groups', get_string('backupgroups','tool_backuprestore'), get_string('backupgroups_desc','tool_backuprestore'), array('value'=>0, 'locked'=>0)));
    $ADMIN->add('tool_backuprestore', $temp);

    $temp = new admin_settingpage('tool_backuprestore_restoresettings', get_string('restoresettings', 'tool_backuprestore'), 'moodle/site:config');

    $temp->add(new admin_setting_configtext('tool_backuprestore/restoredir', get_string('restoredir', 'tool_backuprestore'), get_string('restoredir_desc', 'tool_backuprestore'), '', PARAM_NOTAGS));

    // Predefined modes (purposes) of the backup
    $options = array (backup::MODE_GENERAL   => get_string('mode_general', 'tool_backuprestore'),
                        backup::MODE_IMPORT    => get_string('mode_import', 'tool_backuprestore'),
                        backup::MODE_HUB       => get_string('mode_hub', 'tool_backuprestore'),
                        backup::MODE_SAMESITE  => get_string('mode_samesite', 'tool_backuprestore'),
                        backup::MODE_AUTOMATED => get_string('mode_automated', 'tool_backuprestore'),
                        backup::MODE_CONVERTED => get_string('mode_converted', 'tool_backuprestore')
                    );
    $temp->add(new admin_setting_configselect('tool_backuprestore/restore_mode', get_string('restoremode', 'tool_backuprestore'), get_string('restoremode_desc', 'tool_backuprestore'), backup::MODE_SAMESITE, $options ));

    // Target (new/existing/current/adding/deleting)
    $options = array(backup::TARGET_NEW_COURSE       => get_string('target_new_course', 'tool_backuprestore'),
                        backup::TARGET_EXISTING_DELETING=> get_string('target_existing_deleting', 'tool_backuprestore'),
                        backup::TARGET_EXISTING_ADDING  => get_string('target_existing_adding', 'tool_backuprestore')
                );
    $temp->add(new admin_setting_configselect('tool_backuprestore/restore_target', get_string('restoretarget', 'tool_backuprestore'), get_string('restoretarget_desc', 'tool_backuprestore'), backup::TARGET_EXISTING_DELETING, $options));

    $categories = make_categories_options();
    $temp->add(new admin_setting_configselect('tool_backuprestore/restore_category', get_string('restoretarget', 'tool_backuprestore'), get_string('restoretarget_desc', 'tool_backuprestore'), 1, $categories));

    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_users', get_string('generalusers','backup'), get_string('configgeneralusers','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_anonymize', get_string('generalanonymize','backup'), get_string('configgeneralanonymize','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_role_assignments', get_string('generalroleassignments','backup'), get_string('configgeneralroleassignments','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_activities', get_string('generalactivities','backup'), get_string('configgeneralactivities','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_blocks', get_string('generalblocks','backup'), get_string('configgeneralblocks','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_filters', get_string('generalfilters','backup'), get_string('configgeneralfilters','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_comments', get_string('generalcomments','backup'), get_string('configgeneralcomments','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_badges', get_string('generalbadges','backup'), get_string('configgeneralbadges','backup'), array('value'=>0,'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_userscompletion', get_string('generaluserscompletion','backup'), get_string('configgeneraluserscompletion','backup'), array('value'=>1, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_logs', get_string('generallogs','backup'), get_string('configgenerallogs','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_histories', get_string('generalhistories','backup'), get_string('configgeneralhistories','backup'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_groups', get_string('backupgroups','tool_backuprestore'), get_string('backupgroups_desc','tool_backuprestore'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_keepgroups', get_string('setting_keep_groups_and_groupings','backup'), get_string('restorekeepgroups_desc','tool_backuprestore'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_keeproles', get_string('setting_keep_roles_and_enrolments','backup'), get_string('restorekeeproles_desc','tool_backuprestore'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_overwriteconf', get_string('setting_overwrite_conf', 'backup'), get_string('restoreoverwriteconf_desc','tool_backuprestore'), array('value'=>1, 'locked'=>0)));
    //$temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_questionbank', get_string('generalquestionbank','backup'), get_string('restorequestionbank_desc','tool_backuprestore'), array('value'=>0, 'locked'=>0)));
    $temp->add(new admin_setting_configcheckbox_with_lock('tool_backuprestore/restore_adminmods', get_string('restoreadminmods','tool_backuprestore'), get_string('restoreadminmods_desc','tool_backuprestore'), array('value'=>1, 'locked'=>0)));
    
    $ADMIN->add('tool_backuprestore', $temp);

    $ADMIN->add('tool_backuprestore', new admin_externalpage('toolprebackup', get_string('prebackup', 'tool_backuprestore'),  "$CFG->wwwroot/$CFG->admin/tool/backuprestore/prebackup_cleanup.php",'moodle/site:config'));
    $ADMIN->add('tool_backuprestore', new admin_externalpage('toolpostrestore', get_string('postrestore', 'tool_backuprestore'),  "$CFG->wwwroot/$CFG->admin/tool/backuprestore/postrestore_cleanup.php",'moodle/site:config'));

    $ADMIN->add('tool_backuprestore', new admin_externalpage('toolmultibackup', get_string('multibackup', 'tool_backuprestore'),  "$CFG->wwwroot/$CFG->admin/tool/backuprestore/multibackup.php",'moodle/site:config'));
    $ADMIN->add('tool_backuprestore', new admin_externalpage('toolmultirestore', get_string('multirestore', 'tool_backuprestore'),  "$CFG->wwwroot/$CFG->admin/tool/backuprestore/multirestore.php",'moodle/site:config'));

}
