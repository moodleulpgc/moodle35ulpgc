@mod @mod_qcreate
Feature: Test grading a question in a qcreate activity
  As a teacher
  In order to evaluate my students in a Question creation activity
  I need to grade questions created by my students

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
  Scenario: Teacher grade student question
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name                    | Question Creation 001           |
      | Introduction            | Question Creation introduction  |
      | To own questions        | preview and view / save as new  |
      | Total Questions Graded  | 1                               |
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
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Question Creation 001"
    And I navigate to "Grading" in current page administration
    And "Student 1" row "Status" column of "student_questions" table should contain "Needs grading"
    And I should see "Multi-choice-001"
    And I set the field "Grade for question 'Multi-choice-001' created by Student 1" to "80 / 100"
    And I set the field "Comment for question 'Multi-choice-001' created by Student 1" to "Feedback from teacher."
    And I press "Save all grades & feedback"
    Then I should see "Feedback from teacher."
    And I should see "80 / 100"
    And I should not see "Needs grading"
    And "Student 1" row "Status" column of "student_questions" table should contain "Graded"
