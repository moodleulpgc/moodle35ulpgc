@mod @mod_qcreate
Feature: Backup and Restore of Question creation activities
  In order to reuse my Question creation activities
  As a admin
  I need to be able to backup and restore them

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Question Creation" to section "1" and I fill the form with:
      | Name             | Question Creation 001           |
      | Introduction     | Question Creation introduction  |
    And I log out
    And I log in as "admin"

  @javascript
  Scenario: Backup and restore  in a new course
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    Then I should see "Course 2"
    And I should see "Question Creation 001"
