<?php
/**
 * tool_advuserbulk_preferences  tool library functions
 *
 * @package    tool_advuserbulk
 * @subpackage action_preferences
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function advuserbulk_preferences_set_prefs($prefs) {
    global $DB, $SESSION;
    
    if(!$prefs) {
        return;
    }

    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    if($prefs) {
        if ($rs = $DB->get_recordset_select('user', "id $in", $params, '', 'id, username, idnumber')) {
            foreach ($rs as $user) {
                set_user_preferences($prefs, $user->id);
            }
            $rs->close();
        }
    }
}

function advuserbulk_preferences_unset_prefs($prefs) {
    global $DB, $SESSION;

    if(!$prefs) {
        return;
    }

    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    if($prefs) {
        if ($rs = $DB->get_recordset_select('user', "id $in", $params, '', 'id, username, idnumber')) {
            foreach ($rs as $user) {
                foreach($prefs as $pref) {
                    unset_user_preference($pref, $user->id);
                }
            }
            $rs->close();
        }
    }
}


/**
 * Process display preferences
 * @param object $form post data including module selection settings as modXXX fields
 * @return void
 */

function advuserbulk_preferences_display_prefs($display_prefs) {

    $drawer = null;
    $blocks = null;
    if($display_prefs > 0) {
        $drawer = get_user_preferences('drawer-open-nav', null, $display_prefs);
        $blocks = get_user_preferences('sidepre-open', null, $display_prefs); 
    } elseif($display_prefs == -1) {
        $drawer = 0;
        $blocks = 0;
    } elseif($display_prefs == -2) {
        $drawer = 1;
        $blocks = 1;
    } elseif($display_prefs == -3) {
        $prefs = array('drawer-open-nav', 'sidepre-open');
        advuserbulk_preferences_unset_prefs($prefs);
    }
    
    $prefs = array();
    if($drawer !== null) {
        $prefs['drawer-open-nav'] = $drawer;
    }
    if($blocks !== null) {
        $prefs['sidepre-open'] = $blocks;
    }
    
    advuserbulk_preferences_set_prefs($prefs);
}

/**
 * Process display preferences
 * @param object $form post data including module selection settings as modXXX fields
 * @return void
 */

function advuserbulk_preferences_forum_prefs($forum_prefs) {
    global $DB, $SESSION;
    
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $fields = array('maildigest', 'maildisplay', 'autosubscribe', 'trackforums');
    $userpref = new stdClass();
    foreach($field as $field) {
        $userpref->$field = null;
    }
    $markasreadonnotification = null;
    
    if($forum_prefs > 0) {
        $markasreadonnotification = get_user_preferences('forum_markasreadonnotification', null, $forum_prefs);
        $user = $DB->get_record('user', array('id' => $forum_prefs), 'id, idnumber, '.implode(', ', $fields), MUST_EXIST);
        foreach($fields as $field) {
            $userpref->$field = $user->$field;
        }
    } elseif($forum_prefs == -1) {
        $markasreadonnotification = 0;
        foreach($field as $field) {
            $userpref->$field = 0;
        }        
    } elseif($forum_prefs == -2) {    
        $markasreadonnotification = 1;
        foreach($field as $field) {
            $userpref->$field = 1;
        }        
    } elseif($forum_prefs == -3) {
        $markasreadonnotification = 0;
        $userpref->maildigest = 0;
        $userpref->maildisplay = 2;
        $userpref->autosubscribe = 0;
        $userpref->trackforums = 1;
    }
    
    $prefs = array();
    if(isset($markasreadonnotification)) {
        $prefs['forum_markasreadonnotification'] = $markasreadonnotification;
    }
    
    if($prefs) {
        if ($rs = $DB->get_recordset_select('user', "id $in", $params, '', 'id, username, idnumber')) {
            foreach ($rs as $user) {
                set_user_preferences($prefs, $user->id);
                $userpref->id = $user->id;
                $userpref->username = $user->username;
                user_update_user($userpref, false, false);
            }
            $rs->close();
        }
    }
    
}

/**
 * Process display preferences
 * @param object $form post data including module selection settings as modXXX fields
 * @return void
 */

function advuserbulk_preferences_htmleditor_prefs($htmleditor_prefs) {

    $htmleditor = null;
    
    $editors = array_keys(editors_get_enabled());
    
    if($htmleditor_prefs && is_int($htmleditor_prefs)) {
        $htmleditor = get_user_preferences('htmleditor', null, $htmleditor_prefs);
    } elseif(in_array($htmleditor_prefs, $editors)) {
        $htmleditor = $htmleditor_prefs;
    } elseif($htmleditor_prefs == 'delete') {
        $prefs = array('htmleditor');
        advuserbulk_preferences_unset_prefs($prefs);
    }
    
    $prefs = array();
    if(isset($htmleditor)) {
        $prefs['htmleditor'] = $htmleditor;
    }
    
    advuserbulk_preferences_set_prefs($prefs);
    
}

/**
 * Process display preferences
 * @param object $form post data including module selection settings as modXXX fields
 * @return void
 */

function advuserbulk_preferences_messages_prefs($messages_prefs,  $message_loggedin, $message_loggedoff) {
    global $DB, $SESSION;

    $states = array('loggedin' => $message_loggedin, 'loggedoff' => $message_loggedoff); 
    
    $processors = array();
    foreach(get_message_processors() as $key => $processor) {
        if($processor->enabled && $processor->configured && $processor->available) {
            $processors[$key] = $key;
        }
    }
    
    $prefs = array();
    
    foreach($states as $logged => $state) {
    
        foreach($messages_prefs as $pref) {
            $prefs[$pref.'_'.$logged] = null;
        }
    
        if(!$state) {
            continue;
        } elseif(($state == 'none') || ($state == 'del') || in_array($state,  $processors)) {
            foreach($prefs as $pref => $value) {
                $prefs[$pref] = $state;
            }
        } elseif(is_int($state)) {
            foreach($prefs as $pref => $value) {
                $prefs[$pref] = get_user_preferences($pref, null, $state);;
            }
        }
    }

    $deletes = array();
    foreach($prefs as $pref => $value) {
        if(!is_null($value)) {
            if($value == 'delete') {
                $deletes[] = $pref;
                unset($prefs[$pref]);
            }
        } else {
            unset($prefs[$pref]);
        }
    }
    
    advuserbulk_preferences_unset_prefs($deletes);

    advuserbulk_preferences_set_prefs($prefs);
    
}
