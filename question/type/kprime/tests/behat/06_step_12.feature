@qtype @qtype_kprime @qtype_kprime_step_12
Feature: Step 12

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
    And the following "question categories" exist:
      | contextlevel         | reference      | name             |
      | Course               | c1             | Default for c1   |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                | template            |
      | Default for c1       | kprime         | KPrime-Question-001 | question_one        |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"


  @javascript
  Scenario: TESTCASE 12
  # Change which options are true and which are false.
  # There should never be a state where neither true or
  # false are selected

    And I output "[Kprime - TESTCASE 12 - begin]"
    When I click on "Edit" "link" in the "KPrime-Question-001" "table_row"
    And I click on "id_weightbutton_1_1" "radio"
    And I press "id_updatebutton"
    And element with css "#id_weightbutton_1_1[checked]" should exist
    And element with css "#id_weightbutton_1_2:not([checked])" should exist
    When I click on "id_weightbutton_1_2" "radio"
    And I press "id_updatebutton"
    And element with css "#id_weightbutton_1_1:not([checked])" should exist
    And element with css "#id_weightbutton_1_2[checked]" should exist
    When I click on "id_weightbutton_1_1" "radio"
    And I click on "id_weightbutton_1_1" "radio"
    And I press "id_updatebutton"
    And element with css "#id_weightbutton_1_1[checked]" should exist
    And element with css "#id_weightbutton_1_2:not([checked])" should exist
    And I output "[Kprime - TESTCASE 12 - end]"
    
    
   
   

  
    
