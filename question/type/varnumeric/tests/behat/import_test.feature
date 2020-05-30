@ou @ou_vle @qtype @qtype_varnumeric
Feature: Import and export variable numeric questions
  As a teacher
  In order to reuse my variable numeric questions
  I need to be able to import and export them

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And I log in as "teacher"
    And I am on "Course 1" course homepage

  @javascript @_file_upload
  Scenario: Import and export variable numeric questions
    # Import sample file.
    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/varnumeric/tests/fixtures/testquestion.moodle.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. What is [[a]] + [[b]]?"
    And I press "Continue"
    And I should see "Imported variable numeric question"

    # Now export again.
    When I am on "Course 1" course homepage
    And I navigate to "Question bank > Export" in current page administration
    And I set the field "id_format_xml" to "1"
    And I set the field "category" to "Imported questions (1)"
    And I press "Export questions to file"
    Then following "click here" should download between "2000" and "3000" bytes
    # If the download step is the last in the scenario then we can sometimes run
    # into the situation where the download page causes a http redirect but behat
    # has already conducted its reset (generating an error). By putting a logout
    # step we avoid behat doing the reset until we are off that page.
    And I log out