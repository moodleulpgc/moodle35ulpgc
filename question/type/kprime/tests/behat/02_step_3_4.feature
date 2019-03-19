@qtype @qtype_kprime @qtype_kprime_step_3_4
Feature: Edit a question

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
      | Course       | C1        | AnotherCat     |
    And the following "questions" exist:
      | questioncategory | qtype  | name             | template     |
      | Test questions   | kprime | KPrim-Question-1 | question_one |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"

  @javascript
  Scenario: Edit, Duplicate, Move and delete a MTF question

  # Edit
    And I output "[Kprime - TESTCASE: 3 - begin]"
    When I click on "Edit" "link" in the "KPrim-Question-1" "table_row"
    And I set the following fields to these values:
      | id_name | |
    And I press "id_submitbutton"
    Then I should see "You must supply a value here."
    When I set the following fields to these values:
      | id_name | Edited KPrim-Question-1 |
    And I press "id_submitbutton"
    Then I should see "Edited KPrim-Question-1"

  # Duplicate the question
    When I click on "Duplicate" "link" in the "Edited KPrim-Question-1" "table_row"
    And I press "id_submitbutton"
    Then I should see "Edited KPrim-Question-1 (copy)"

  # Move the question to another category
    And I output "[Kprime - TESTCASE: 4 - begin]"
    When I click on css "tr:contains('Edited KPrim-Question-1 (copy)') input[title='Select']"
    And I set the field "id_movetocategory" to "AnotherCat"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat"
    And I should see "Edited KPrim-Question-1 (copy)"

  # Delete the question
    When I click on "Delete" "link" in the "Edited KPrim-Question-1 (copy)" "table_row"
    And I press "Delete"
    Then I should not see "Edited KPrim-Question-1 (copy)"
    And I output "[Kprime - TESTCASE: 3 - end]"
    And I output "[Kprime - TESTCASE: 4 - end]"
