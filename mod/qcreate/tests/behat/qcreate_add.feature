@mod @mod_qcreate
Feature: A teacher can create a Question Creation activity
  In order to test my student ability to create questions
  As a teacher
  I need to create a Question Creation activity

  Scenario: Create a qcreate activity
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name             | Question Creation 001            |
      | Introduction     | Question Creation introduction  |
    And I am on "Course 1" course homepage
    And I follow "Question Creation 001"
    Then I should see "Question Creation 001"
    And I should see "Question Creation introduction"
    And I should see "Activity is open. No time limits set."
    And I should see "Grading is 50%% automatic, 50%% manual."
    And I navigate to "Grading" in current page administration
    And I should see "Nothing to display"
