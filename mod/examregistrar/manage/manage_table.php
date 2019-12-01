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
 * Base class for the table used by a {@link examregistrar_manage}.
 *
 * @package   mod_examregistrar
 * @copyright 2014 Enrique castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');


/**
 * Base class for the table used by a {@link examregistrar_manage}.
 *
 * @copyright 2010 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examregistrar_management_table extends flexible_table {
    /** @var moodle_url the URL of outer form for this table. */
    protected $wrapurl;

    /** @var array the hiddenparams additional to those in wrapurl querystring. */
    protected $hiddenparams;

    /// TODO      /// TODO      /// TODO      /// TODO      /// TODO      /// TODO
    /** @var array of menus for outer form. Each menu has a separate submit button*/
    protected $menus;

    /** @var array the actions in menu for outer form. */
    protected $actions;

    /** @var array some additional form fields for outer form. associative action,additional */
    protected $additionalfields;


    /**
     * Sets property with url value
     * @param moodle_url $url for management form
     * @return void
     */
    public function set_wrapformurl($url) {
        $this->wrapurl = new moodle_url($url);
    }


    /**
     * Sets property with hidden params array
     * @param array $params an associative array param=>value
     * @return void
     */
    public function set_hiddenparams($params) {
        $this->hiddenparams = $params;
    }

    /**
     * Adds new params to property with hidden params array
     * @param array $params an associative array param=>value
     * @return void
     */
    public function add_hiddenparams($params) {
        $this->hiddenparams = $this->hiddenparams + $params;
    }

    /**
     * Sets property with action menu array
     * @param array $actions an associative array action=>displayname (from get_string)
     * @return void
     */
    public function set_actionsmenu($actions) {
        $this->actions = $actions;
    }

    /**
     * Sets property with property with additional form fields
     * @param array $fields an array of HTML form input fields
     * @return void
     */
    public function set_additionalfields($action, $fields) {
        $this->additionalfields = array();
        $this->additionalfields[$action] = $fields;
    }

    /**
     * Adds new params to property with additional form fields
     * @param array $fields an array of HTML form input fields
     * @return void
     */
    public function add_additionalfields($action, $fields) {
        $this->additionalfields[$action] = $this->additionalfields[$action] + $fields;
    }

    /**
     * Generate the display of the checkbox column.
     * @param object $element the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_checkbox($element) {
        if ($element->id) {
            return '<input type="checkbox" name="items[]" value="'.$element->id.'" />';
        } else {
            return '';
        }
    }

    /**
     * Formats the display output in a column with separate name/idnumber
     * @param string $name the table row being output.
     * @param string $idnumber the table row being output.
     * @param array $options some options, not used
     * @return string HTML content to go inside the td.
     */
    public function col_formatitem($name, $idnumber, $options = false) {

        $formattedname = '';
        $formattedtail = '';
        if($name) {
            $formattedname = format_text($name, FORMAT_MOODLE, array('filter'=>false, 'para'=>false));
        }
        if($idnumber) {
            $formattedtail = format_string($idnumber, FORMAT_MOODLE, array('filter'=>false, 'para'=>false));
            if($name) {
                $formattedtail = " ($formattedtail)";
            }
        }

        $name = $formattedname.$formattedtail;

        if($options) {
            $name = html_writer::span($name, $options);
        }

        return $name;
    }


    public function wrap_html_start() {
        if ($this->is_downloading() || !$this->actions) {
            return;
        }

        $url = $this->wrapurl;
        $url->param('sesskey', sesskey());

        echo '<div id="exregtablecontainer" class=" examregmanagementform clearfix " >';
        echo '<form id="examregistrarmanagementtableform" method="post" action="' . $url->out_omit_querystring() . '">';

        echo html_writer::input_hidden_params($url);
        if($this->hiddenparams) {
            foreach ($this->hiddenparams as $key => $value) {
                $attributes = array('type'=>'hidden', 'name'=>$key, 'value'=>$value);
                echo html_writer::empty_tag('input', $attributes)."\n";
            }
        }
        echo '<div>';
    }

    public function wrap_html_finish() {
        if ($this->is_downloading() || !$this->actions) {
            return;
        }

        echo '<div id="tablecommands">';
        echo '<br />&nbsp;&nbsp;';
        $this->submit_buttons();
        echo '</div>';

        // Close the form.
        echo '</div>';
        echo '</form></div>';
    }

    /**
     * Output any submit buttons required by the $this->includecheckboxes form.
     */
    protected function submit_buttons() {
        global $PAGE;

        //$actionsmenu = array(''=>get_string('choose'));
        $actionsmenu = array(''=>get_string('choose')) + $this->actions;
        echo html_writer::label(get_string('withselecteddo', 'examregistrar').': ', 'batch');
        echo html_writer::select($actionsmenu, "batch", '');
        echo ' &nbsp; ';

        /// TODO      /// TODO      /// TODO      /// TODO      /// TODO      /// TODO
        /** @var array of menus for outer form. Each menu has a separate submit button*/
        /// use  $this->menus;

        if($this->additionalfields) {
            foreach($this->additionalfields as $action => $items) {
                foreach($items as $item) {
                    echo $item;
                }
            }
            echo ' &nbsp; ';
        }
        echo '<input type="submit" value="'.get_string('go', 'examregistrar').'" />'."\n";
    }
}
