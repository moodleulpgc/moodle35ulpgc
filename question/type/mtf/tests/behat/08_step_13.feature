@qtype @qtype_mtf @qtype_mtf_step_13
Feature: Step 13

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"

 @javascript
  Scenario: TESTCASE 13
  # Create a MTF Question with all options set true. 
  # Change all options to false. Save. Reenter. 
  # All options should now be set to false.

    And I output "[MTF - TESTCASE 13 - begin]"
    When I press "Create a new question ..."
    And I click on "item_qtype_mtf" "radio"
    And I press "Add"
  # Set all to true
    And I set the following fields to these values:
      | id_name              | MTF-Question-002         |
      | id_defaultmark       | 1                        |
      | id_questiontext      | The Questiontext         |
      | id_generalfeedback   | This feedback is general |
      | id_option_0          | questiontext 1           |
      | id_option_1          | questiontext 2           |
      | id_option_2          | questiontext 3           |
      | id_option_3          | questiontext 4           |
      | id_feedback_0        | feedbacktext 1           |
      | id_feedback_1        | feedbacktext 2           |
      | id_feedback_2        | feedbacktext 3           |
      | id_feedback_3        | feedbacktext 4           |
      | id_weightbutton_0_1  | checked                  |
      | id_weightbutton_1_1  | checked                  |
      | id_weightbutton_2_1  | checked                  |
      | id_weightbutton_3_1  | checked                  |
    And I press "id_submitbutton"
    Then I should see "MTF-Question-002"

    When I click on "Edit" "link" in the "MTF-Question-002" "table_row"
  # Check
    And element with css "#id_weightbutton_0_1[checked]" should exist
    And element with css "#id_weightbutton_1_1[checked]" should exist
    And element with css "#id_weightbutton_2_1[checked]" should exist
    And element with css "#id_weightbutton_3_1[checked]" should exist
  # Seta all to false
    And I set the following fields to these values:
      | id_weightbutton_0_2 | checked |
      | id_weightbutton_1_2 | checked |
      | id_weightbutton_2_2 | checked |
      | id_weightbutton_3_2 | checked |
    And I press "id_updatebutton"
  # Check
    And element with css "#id_weightbutton_0_2[checked]" should exist
    And element with css "#id_weightbutton_1_2[checked]" should exist
    And element with css "#id_weightbutton_2_2[checked]" should exist
    And element with css "#id_weightbutton_3_2[checked]" should exist
    And I output "[MTF - TESTCASE 13 - end]"
    
