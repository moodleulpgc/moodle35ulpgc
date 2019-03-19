@qtype @qtype_kprime @qtype_mtf_add @qtype_kprime_step_24_25
Feature: Step 24 and 25

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
      | student1 | S1        | Student1 | student1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student1 | c1     | student        |

 @javascript
  Scenario: TESTCASE 24.
  # Testcase 24:
  # When adding hint options, hints should be saved.
  # Hints should also be duplicated if the question is duplicated

  # Create a question with hints
    And I output "[Kprime - TESTCASE 24 - begin]"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    And I press "Create a new question ..."
    And I click on "item_qtype_kprime" "radio"
    And I press "Add"
    And I set the following fields to these values:
      | id_name              | KPrime-Question-001      |
      | id_defaultmark       | 1                        |
      | id_questiontext      | question_one		|
      | id_generalfeedback   | This feedback is general |
      | id_option_1          | q1                       |
      | id_option_2          | q2                       |
      | id_option_3          | q3                       |
      | id_option_4          | q4                       |
      | id_feedback_1        | f1                       |
      | id_feedback_2        | f2                       |
      | id_feedback_3        | f3                       |
      | id_feedback_4        | f4                       |
      | id_weightbutton_1_1  | checked                  |
      | id_weightbutton_2_1  | checked                  |
      | id_weightbutton_3_2  | checked                  |
      | id_weightbutton_4_2  | checked                  |
      | id_hint_0            | Hint 1 should be saved   |
      | id_hint_1            | Hint 2 should be saved   |
    And I press "id_submitbutton"

  # Check if hints are saved
    When I click on "Edit" "link" in the "KPrime-Question-001" "table_row"
    And I click on "Multiple tries" "link"
    Then I should see "Hint 1 should be saved"
    And I should see "Hint 2 should be saved"
    And I press "id_cancel"

  # Duplicate question and see if hints are copied as well
    When I click on "Duplicate" "link" in the "KPrime-Question-001" "table_row"
    And I press "id_submitbutton"
    Then I should see "KPrime-Question-001 (copy)" 

  # Check if hints are saved
    When I click on "Edit" "link" in the "KPrime-Question-001 (copy)" "table_row"
    And I click on "Multiple tries" "link"
    Then I should see "Hint 1 should be saved"
    And I should see "Hint 2 should be saved"
    And I press "id_cancel"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I output "[Kprime - TESTCASE 24 - end]"


 @javascript
  Scenario: TESTCASE 25.
  # Testcase 25:
  # Hints are displayed as output and the penalty for displaying
  # those hints will be considered when computing the final score

  # Actvate Hints as teacher1
    And I output "[Kprime - TESTCASE 25 - begin]"
    Given the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel         | reference      | name                 |
      | Course               | c1             | Default for c1       |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                 | template        |
      | Default for c1       | kprime         | KPrime-Question-004  | question_four   |
    And quiz "Quiz 1" contains the following questions:
      | question            | page |
      | KPrime-Question-004 | 1    |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Edit settings" "link"
    And I click on "Question behaviour" "link"
    And I set the field "How questions behave" to "Interactive with multiple tries"
    And I press "Save and display"
    And I click on css ".usermenu"
    And I click on "Log out" "link"   

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"

  # Correct answer, but 2 tries (1.00 - 0.33 - 0.33 = 0.33) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Try again"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 0.33 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"
 
  # Correct answer, but 1 try (1.00 - 0.33 = 0.66) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 0.67 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Correct answers, and no further tries (1.00) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Partially correct answer, and no further tries (0.50) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.50 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Partially correct answer, but 1 try (0.5 - 0.33 = 0.17) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.17 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Partially correct answer, but 2 tries (0.5 - 0.33 - 0.33 = 0.00) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 2 tries (0.0 - 0.33 - 0.33 = 0.00) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=2]"
    And I click on css "tr:contains('option text 2') input[value=2]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 1 try (0.0 - 0.33 = 0.00) (Subpoints)
    And I click on css "tr:contains('option text 1') input[value=2]"
    And I click on css "tr:contains('option text 2') input[value=2]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Change Grading to MTF 1/0
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "KPrime-Question-004" "table_row"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprimeonezero" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link" 

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Re-attempt quiz"

  # Correct answers, and no further tries (1.00) (KPrime1/0)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Partially correct answers, but 1 try (0.00 - 0.33 = 0.00) (KPrime/0)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Change Grading to KPrime
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "KPrime-Question-004" "table_row"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprime" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"  

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Re-attempt quiz"

  # Correct answer, and no further tries (1.00) (KPrime)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Correct answers, but 1 try (1.00 - 0.33 = 0.66) (KPrime)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    And I press "Try again"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 0.67 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # One wrong and no further tries (0.5) (KPrime)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.50 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # One wrong and 1 further tries (0.5 - 0.33 = 0.17) (KPrime)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.17 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Two wrong and no further tries (0.00) (KPrime)
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=1]"
    And I click on css "tr:contains('option text 4') input[value=1]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I output "[Kprime - TESTCASE 25 - end]"








