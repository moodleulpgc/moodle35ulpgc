@qtype @qtype_mtf @qtype_mtf_step_3_4
Feature: Step 3 and Step 4

  Background:
    Given the following "users" exist:
      | username | firstname | lastname       | email                |
      | teacher1 | T1        | Teacher1       | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category       |
      | Course 1 | c1        | 0              |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher1 | c1        | editingteacher |
    And the following "question categories" exist:
      | contextlevel         | reference      | name                 |
      | Course               | c1             | Default for c1       |
      | Course               | c1             | AnotherCat for c1    |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                 | template        |
      | Default for c1       | mtf            | MTF-Question-001     | question_one    |

    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"

  @javascript
  Scenario: TESTCASE 3.

    # Edit the question
    And I output "[MTF - TESTCASE 3 - begin]"
    When I click on "Edit" "link" in the "MTF-Question-001" "table_row"
    And I set the following fields to these values:
      | id_name | |
    And I press "id_submitbutton"
    Then I should see "You must supply a value here."
    When I set the following fields to these values:
      | id_name | Edited Multiple choice name |
    And I press "id_submitbutton"
    Then I should see "Edited Multiple choice name"

    # Duplicate the question
    When I click on "Duplicate" "link" in the "Edited Multiple choice name" "table_row"
    And I press "id_submitbutton"
    Then I should see "Edited Multiple choice name (copy)"

    # Delete the question
    When I click on "Delete" "link" in the "Edited Multiple choice name (copy)" "table_row"
    And I press "Delete"
    Then I should not see "Edited Multiple choice name (copy)"
    And I output "[MTF - TESTCASE 3 - end]"

  @javascript
  Scenario: RESTCASE 4.

    # Move the question to another category
    And I output "[MTF - TESTCASE 4 - begin]"
    And I click on css "tr:contains('MTF-Question-001') input[title='Select']"
    And I set the field "id_movetocategory" to "AnotherCat for c1"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat for c1"
    And I should see "MTF-Question-001"
    And I output "[MTF - TESTCASE 4 - end]"


    
