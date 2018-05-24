@mod @mod_qcreate
Feature: Export good questions of a qcreate activity
  As a teacher
  In order to re-use questions created by my students in a Question creation activity
  I need to export them

  @javascript @_file_upload
  Scenario: Teacher export good questions
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name                    | Question Creation 001           |
      | Introduction            | Question Creation introduction  |
      | To own questions        | preview and view / save as new  |
      | Total Questions Graded  | 2                               |
    And I log out
    And I run the scheduled task "mod_qcreate\task\synchronize_qaccess"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I add a "Multiple choice" question to the "Question Creation 001" qcreate with:
      | Question name            | Multi-choice-001                   |
      | Question text            | Find the capital of France.        |
      | General feedback         | Paris is the capital of France     |
      | Choice 1                 | Tokyo                              |
      | Choice 2                 | London                             |
      | Choice 3                 | Paris                              |
      | id_fraction_0            | None                               |
      | id_fraction_1            | None                               |
      | id_fraction_2            | 100%                               |
    And I add a "Multiple choice" question to the "Question Creation 001" qcreate with:
      | Question name            | Multi-choice-002                   |
      | Question text            | What\'s between orange and green in the spectrum?  |
      | General feedback         | The odd numbers are One and Three                  |
      | Choice 1                 | Red                                                |
      | Choice 2                 | Yellow                                             |
      | Choice 3                 | Blue                                               |
      | id_fraction_0            | None                                               |
      | id_fraction_1            | 100%                                               |
      | id_fraction_2            | None                                               |
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I add a "Multiple choice" question to the "Question Creation 001" qcreate with:
      | Question name            | Multi-choice-003                   |
      | Question text            | Find the capital of England        |
      | General feedback         | London is the capital of England   |
      | Choice 1                 | Tokyo                              |
      | Choice 2                 | London                             |
      | Choice 3                 | Paris                              |
      | id_fraction_0            | None                               |
      | id_fraction_1            | 100%                               |
      | id_fraction_2            | None                               |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Question Creation 001"
    And I navigate to "Grading" in current page administration
    And I set the field "Grade for question 'Multi-choice-001' created by Student 1" to "80 / 100"
    And I set the field "Grade for question 'Multi-choice-002' created by Student 1" to "50 / 100"
    And I set the field "Grade for question 'Multi-choice-003' created by Student 2" to "90 / 100"
    And I press "Save all grades & feedback"
    And I navigate to "Export good questions" in current page administration
    And I set the field "id_betterthangrade" to "70 / 100"
    And I set the field "id_format_xml" to "1"
    And I press "Export questions to file"
    Then following "click here" should download between "2900" and "3000" bytes
    And I log out
