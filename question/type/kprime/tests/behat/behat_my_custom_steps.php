<?php

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
    public function iOutput($arg1) {
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
    public function elementWithXpathShouldExist($arg1) {

      $session = $this->getSession();
      $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToxpath('css', $arg1)
      );

      if(null === $element) {
        throw new Exception($arg1 . 'not found');
      }
    }


    /**
    * @Then element with css :arg1 should exist
    */
    public function elementWithCssShouldExist($arg1) {

      $element = $this->getSession()->getPage()->find('css', $arg1);


      if(null === $element) {
        throw new Exception($arg1 . 'not found');
      }
    }


    /**
    * @When I click on css :arg1
    */
    public function clickOnCss($arg1) {

      $element = $this->getSession()->getPage()->find('css', $arg1);

      if(null === $element) {
        throw new Exception($arg1 . 'not found');
      }
      $element->click();
    }

    /**
    * @When I attach file :arg1 to :arg2
    */
    public function iAttachFile($arg1, $arg2) {
	$element = $this->getSession()->getPage()->find('css', $arg2);
	$element->attachFile($arg1);
    }

}


