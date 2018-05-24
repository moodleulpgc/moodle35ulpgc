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
 * Privacy Subsystem implementation for mod_qcreate.
 *
 * @package    mod_qcreate
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qcreate\privacy;

use \core_privacy\local\request\writer;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\deletion_criteria;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\helper;
use \core_privacy\manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/qcreate/lib.php');
require_once($CFG->dirroot . '/mod/qcreate/locallib.php');

/**
 * Privacy Subsystem implementation for mod_qcreate.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin_provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   collection  $items  The collection to add metadata to.
     * @return  collection  The array of metadata
     */
    public static function get_metadata(collection $items) : collection {
        // The table 'qcreate' stores a record for each qcreate activity.
        // It does not contain user personal data, but data is returned from it for contextual requirements.

        // The table 'qcreate_grades' contains the current grade for each qcreate/user combination.
        $items->add_database_table('qcreate_grades', [
                'qcreateid'                  => 'privacy:metadata:qcreate_grades:qcreateid',
                'questionid'                => 'privacy:metadata:qcreate_grades:questionid',
                'grade'                 => 'privacy:metadata:qcreate_grades:grade',
                'gradecomment'                 => 'privacy:metadata:qcreate_grades:gradecomment',
                'teacher'                 => 'privacy:metadata:qcreate_grades:teacher',
                'timemarked'          => 'privacy:metadata:qcreate_grades:timemodified',
            ], 'privacy:metadata:qcreate_grades');

        // The qcreate links to the 'core_question' subsystem for all question functionality.
        $items->add_subsystem_link('core_question', [], 'privacy:metadata:core_question');

        // Although the qcreate supports the core_completion API and defines custom completion items, these will be
        // noted by the manager as all activity modules are capable of supporting this functionality.

        return $items;
    }

    /**
     * Get the list of contexts where the specified user has attempted a qcreate activity, or been involved with manual marking
     * and/or grading of a qcreate activity.
     *
     * @param   int             $userid The user to search.
     * @return  contextlist     $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {

        // Select the context of any qcreate attempt where a user has a grade
        // (even if not manual graded by a teacher), or is the manual grader of a created question.
        $sql = 'SELECT c.id
                FROM {question} q
                LEFT JOIN {user} u ON u.id = q.createdby
                LEFT JOIN {question_categories} qc ON qc.id = q.category
                LEFT JOIN {qcreate_grades} g ON g.questionid = q.id
                LEFT JOIN {context} c ON c.id = qc.contextid
                LEFT JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                LEFT JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                LEFT JOIN {qcreate} a ON a.id = cm.instance
            WHERE
                g.teacher = :qauserid OR
                q.createdby = :qouserid';

            $params = array(
                    'contextlevel'      => CONTEXT_MODULE,
                    'modname'           => 'qcreate',
                    'qauserid'          => $userid,
                    'qouserid'          => $userid,
            );

        $resultset = new contextlist();
        $resultset->add_from_sql($sql, $params);

        return $resultset;
    }

    /**
     * Export all data for all users in the specified context.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        foreach ($contextlist->get_contexts() as $context) {
            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            $user = $contextlist->get_user();

            $qcreatedata = helper::get_context_data($context, $user);
            helper::export_context_files($context, $user);

            writer::with_context($context)->export_data([], $qcreatedata);
            $qcreateobj = new \qcreate($context, null, null);

            // I need to find out if I'm a student or a teacher.
            if ($userids = self::get_graded_users($user->id, $qcreateobj)) {
                // Return teacher info.
                $currentpath = [get_string('privacy:graderpath', 'mod_qcreate')];
                foreach ($userids as $studentuserid) {
                    $studentpath = array_merge($currentpath, [$studentuserid->id]);
                    static::export_grade($qcreateobj, $studentuserid, $context, $studentpath, true);
                }
            }
            $currentpath = [get_string('privacy:studentpath', 'mod_qcreate')];
            $studentpath = array_merge($currentpath, [$user->id]);
            static::export_grade($qcreateobj, $user, $context, $studentpath, true);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $cm = get_coursemodule_from_id('qcreate', $context->instanceid);
        if (!$cm) {
            // Only qcreate module will be handled.
            return;
        }
        $qcreateobj = new \qcreate($context, null, null);

        // This will delete all local grades for this qcreate.
        $DB->delete_records('qcreate_grades', array('qcreateid' => $qcreateobj->get_instance()->id));

    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist as $context) {
            $cm = get_coursemodule_from_id('qcreate', $context->instanceid);
            if (!$cm) {
                // Only qcreate module will be handled.
                continue;
            }
            $qcreateobj = new \qcreate($context, null, null);

            // This will delete all local grades for this user and this qcreate instance.
            $qcreateobj->delete_user_local_grades($user->id);

            // If this user has graded any question we need to anonymise the user id in the grade.
            $params = ['grader' => $user->id, 'qcreateid' => $qcreateobj->get_instance()->id];
            $DB->set_field_select('qcreate_grades', 'gradecomment', '',
                    'teacher = :grader AND qcreateid = :qcreateid', $params);
            $DB->set_field_select('qcreate_grades', 'teacher', 0,
                    'teacher = :grader AND qcreateid = :qcreateid', $params);
        }
    }

    /**
     * Find out if this user has graded any users.
     *
     * @param  int $userid The user ID (potential teacher).
     * @param  qcreate $qcreateobj The qcreate object.
     * @return array If successful an array of objects with userids that this user graded, otherwise false.
     */
    protected static function get_graded_users(int $userid, \qcreate $qcreateobj) {
        $params = ['grader' => $userid, 'qcreateid' => $qcreateobj->get_instance()->id];

        $sql = "SELECT DISTINCT q.createdby AS id
                  FROM {qcreate_grades} g
                  LEFT JOIN {question} q ON q.id = g.questionid
                 WHERE g.teacher = :grader AND g.qcreateid = :qcreateid";

        $useridlist = new useridlist($userid, $qcreateobj->get_instance()->id);
        $useridlist->add_from_sql($sql, $params);

        $userids = $useridlist->get_userids();
        return ($userids) ? $userids : false;
    }

    /**
     * Exports qcreate grade data for a user.
     *
     * @param  \qcreate         $qcreateobj       The qcreate object
     * @param  \stdClass        $user             The user object
     * @param  \context_module $context           The context
     * @param  array           $path              The path for exporting data
     * @param  bool|boolean    $exportforteacher  A flag for if this is exporting data as a teacher.
     */
    protected static function export_grade(\qcreate $qcreateobj, \stdClass $user, \context_module $context, array $path,
            bool $exportforteacher = false) {
        if ($exportforteacher) {
            // We need to export all local grades made by this teacher.
            $grades = $qcreateobj->get_all_local_grades($user->id, true);
            foreach ($grades as $grade) {
                self::export_grade_data($grade, $context, $path);
            }
        }
        // Then we need to export local grades for all questions created.
        $grades = $qcreateobj->get_all_local_grades($user->id, false);

        foreach ($grades as $grade) {
                self::export_grade_data($grade, $context, $path);
        }
    }

    /**
     * Formats and then exports the user's grade data.
     *
     * @param  \stdClass $grade The assign grade object
     * @param  \context $context The context object
     * @param  array $currentpath Current directory path that we are exporting to.
     */
    protected static function export_grade_data(\stdClass $grade, \context $context, array $currentpath) {
        $gradedata = (object)[
            'timemarked' => transform::datetime($grade->grademodified),
            'teacher' => transform::user($grade->grader),
            'grade' => $grade->bestgrade,
            'question' => $grade->questiongraded,
            'gradecomment' => $grade->teachercomment,
        ];
        writer::with_context($context)
            ->export_data(array_merge($currentpath, [get_string('privacy:gradepath', 'mod_qcreate')]), $gradedata);
    }
}
