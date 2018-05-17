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

function xmldb_dialogue_upgrade($oldversion=0) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    /**
     * Moodle v2.8.0 release upgrade line.
     */
    if($oldversion < 2015051100) {
        // Archive off existing dialogue tables, so can upgrade dialogues selectively.
        $tables = array('dialogue_conversations', 'dialogue_entries', 'dialogue_read');
        foreach($tables as $table) {
            $tablearchive = $table . '_old';
            if ($dbman->table_exists($table) and !$dbman->table_exists($tablearchive)) {
                $dbman->rename_table(new xmldb_table($table), $tablearchive);
            }
            // drop old indexes? $dbman->drop_index($xmldb_table, $xmldb_index)
        }
        echo $OUTPUT->notification('Renaming old dialogue module tables', 'notifysuccess');


        //modigy dialogue table settings


        $table = new xmldb_table('dialogue');
        $field = new xmldb_field('maxattachments', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'multipleconversations');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxbytes', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'maxattachments');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('usecoursegroups', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'maxbytes');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('alternatemode', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'usecoursegroups');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('notifications', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'alternatemode');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('notificationcontent', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'notifications');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // add new tables
        // Dialogue conversations
        if (!$dbman->table_exists('dialogue_conversations')) {
            $table = new xmldb_table('dialogue_conversations');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('dialogueid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_index('dialogueid', XMLDB_INDEX_NOTUNIQUE, array('dialogueid'));
            $dbman->create_table($table);
        }
        // Dialogue participants
        if (!$dbman->table_exists('dialogue_participants')) {
            $table = new xmldb_table('dialogue_participants');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('dialogueid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('conversationid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_index('userid-dialogueid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'dialogueid'));
            $table->add_index('userid-conversationid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'conversationid'));
            $dbman->create_table($table);
        }

        // Dialogue messages
        if (!$dbman->table_exists('dialogue_messages')) {
            $table = new xmldb_table('dialogue_messages');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('dialogueid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('conversationid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('conversationindex', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('authorid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('body', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null);
            $table->add_field('bodyformat', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('bodytrust', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('attachments', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('state', XMLDB_TYPE_CHAR, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_index('authorid', XMLDB_INDEX_NOTUNIQUE, array('authorid'));
            $dbman->create_table($table);
        }

        // Dialogue flags
        if (!$dbman->table_exists('dialogue_flags')) {
            $table = new xmldb_table('dialogue_flags');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('dialogueid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('conversationid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('flag', XMLDB_TYPE_CHAR, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_index('userid-dialogueid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'dialogueid'));
            $table->add_index('userid-conversationid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'conversationid'));
            $table->add_index('userid-messageid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'messageid'));
            $dbman->create_table($table);
        }

        // Dialogue bulk opener rules
        if (!$dbman->table_exists('dialogue_bulk_opener_rules')) {
            $table = new xmldb_table('dialogue_bulk_opener_rules');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('dialogueid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('conversationid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('type', XMLDB_TYPE_CHAR, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
            $table->add_field('sourceid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('includefuturemembers', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('cutoffdate', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_field('lastrun', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);
        }

        // copy settings
        $DB->set_field('dialogue', 'maxattachments', 1, array());
        $DB->set_field('dialogue', 'alternatemode', 1, array('dialoguetype'=>0));
        $DB->set_field('dialogue', 'notifications', 1, array('notifydefault'=>1));

        upgrade_mod_savepoint(true, 2015051100, 'dialogue');
    }


    if($oldversion < 2015051103) {

        $table = new xmldb_table('dialogue');
        $fields = array('deleteafter', 'dialoguetype', 'notifydefault', 'edittime', 'recipients');
        foreach($fields as $fieldname) {
            $field = new xmldb_field($fieldname);
            // Conditionally launch drop field disableprinting
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        if ($dbman->table_exists('dialogue_conversations_old')) {

            $params = array();
            $timewhere = '';
            if($firstmessage = $DB->get_records_menu('dialogue_messages', array(), 'timemodified DESC', 'id, timemodified', 0, 1)) {
                $timewhere = " AND dco.timemodified <= ? ";
                $params[] = reset($firstmessage);
            }
            $sql = "SELECT dco.*, d.course
                    FROM {dialogue_conversations_old} dco
                    JOIN {dialogue} d ON dco.dialogueid = d.id
                    WHERE dco.dialogueid > 0 $timewhere ";

            $rs_conversations = $DB->get_recordset_sql($sql, $params);
            if($rs_conversations->valid()) {
                foreach($rs_conversations as $conversation) {
                    $new = new stdclass();
                    $new->course = $conversation->course;
                    $new->dialogueid = $conversation->dialogueid;
                    $new->subject = $conversation->subject;
                    if($cid = $DB->insert_record('dialogue_conversations', $new)) {
                        // conversation inserted, process

                        $users = array();
                        $users[$conversation->userid] = $conversation->userid;
                        $users[$conversation->recipientid] = $conversation->recipientid;
                        $users[$conversation->lastid] = $conversation->lastid;
                        $users[$conversation->lastrecipientid] = $conversation->lastrecipientid;

                        // insert entries as messages
                        if($entries = $DB->get_records('dialogue_entries_old', array('dialogueid'=>$conversation->dialogueid,
                                                                                    'conversationid'=>$conversation->id), 'timecreated ASC')) {
                            $index = 0;
                            foreach($entries as $entry) {
                                $index += 1;
                                $message = new stdclass();
                                $message->dialogueid = $conversation->dialogueid;
                                $message->conversationid = $cid;
                                $message->conversationindex = $index;
                                $message->authorid = $entry->userid;
                                $message->body = $entry->text;
                                $message->bodyformat = $entry->format;
                                $message->bodytrust = $entry->trust;
                                $message->attachments = $entry->attachment;
                                $message->state = $conversation->closed ? 'closed' : 'open';
                                $message->timecreated = $entry->timecreated;
                                $message->timemodified = $entry->timemodified;
                                if($mid = $DB->insert_record('dialogue_messages', $message)) {
                                    // process flags
                                    if($entry->userid) {
                                        $users[$entry->userid] = $entry->userid;
                                        $flag = new stdclass();
                                        $flag->dialogueid = $conversation->dialogueid;
                                        $flag->conversationid = $cid;
                                        $flag->messageid = $mid;

                                        $flag->flag = 'read';
                                        $flag->userid = $message->authorid;
                                        if(!$DB->record_exists('dialogue_flags', array('messageid'=>$mid, 'userid'=>$flag->userid) )) {
                                            $DB->insert_record('dialogue_flags', $flag);
                                        }
                                    }
                                    if($entry->recipientid) {
                                        $flag->flag = 'sent';
                                        $users[$entry->recipientid] = $entry->recipientid;
                                        $flag->userid = $entry->recipientid;
                                        if($DB->record_exists('dialogue_read_old', array('entryid'=>$entry->id, 'userid'=>$flag->userid))) {
                                            $flag->flag = 'read';
                                        }
                                        if(!$DB->record_exists('dialogue_flags', array('messageid'=>$mid, 'userid'=>$flag->userid) )) {
                                            $DB->insert_record('dialogue_flags', $flag);
                                        }

                                        if($message->attachments) {
                                            $DB->set_field('files', 'itemid', $mid, array('component'=>'mod_dialogue', 'filearea'=>'attachment', 'itemid'=>$entry->id));
                                        }
                                    }
                                }
                            }
                        }

                        // now insert participants
                        foreach($users as $userid) {
                            if(!$DB->record_exists('dialogue_participants', array('dialogueid'=>$conversation->dialogueid,
                                                                                'conversationid'=>$cid,
                                                                                'userid'=>$userid))) {
                                $new = new stdclass();
                                $new->dialogueid = $conversation->dialogueid;
                                $new->conversationid = $cid;
                                $new->userid = $userid;
                                $DB->insert_record('dialogue_participants', $new);
                            }
                        }

                        //OK, end processing, set old table done
                        $DB->set_field('dialogue_conversations_old', 'dialogueid', -$conversation->dialogueid, array('id'=>$conversation->id));
                    }
                }

            }
            $rs_conversations->close();
        }
        upgrade_mod_savepoint(true, 2015051103, 'dialogue');
    }

    return true;
}
