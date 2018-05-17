@mod @mod_qcreate
Feature: Test creating a question in a qcreate activity
  As a student
  In order to get a grade in a Question creation activity
  I need to be able to create a question

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

  @javascript @_switch_window
  Scenario: Student create a Multiple choice question
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name                    | Question Creation 001           |
      | Introduction            | Question Creation introduction  |
      | To own questions        | preview and view / save as new  |
      | Total Questions Graded  | 2                               |
    And I log out
    # We need to run the task to update students capabilities on questions
    And I run the scheduled task "mod_qcreate\task\synchronize_qaccess"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I add a "Multiple choice" question to the "Question Creation 001" qcreate with:
      | Question name            | Multi-choice-001                   |
      | Question text            | Find the capital cities in Europe. |
      | General feedback         | Paris and London                   |
      | One or multiple answers? | Multiple answers allowed           |
      | Choice 1                 | Tokyo                              |
      | Choice 2                 | Spain                              |
      | Choice 3                 | London                             |
      | Choice 4                 | Barcelona                          |
      | Choice 5                 | Paris                              |
      | id_fraction_0            | None                               |
      | id_fraction_1            | None                               |
      | id_fraction_2            | 50%                                |
      | id_fraction_3            | None                               |
      | id_fraction_4            | 50%                                |
    Then I should see "You've done one extra question."
    And I should see "2 questions of any of the types below will be graded"
    And I should see "Multi-choice-001"
    And I should see "Not graded yet"
    And I should see "You have been awarded a total grade of 25 / 100 for this activity"
    And I should see "You have been awarded an automatic grade of 25 / 50 for these questions, since you have done 1 of 2 required questions."
    And I should see "A teacher has awarded you a grade of 0 / 50 for the questions you have done."
    And I add a "Multiple choice" question to the "Question Creation 001" qcreate with:
      | Question name            | Multi-choice-002                   |
      | Question text            | Which are the odd numbers?         |
      | General feedback         | The odd numbers are One and Three  |
      | One or multiple answers? | Multiple answers allowed           |
      | Choice 1                 | One                                |
      | Choice 2                 | Two                                |
      | Choice 3                 | Three                              |
      | Choice 4                 | Four                               |
      | id_fraction_0            | 50%                                |
      | id_fraction_1            | None                               |
      | id_fraction_2            | 50%                                |
      | id_fraction_3            | None                               |
    And I should see "You've done 2 extra questions."
    And I should see "You've done 2 questions of this type."
    And I should see "Multi-choice-002"
    And I should see "Not graded yet"
    And I should see "You have been awarded a total grade of 50 / 100 for this activity"
    And I should see "You have been awarded an automatic grade of 50 / 50 for these questions, since you have done 2 of 2 required questions."
    And I should see "A teacher has awarded you a grade of 0 / 50 for the questions you have done."
    And I click on "Preview" "link" in the "Multi-choice-002" "list_item"
    And I switch to "questionpreview" window
    And I should see "Marked out of 1.00"
    And I should see "Technical information"
    And I should see "Attempt options"
    And I should see "Display options"
