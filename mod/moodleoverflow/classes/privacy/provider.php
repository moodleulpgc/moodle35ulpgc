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
 * Privacy Subsystem implementation for mod_moodleoverflow.
 *
 * @package    mod_moodleoverflow
 * @copyright  2018 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_moodleoverflow\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use \core_privacy\local\request\helper as request_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for mod_moodleoverflow implementing provider.
 *
 * @copyright  2018 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {

    // This trait must be included.
    use \core_privacy\local\legacy_polyfill;

    /** Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     *
     * @return collection the updated collection of metadata items.
     */
    public static function _get_metadata(collection $collection) {
        $collection->add_database_table('moodleoverflow_discussions',
            [
                'name'         => 'privacy:metadata:moodleoverflow_discussions:name',
                'userid'       => 'privacy:metadata:moodleoverflow_discussions:userid',
                'timemodified' => 'privacy:metadata:moodleoverflow_discussions:timemodified',
                'usermodified' => 'privacy:metadata:moodleoverflow_discussions:usermodified'
            ],
            'privacy:metadata:moodleoverflow_discussions');

        $collection->add_database_table('moodleoverflow_posts',
            [
                'discussion' => 'privacy:metadata:moodleoverflow_posts:discussion',
                'parent'     => 'privacy:metadata:moodleoverflow_posts:parent',
                'userid'     => 'privacy:metadata:moodleoverflow_posts:userid',
                'created'    => 'privacy:metadata:moodleoverflow_posts:created',
                'modified'   => 'privacy:metadata:moodleoverflow_posts:modified',
                'message'    => 'privacy:metadata:moodleoverflow_posts:message'
            ],
            'privacy:metadata:moodleoverflow_posts');

        $collection->add_database_table('moodleoverflow_read',
            [
                'userid'       => 'privacy:metadata:moodleoverflow_read:userid',
                'discussionid' => 'privacy:metadata:moodleoverflow_read:discussionid',
                'postid'       => 'privacy:metadata:moodleoverflow_read:postid',
                'firstread'    => 'privacy:metadata:moodleoverflow_read:firstread',
                'lastread'     => 'privacy:metadata:moodleoverflow_read:lastread'
            ],
            'privacy:metadata:moodleoverflow_read');

        $collection->add_database_table('moodleoverflow_subscriptions',
            [
                'userid'         => 'privacy:metadata:moodleoverflow_subscriptions:userid',
                'moodleoverflow' => 'privacy:metadata:moodleoverflow_subscriptions:moodleoverflow'
            ],
            'privacy:metadata:moodleoverflow_subscriptions');

        $collection->add_database_table('moodleoverflow_discuss_subs',
            [
                'userid'     => 'privacy:metadata:moodleoverflow_discuss_subs:userid',
                'discussion' => 'privacy:metadata:moodleoverflow_discuss_subs:discussion',
                'preference' => 'privacy:metadata:moodleoverflow_discuss_subs:preference'
            ],
            'privacy:metadata:moodleoverflow_discuss_subs');

        $collection->add_database_table('moodleoverflow_ratings',
            [
                'userid'      => 'privacy:metadata:moodleoverflow_ratings:userid',
                'postid'      => 'privacy:metadata:moodleoverflow_ratings:postid',
                'rating'      => 'privacy:metadata:moodleoverflow_ratings:rating',
                'firstrated'  => 'privacy:metadata:moodleoverflow_ratings:firstrated',
                'lastchanged' => 'privacy:metadata:moodleoverflow_ratings:lastchanged'
            ],
            'privacy:metadata:moodleoverflow_ratings');

        $collection->add_database_table('moodleoverflow_tracking',
            [
                'userid'           => 'privacy:metadata:moodleoverflow_tracking:userid',
                'moodleoverflowid' => 'privacy:metadata:moodleoverflow_tracking:moodleoverflowid'
            ],
            'privacy:metadata:moodleoverflow_tracking');

        $collection->link_subsystem('core_files',
            'privacy:metadata:core_files'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     *
     * @return contextlist $contextlist The list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) {
        // Fetch all forum discussions, forum posts, ratings, tracking settings and subscriptions.
        $sql = "SELECT c.id
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {moodleoverflow} mof ON mof.id = cm.instance
                LEFT JOIN {moodleoverflow_discussions} d ON d.moodleoverflow = mof.id
                LEFT JOIN {moodleoverflow_posts} p ON p.discussion = d.id
                LEFT JOIN {moodleoverflow_read} r ON r.moodleoverflowid = mof.id
                LEFT JOIN {moodleoverflow_subscriptions} s ON s.moodleoverflow = mof.id
                LEFT JOIN {moodleoverflow_discuss_subs} ds ON ds.moodleoverflow = mof.id
                LEFT JOIN {moodleoverflow_ratings} ra ON ra.moodleoverflowid = mof.id
                LEFT JOIN {moodleoverflow_tracking} track ON track.moodleoverflowid = mof.id
                WHERE (
                    d.userid = :duserid OR
                    d.usermodified = :dmuserid OR
                    p.userid = :puserid OR
                    r.userid = :ruserid OR
                    s.userid = :suserid OR
                    ds.userid = :dsuserid OR
                    ra.userid = :rauserid OR
                    track.userid = :userid
                )
         ";

        $params = [
            'modname'      => 'moodleoverflow',
            'contextlevel' => CONTEXT_MODULE,
            'duserid'      => $userid,
            'dmuserid'     => $userid,
            'puserid'      => $userid,
            'ruserid'      => $userid,
            'suserid'      => $userid,
            'dsuserid'     => $userid,
            'rauserid'     => $userid,
            'userid'       => $userid
        ];

        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT
                    c.id AS contextid,
                    mof.*,
                    cm.id AS cmid,
                    s.userid AS subscribed,
                    track.userid AS tracked
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid
                INNER JOIN {modules} m ON m.id = cm.module
                INNER JOIN {moodleoverflow} mof ON mof.id = cm.instance
                LEFT JOIN {moodleoverflow_subscriptions} s ON s.moodleoverflow = mof.id AND s.userid = :suserid
                LEFT JOIN {moodleoverflow_ratings} ra ON ra.moodleoverflowid = mof.id AND ra.userid = :rauserid
                LEFT JOIN {moodleoverflow_tracking} track ON track.moodleoverflowid = mof.id AND track.userid = :userid
                WHERE (
                    c.id {$contextsql}
                )
                ";

        $params = [
            'suserid'  => $userid,
            'rauserid' => $userid,
            'userid'   => $userid
        ];
        $params += $contextparams;

        // Keep a mapping of moodleoverflowid to contextid.
        $mappings = [];

        $forums = $DB->get_recordset_sql($sql, $params);
        foreach ($forums as $forum) {
            $mappings[$forum->id] = $forum->contextid;

            $context = \context::instance_by_id($mappings[$forum->id]);

            // Store the main moodleoverflow data.
            $data = request_helper::get_context_data($context, $user);
            writer::with_context($context)->export_data([], $data);
            request_helper::export_context_files($context, $user);

            // Store relevant metadata about this forum instance.
            data_export_helper::export_subscription_data($forum);
            data_export_helper::export_tracking_data($forum);
        }

        $forums->close();

        if (!empty($mappings)) {
            // Store all discussion data for this moodleoverflow.
            data_export_helper::export_discussion_data($userid, $mappings);
            // Store all post data for this moodleoverflow.
            data_export_helper::export_all_posts($userid, $mappings);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        // Additional checks that are necessary because $context can be ANY kind of context, regardless of its type.
        // Check that this is a context_module.
        if (!$context instanceof \context_module) {
            return;
        }

        // Get the course module (and verify that it is actually a Moodleoverflow module).
        $cm = get_coursemodule_from_id('moodleoverflow', $context->instanceid);
        if (!$cm) {
            return;
        }
        $forum = $DB->get_record('moodleoverflow', ['id' => $cm->instance]);

        $DB->delete_records('moodleoverflow_subscriptions', ['moodleoverflow' => $forum->id]);
        $DB->delete_records('moodleoverflow_read', ['moodleoverflowid' => $forum->id]);
        $DB->delete_records('moodleoverflow_tracking', ['moodleoverflowid' => $forum->id]);
        $DB->delete_records('moodleoverflow_ratings', ['moodleoverflowid' => $forum->id]);
        $DB->delete_records('moodleoverflow_discuss_subs', ['moodleoverflow' => $forum->id]);
        $DB->delete_records_select(
            'moodleoverflow_posts',
            "discussion IN (SELECT id FROM {moodleoverflow_discussions} WHERE moodleoverflow = :forum)",
            [
                'forum' => $forum->id,
            ]
        );
        $DB->delete_records('moodleoverflow_discussions', ['moodleoverflow' => $forum->id]);

        // Delete all files from the posts.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_moodleoverflow', 'attachment');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            // Additional checks, probably unnecessary as contexts stem from get_contexts_for_userid.
            // Check that this is a context_module.
            if (!$context instanceof \context_module) {
                continue;
            }

            // Get the course module (and verify that it is actually a Moodleoverflow module).
            $cm = get_coursemodule_from_id('moodleoverflow', $context->instanceid);
            if (!$cm) {
                continue;
            }
            // Get the module instance.
            $forum = $DB->get_record('moodleoverflow', ['id' => $cm->instance]);

            $DB->delete_records('moodleoverflow_read', [
                'moodleoverflowid' => $forum->id,
                'userid'           => $userid]);

            $DB->delete_records('moodleoverflow_subscriptions', [
                'moodleoverflow' => $forum->id,
                'userid'         => $userid]);

            $DB->delete_records('moodleoverflow_discuss_subs', [
                'moodleoverflow' => $forum->id,
                'userid'         => $userid]);

            $DB->delete_records('moodleoverflow_tracking', [
                'moodleoverflowid' => $forum->id,
                'userid'           => $userid]);

            // Do not delete ratings but reset userid.
            $ratingsql = "userid = :userid AND discussionid IN
            (SELECT id FROM {moodleoverflow_discussions} WHERE moodleoverflow = :forum)";
            $ratingparams = [
                'forum'  => $forum->id,
                'userid' => $userid
            ];
            $DB->set_field_select('moodleoverflow_ratings', 'userid', 0, $ratingsql, $ratingparams);

            // Do not delete forum posts.
            // Update the user id to reflect that the content has been deleted, and delete post contents.
            $postsql = "userid = :userid AND discussion IN
            (SELECT id FROM {moodleoverflow_discussions} WHERE moodleoverflow = :forum)";
            $postparams = [
                'forum'  => $forum->id,
                'userid' => $userid
            ];

            $DB->set_field_select('moodleoverflow_posts', 'message', '', $postsql, $postparams);
            $DB->set_field_select('moodleoverflow_posts', 'messageformat', FORMAT_PLAIN, $postsql, $postparams);
            $DB->set_field_select('moodleoverflow_posts', 'userid', 0, $postsql, $postparams);

            // Do not delete discussions but reset userid.
            $discussionselect = "moodleoverflow = :forum AND userid = :userid";
            $disuccsionsparams = ['forum' => $forum->id, 'userid' => $userid];
            $DB->set_field_select('moodleoverflow_discussions', 'name', '', $discussionselect, $disuccsionsparams);
            $DB->set_field_select('moodleoverflow_discussions', 'userid', 0, $discussionselect, $disuccsionsparams);
            $discussionselect = "moodleoverflow = :forum AND usermodified = :userid";
            $disuccsionsparams = ['forum' => $forum->id, 'userid' => $userid];
            $DB->set_field_select('moodleoverflow_discussions', 'usermodified', 0, $discussionselect, $disuccsionsparams);

            // Delete attachments.
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_moodleoverflow', 'attachment');
        }
    }
}