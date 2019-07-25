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

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationExcpetion as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundExcpetion;

class behat_my_custom_steps extends behat_base{

    /**
     * @When I output :arg1
     */
    public function i_output($arg1) {
        fwrite(STDOUT, $arg1);
    }

    /**
     * @When I wait :sec
     */
    public function wait($sec) {
        sleep($sec);
    }

    /**
     * @Then element with xpath :arg1 should exist
     */
    public function element_with_xpath_should_exist($arg1) {

        $session = $this->getSession();
        $element = $session->getPage()->find('xpath',
            $session->getSelectorsHandler()->selectorToxpath('css', $arg1));

        if (null === $element) {
            throw new Exception($arg1 . 'not found');
        }
    }

    /**
     * @Then element with css :arg1 should exist
     */
    public function element_with_css_should_exist($arg1) {

        $element = $this->getSession()->getPage()->find('css', $arg1);

        if (null === $element) {
            throw new Exception($arg1 . 'not found');
        }
    }

    /**
     * @When I click on css :arg1
     */
    public function click_on_css($arg1) {

        $element = $this->getSession()->getPage()->find('css', $arg1);

        if (null === $element) {
            throw new Exception($arg1 . 'not found');
        }

        $element->click();
    }

    /**
     * @When I attach file :arg1 to :arg2
     */
    public function i_attach_file($arg1, $arg2) {

        $element = $this->getSession()->getPage()->find('css', $arg2);
        $element->attachFile($arg1);
    }
}


