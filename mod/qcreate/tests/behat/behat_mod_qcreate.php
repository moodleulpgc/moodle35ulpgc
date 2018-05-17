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
 * Steps definitions related to mod_qcreate.
 *
 * @package   mod_qcreate
 * @category  test
 * @copyright 2017 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../question/tests/behat/behat_question_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to mod_qcreate.
 *
 * @copyright 2017 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_qcreate extends behat_question_base {

    /**
     * Adds a question to the existing Question creation activity with filling the form.
     *
     * The form for creating a question should be on one page.
     *
     * @When /^I add a "(?P<question_type_string>(?:[^"]|\\")*)" question to the "(?P<qcreate_name_string>(?:[^"]|\\")*)" qcreate with:$/
     * @param string $questiontype
     * @param string $qcreatename
     * @param TableNode $questiondata with data for filling the add question form
     */
    public function i_add_question_to_the_qcreate_with($questiontype, $qcreatename, TableNode $questiondata) {
        $qcreatename = $this->escape($qcreatename);
        $questiontype = $this->escape($questiontype);
        $this->execute('behat_general::click_link', $qcreatename);
        $this->execute('behat_general::click_link', $questiontype);
        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $questiondata);
        $this->execute("behat_forms::press_button", 'id_submitbutton');
    }
}
