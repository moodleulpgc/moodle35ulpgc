@qtype @qtype_mtf @qtype_mtf_step_11
Feature: Step 11

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And I log in as "admin"

 @javascript
  Scenario: TESTCASE 11.
    And I output "[MTF - TESTCASE 11 - begin]"
  
  # Install languages
    And I click on css "#nav-drawer a:contains('Site administration')"
    And I click on "Language packs" "link"
    And I click on css "option[value='de_ch']"
    And I click on css "input[value='Install selected language pack(s)']"

  # Check english version
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    When I press "Create a new question ..."
    And I click on "item_qtype_mtf" "radio"
    And I press "Add"
    Then element with css "input[id='id_responsetext_1'][value='True']" should exist
    And element with css "input[id='id_responsetext_2'][value='False']" should exist
    And I press "id_cancel"

  # Switch to german
    And I click on css "#header-right .userlang"
    And I click on css ".userlang-menu a:contains('Deutsch - Schweiz')"

  # Check german version
    When I press "Neue Frage erstellen..."
    And I click on "item_qtype_mtf" "radio"
    And I press "Hinzufügen"
    Then element with css "input[id='id_responsetext_1'][value='Wahr']" should exist
    And element with css "input[id='id_responsetext_2'][value='Falsch']" should exist
    And I press "id_cancel"
  
  And I output "[MTF - TESTCASE 11 - end]"
    
