@assignfeedback @assignfeedback_solutionsheet @_file_upload
Feature: In an assignment, teachers can not upload solution sheets without capability
  In order to provide solutions
  As a teacher
  I need to upload the solution sheet.

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
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name                  | Test assignment name  |
      | Description                      | Questions here        |
      | allowsubmissionsfromdate[year]   | 2009                  |
    And I follow "Test assignment name"
    And I navigate to "Edit settings" in current page administration
    And I follow "Expand all"
    And I set the field "assignfeedback_solutionsheet_enabled" to "1"
    And I upload "mod/assign/feedback/solutionsheet/tests/fixtures/solutionsheet.txt" file to "Upload solution sheets" filemanager
    And I press "Save and display"
    And I log out

  @javascript
  Scenario: A teacher can not show and hide the solution sheet if they do not have the capability.
    Given I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                                                | permission |
      | assignfeedback/solutionsheet:releasesolution              | Prevent    |
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name"
    Then I should see "Solution sheets"
    And I should see "solutionsheet.txt"
    And I should see "Students can not currently access the solutions"
    And I should not see "Click to show the solutions"
    And I log out
