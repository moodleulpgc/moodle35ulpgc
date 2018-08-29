<?php

/**
* @package mod-tracker
* @category mod
* @author Clifford Tham, Valery Fremaux > 1.8
* @date 02/12/2007
*
* From for showing used element list
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from view.php in mod/tracker
}


echo $OUTPUT->box_start('generalbox bugreport', null, array('width'=>'100%'));

tracker_loadelementsused($tracker, $used);

echo $OUTPUT->heading(tracker_getstring('elementsused', 'tracker'));

$orderstr = tracker_getstring('order', 'tracker');
$namestr = tracker_getstring('name');
$typestr = tracker_getstring('type', 'tracker');
$cmdstr = tracker_getstring('action', 'tracker');

$table = new html_table();
$table->head = array("<b>$orderstr</b>", "<b>$namestr</b>", "<b>$typestr</b>","<b>$cmdstr</b>");
$table->width = '100%';
$table->size = array(20, 250, 50, 100);
$table->align = array('left', 'center', 'center', 'center');

if (!empty($used)) {
    foreach ($used as $element) {
        $icontype = $OUTPUT->pix_icon("/types/{$element->type}", '', 'mod_tracker');
        if ($element->sortorder > 1) {
            $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'raiseelement', 'elementid' => $element->id);
            $url = new moodle_url('/mod/tracker/view.php', $params);
            $actions = '&nbsp;<a href="'.$url.'">'.$OUTPUT->pix_icon('t/up', '').'</a>';
        } else {
            $actions = '&nbsp;'.$OUTPUT->pix_icon('up_shadow', '', 'mod_tracker');
        }
        if ($element->sortorder < count($used)) {
            $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'lowerelement', 'elementid' => $element->id);
            $url = new moodle_url('/mod/tracker/view.php', $params);
            $actions .= '&nbsp;<a href="'.$url.'">'.$OUTPUT->pix_icon('t/down', '').'</a>';
        } else {
            $actions .= '&nbsp;'.$OUTPUT->pix_icon('down_shadow', '', 'mod_tracker');
        }

        $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'editelement', 'elementid' => $element->id, 'used' => 1, 'type' => $element->type);
        $url = new moodle_url('/mod/tracker/view.php', $params);
        $actions .= '&nbsp;<a href="'.$url.'">'.$OUTPUT->pix_icon('t/edit', '').'</a>';

        if ($element->type_has_options()) {
            $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'viewelementoptions', 'elementid' => $element->id);
            $url = new moodle_url('/mod/tracker/view.php', $params);
            $actions .= '&nbsp;<a href="'.$url.'" title="'.tracker_getstring('editoptions', 'mod_tracker').'">'.$OUTPUT->pix_icon('editoptions', '', 'mod_tracker').'</a>';
        }

        $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'removeelement', 'usedid' => $element->id);
        $url = new moodle_url('/mod/tracker/view.php', $params);
        $actions .= '&nbsp;<a href="'.$url.'">'.$OUTPUT->pix_icon('t/right', '').'</a>';

        if ($element->active) {
            if (!$element->mandatory) {
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setinactive', 'usedid' => $element->id);
                $url = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setinactive', 'tracker').'">'.$OUTPUT->pix_icon('t/hide', '').'</a>';
            } else {
                $actions .= '&nbsp;'.$OUTPUT->pix_icon('t/hide', '');
            }
        } else {
            $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setactive', 'usedid' => $element->id);
            $url = new moodle_url('/mod/tracker/view.php', $params);
            $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setactive', 'tracker').'">'.$OUTPUT->pix_icon('t/show', '').'</a>';
        }

        if ($element->has_mandatory_option()) {
            if ($element->mandatory) {
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setnotmandatory', 'usedid' => $element->id);
                $url = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setnotmandatory', 'tracker').'">'.$OUTPUT->pix_icon('notempty', '', 'mod_tracker').'</a>';
            } else {
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setmandatory', 'usedid' => $element->id);
                $url = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setmandatory', 'tracker').'">'.$OUTPUT->pix_icon('empty', '', 'mod_tracker').'</a>';
            }
        } else {
            if ($element->mandatory) {
                $actions .= '&nbsp;'.$OUTPUT->pix_icon('notempty', 'tracker');
            } else {
                $actions .= '&nbsp;'.$OUTPUT->pix_icon('empty', 'tracker');
            }
        }

        if ($element->has_private_option()) {
            if ($element->private) {
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setpublic', 'usedid' => $element->id);
                $url = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setpublic', 'tracker').'">'.$OUTPUT->pix_icon('t/locked', '').'</a>';
            } else {
                if (!$element->mandatory) {
                    $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'setprivate', 'usedid' => $element->id);
                    $url = new moodle_url('/mod/tracker/view.php', $params);
                    $actions .= '&nbsp;<a href="'.$url.'" title="'.get_string('setprivate', 'tracker').'">'.$OUTPUT->pix_icon('t/lock', '').'</a>';
                } else {
                    $actions .= '&nbsp;'.$OUTPUT->pix_icon('t/lock', '');
                }
            }
        } else {
            if ($element->private) {
                $actions .= '&nbsp;'.$OUTPUT->pix_icon('t/locked', 'core').'" />';
            } else {
                $actions .= '&nbsp;'.$OUTPUT->pix_icon('t/lock', 'core').'" />';
            }
        }

        $description = ($element->private) ? '<span class="dimmed">'.format_string($element->description).'</span>' : format_string($element->description) ;

        $table->data[] = array($element->sortorder, $description, $icontype, $actions);
    }
    echo html_writer::table($table);
} else {
    echo '<center>';
    print_string('noelements', 'tracker');
    echo '<br/></center>';
}

echo $OUTPUT->box_end();

