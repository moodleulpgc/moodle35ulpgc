@qtype @qtype_kprime @qtype_kprime_step_8_9_10
Feature: Step 8 and Step 9 and Step 10

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
      | questioncategory     | qtype          | name                | template       |
      | Default for c1       | kprime         | KPrime-Question-003 | question_three |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"


  @javascript @_switch_window
  Scenario: TESTCASE 8
  # Change scoring Method to KPrime1/0 and test evaluation.
  # If everything correct -> Max. Points
  # If one or more incorrect -> 0 Points

    And I output "[Kprime - TESTCASE 8 - begin]"
    When I click on "Edit" "link" in the "KPrime-Question-003" "table_row"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprimeonezero" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"
    And I output "[Kprime - TESTCASE 8 - end]"
   
  @javascript @_switch_window
  Scenario: TESTCASE 9 - Part 1
  # Change scoring Method to Subpoints and test evaluation.
  # For each correct answer you should get subpoints.
  # You should also get subpoints if you answer some correctly
  # but dont't fill out all options

    And I output "[Kprime - TESTCASE 9 - Part 1 - begin]"
    When I click on "Edit" "link" in the "KPrime-Question-003" "table_row"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "Mark 0.50 out of 1.00"
    And I output "[Kprime - TESTCASE 9 - Part 1 - end]"

  @javascript @_switch_window
  Scenario: TESTCASE 9 - Part 2
  # Change scoring Method to KPrime and test evaluation.

    And I output "[Kprime - TESTCASE 9 - Part 2- begin]"
    When I click on "Edit" "link" in the "KPrime-Question-003" "table_row"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprime" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "Mark 0.50 out of 1.00"
    And I press "Start again"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"
    And I output "[Kprime - TESTCASE 9 - Part 2- end]"

  @javascript @_switch_window
  Scenario: TESTCASE 10
  # Edit true and false fields.
  # They sould be editable and the values
  # get actualised in the answer options

    And I output "[Kprime - TESTCASE 10 - begin]"
    When I click on "Edit" "link" in the "KPrime-Question-003" "table_row"
    And I set the field "id_responsetext_1" to "Red Answer"
    And I set the field "id_responsetext_2" to "Blue Answer"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Red Answer"
    And I should see "Blue Answer"
    And I output "[Kprime - TESTCASE 10 - end]"
  
    
