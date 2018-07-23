<?php

require_once($CFG->dirroot.'/user/filters/lib.php');

require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php'); // ecastro ULPGC include add_selection_all & get_selection_data($ufiltering
define('ACTIONS_LANG_PREFIX', 'bulkuseractions_');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}
/*
function add_selection_all($ufiltering) {
function get_selection_data($ufiltering) {

Now defined in admin/user/lib.php

*/

function check_action_capabilities($action, $require = false) {
    global $CFG;
    $requirecapability = NULL;
    if (file_exists($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/actions/'.$action.'/settings.php')) {
        include($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/actions/'.$action.'/settings.php');
    }

    if (is_null($requirecapability)) {
        if ($require) {
            print_error('action_nocaps');
        }
        return false;
    } else if (is_string($requirecapability)) {
        $caps = array( $requirecapability );
    } else if (is_array($requirecapability)) {
        $caps = $requirecapability;
    } else {
        if ($require) {
            print_error('action_nocaps');
        }
        return false;
    }
    
    $syscontext = context_system::instance();

    foreach ($caps as $cap) {
        if ($require) {
            require_capability($cap, $syscontext);
        } else {
            if (!has_capability($cap, $syscontext)) {
                return false;
            }
        }
    }
    
    return true;
}

function advuserbulk_get_string($identifier, $component, $a = NULL) {
    global $CFG;

    $identifier = clean_param($identifier, PARAM_STRINGID);
    if (empty($identifier)) {
        throw new coding_exception('Invalid string identifier. Most probably some illegal character is part of the string identifier. Please fix your get_string() call and string definition');
    }

    if (empty($component)) {
        throw new coding_exception('Parameter \'component\' for function advuserbulk_get_string() can not be empty. ');
    }

    if (strpos($component, ACTIONS_LANG_PREFIX) !== 0) {
        throw new coding_exception('Function advuserbulk_get_string() must be called only for actions strings (component \''.ACTIONS_LANG_PREFIX.'XXX\')');
    }

    $dir = substr($component, strlen(ACTIONS_LANG_PREFIX));

    $lang = current_language();

    $string = array();
    
    if (file_exists("$CFG->dirroot/$CFG->admin/tool/advuserbulk/actions/$dir/lang/$lang/$component.php")) {
        include("$CFG->dirroot/$CFG->admin/tool/advuserbulk/actions/$dir/lang/$lang/$component.php");
    } elseif (file_exists("$CFG->dirroot/$CFG->admin/tool/advuserbulk/actions/$dir/lang/en/$component.php")) {
        include("$CFG->dirroot/$CFG->admin/tool/advuserbulk/actions/$dir/lang/en/$component.php");
    } else {
        return "[[$identifier]]";
    }

    if(isset($string[$identifier])) {
        $string = $string[$identifier];
    } else {
        return "[[$identifier]]";
    }

    if ($a !== NULL) {
        if (is_object($a) or is_array($a)) {
            $a = (array)$a;
            $search = array();
            $replace = array();
            foreach ($a as $key=>$value) {
                if (is_int($key)) {
                    // we do not support numeric keys - sorry!
                    continue;
                }
                $search[]  = '{$a->'.$key.'}';
                $replace[] = (string)$value;
            }
            if ($search) {
                $string = str_replace($search, $replace, $string);
            }
        } else {
            $string = str_replace('{$a}', (string)$a, $string);
        }
    }

    return $string;
}

/**
 * This function generates the list of courses for <select> control
 * using the specified string filter and/or course id's filter
 *
 * @param string $strfilter The course name filter
 * @param array $arrayfilter Course ID's filter, NULL by default, which means not to use id filter
 * @return string
 */
function advuserbulk_gen_course_list($strfilter = '', $arrayfilter = NULL, $filtinvert = false) {
    $courselist = array();
    $catcnt = 0;
    // get the list of course categories
    $categories = make_categories_options();
    foreach ($categories as $catid => $catname) {
        // for each category, add the <optgroup> to the string array first
        $courselist[$catcnt] = '<optgroup label="' . $catname . '">';
        // get the course list in that category
        $courses = get_courses($catid, 'c.sortorder ASC', 'c.id, c.fullname, c.shortname'); // ecastro ULPGC improve course identification
        $coursecnt = 0;

        // for each course, check the specified filter
        foreach ($courses as $course) {
            if ((!empty($strfilter) && strripos($course->fullname, $strfilter) === false ) && (strripos($course->shortname, $strfilter) === false ) || ( $arrayfilter !== NULL && in_array($course->id, $arrayfilter) === $filtinvert )) {
                continue;
            }
            // if we pass the filter, add the option to the current string
            $courselist[$catcnt] .= '<option value="' . $course->id . '">' . $course->shortname.'-'.$course->fullname . '</option>';  // ecastro ULPGC improve course identification
            $coursecnt++;
        }

        // if no courses pass the filter in that category, delete the current string
        if ($coursecnt == 0) {
            unset($courselist[$catcnt]);
        } else {
            $courselist[$catcnt] .= '</optgroup>';
            $catcnt++;
        }
    }

    // return the html code with categorized courses
    return implode(' ', $courselist);
}
