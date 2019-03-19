@qtype @qtype_kprime @qtype_kprime_step_18_19_20
Feature: Step 18 and 19 and 20

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
      | student2 | Student   | Tneduts  | student2@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student2 | c1     | student        |   
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name            | template       |
      | Default for c1   | kprime | MTF-Question-2 | question_two   |
      | Default for c1   | kprime | MTF-Question-3 | question_three |



  @javascript
  Scenario: TESTCASE 18.
  # In the first Run feedback will be enabled. Check if fb and results are displayed
  # In the second Run feedback will be disabled. Check if fb and results are hidden

  # See if the Review is shown if enabled
    And I output "[Kprime - TESTCASE 18 - begin]"
    Given I log in as "teacher1"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Edit settings" "link"
    And I set the following fields to these values:
      | id_attemptimmediately | 1 |
      | id_correctnessimmediately | 1 |
      | id_marksimmediately | 1 |
      | id_specificfeedbackimmediately | 1 |
      | id_generalfeedbackimmediately | 1 |
      | id_rightanswerimmediately | 1 |
      | id_overallfeedbackimmediately | 1 |
    And I press "Save and return to course"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    When I press "Attempt quiz now"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I should see "1.00/1.00"
    And I should see "100.00 out of 100.00"
    And I should see "feedback to option 1"
    And I should see "feedback to option 2"
    And I should see "feedback to option 3"
    And I should see "feedback to option 4"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # See if the Review is shown if disabled
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Edit settings" "link"
    And I click on "Review options" "link"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_correctnessimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_marksimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_specificfeedbackimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_generalfeedbackimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_rightanswerimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_overallfeedbackimmediately']"
    And I click on css "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_attemptimmediately']"
    And I press "Save and return to course"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Re-attempt quiz"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I should not see "1.00/1.00"
    And I should not see "100.00 out of 100.00"
    And I should not see "feedback to option 1"
    And I should not see "feedback to option 2"
    And I should not see "feedback to option 3"
    And I should not see "feedback to option 4"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I output "[Kprime - TESTCASE 18 - end]"

  @javascript
  Scenario: TESTCASE 19 - Part 1.
  # After the Test is submitted control that results (true/false) 
  # selection are correctly aligned to the corresponded option. 
  # Options and results must correspond.
  # Scenario: Shuffling disabled
    
  # Create a response as student
    And I output "[Kprime - TESTCASE 19 - Part 1 - begin]"
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  # Check Responses Page
    And I click on "Actions menu" "link"
    And I click on "Responses" "link"
    Then element with xpath "[id='mod-quiz-report-responses-report_r0']" should exist
    And I should see "student2@moodle.com"
    And I should see "100.00"


  # Check Review Attempt Page
    And I click on "Review attempt" "link"
    Then element with css "tr:contains('option text 1') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 2') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 3') input[value='2'][checked='checked']" should exist
    And element with css "tr:contains('option text 4') input[value='2'][checked='checked']" should exist
    And I should see "option text 1: True"
    And I should see "option text 2: True"
    And I should see "option text 3: False"
    And I should see "option text 4: False"
    And I output "[Kprime - TESTCASE 19 - Part 1 - end]"

  @javascript
  Scenario: TESTCASE 19 - Part 2.
  # After the Test is submitted control that results (true/false) 
  # selection are correctly aligned to the corresponded option. 
  # Options and results must correspond.
  # Scenario: Shuffling enabled
    
  # Create a response as student
    And I output "[Kprime - TESTCASE 19 - Part 2 - begin]"
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-3 | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Responses" "link"
    And I click on "Review attempt" "link"
    Then element with css "tr:contains('option text 1') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 2') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 3') input[value='2'][checked='checked']" should exist
    And element with css "tr:contains('option text 4') input[value='2'][checked='checked']" should exist
    And I should see "option text 1: True"
    And I should see "option text 2: True"
    And I should see "option text 3: False"
    And I should see "option text 4: False"
    And I output "[Kprime - TESTCASE 19 - Part 2 - end]"

  @javascript
  Scenario: TESTCASE 20.
  # View  results as a teacher.
  # Check "review attempt, "responses", "statistics"
    
  # Create a response as student
    And I output "[Kprime - TESTCASE 20 - begin]"
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  # Check Responses Page
    And I click on "Actions menu" "link"
    And I click on "Responses" "link"
    Then element with xpath "[id='mod-quiz-report-responses-report_r0']" should exist
    And I should see "student2@moodle.com"
    And I should see "100.00"

  # Check Review Attempt Page
    When I click on "Review attempt" "link"
    Then I should see "100.00 out of 100.00"
    Then I should see "Mark 1.00 out of 1.00"
    And element with css "tr:contains('option text 1') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 2') input[value='1'][checked='checked']" should exist
    And element with css "tr:contains('option text 3') input[value='2'][checked='checked']" should exist
    And element with css "tr:contains('option text 4') input[value='2'][checked='checked']" should exist
    And I should see "option text 1: True"
    And I should see "option text 2: True"
    And I should see "option text 3: False"
    And I should see "option text 4: False"

  # Check Responses Page - Delete Entry
    And I click on "Actions menu" "link"
    And I click on "Responses" "link"
    And I click on "Select all" "link"
    And I press "Delete selected attempts"
    And I click on "Yes" "button" in the "Confirmation" "dialogue"
    Then I should not see "student2@moodle.com"
    And I output "[Kprime - TESTCASE 20 - end]"












