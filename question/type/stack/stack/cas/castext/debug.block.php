<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.
defined('MOODLE_INTERNAL') || die();

/**
 * @copyright  2017 Aalto University.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

require_once("block.interface.php");

class stack_cas_castext_debug extends stack_cas_castext_block {

    public function extract_attributes(&$tobeevaluatedcassession, $conditionstack = null) {
        // The debug block does nothing but reads the data from the context and outputs details based on it.
        return;
    }

    public function content_evaluation_context($conditionstack = array()) {
        return $conditionstack;
    }

    public function process_content($evaluatedcassession, $conditionstack = null) {
        $output = "<h3>Debug output</h3>\n";
        $output .= "<h5>Compiled castext:</h5>\n";
        $output .= "<pre>";

        $rootnode = $this->get_node();
        while ($rootnode->parent !== null) {
            $rootnode = $rootnode->parent;
        }
        $text = $rootnode->to_string();

        $text = str_replace("[[", "[&#8203;[", $text);
        $text = str_replace("]]", "]&#8203;]", $text);
        $output .= $text;

        $output .= "</pre>\n";

        $output .= "<h5>CAS session values:</h5>\n";
        $output .= "<table><tr><th>key</th><th>casstring</th><th>rawcasstring</th><th>value</th><th>dispvalue</th></tr>\n";

        foreach ($evaluatedcassession->get_session() as $cs) {
            $output .= "<tr>";
            $output .= "<td>" . $cs->get_key() . "</td>";
            $output .= "<td>" . $cs->get_casstring() . "</td>";
            $output .= "<td>" . $cs->get_raw_casstring() . "</td>";
            $output .= "<td>" . $cs->get_value() . "</td>";
            $output .= "<td>" . $cs->get_dispvalue() . "</td>";
            $output .= "</tr>";
        }

        $output .= "</table>";

        $this->get_node()->convert_to_text($output);

        return false;
    }

    public function validate_extract_attributes() {
        return array();
    }
}

