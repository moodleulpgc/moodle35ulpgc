@mod @mod_qcreate
Feature: Test grading several questions in a qcreate activity
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
  Scenario: Teacher grade student questions
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name                    | Question Creation 001          |
      | Introduction            | Question Creation introduction |
      | To own questions        | preview and view / save as new |
      | Total Questions Graded  | 2                              |
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
    And I should see "Not graded yet"
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
    Then I should see "Changes saved"
    And I should see "Feedback from teacher."
    And I should see "80 / 100"
    And I should see "Graded"
    And I set the field "Grade for question 'Multi-choice-002' created by Student 1" to "50 / 100"
    And I set the field "Comment for question 'Multi-choice-002' created by Student 1" to "You can do better."
    And I press "Save all grades & feedback"
    And I should see "Changes saved"
    And "Student 1" row "Final grade" column of "student_questions" table should contain "82.50"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Question Creation 001"
    And I should see "You have been awarded a total grade of 82.5 / 100 for this activity."
    And I should see "You have been awarded an automatic grade of 50 / 50 for these questions, since you have done 2 of 2 required questions."
    And I should see "A teacher has awarded you a grade of 32.5 / 50 for the questions you have done."
    And I follow "Grades" in the user menu
    And I click on "Course 1" "link" in the "region-main" "region"
    And I should see "82.50"
