@ou @ou_vle @qtype @qtype_varnumunit
Feature: Test all the basic functionality of varnumunit question type
  In order evaluate students calculating ability
  As an teacher
  I need to create and preview variable numeric set with units questions.

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
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: Create, edit then preview a variable numeric set with units question.
    # Create a new question.
    And I add a "Variable numeric set with units" question filling the form with:
      | Question name        | Variable numeric set with units question |
      | Question text        | What is [[a]] m + [[b]] m?               |
      | id_vartype_0         | Predefined variable                      |
      | Variable 1           | a                                        |
      | id_variant0_0        | 2                                        |
      | id_variant1_0        | 3                                        |
      | id_variant2_0        | 5                                        |
      | id_vartype_1         | Predefined variable                      |
      | Variable 2           | b                                        |
      | id_variant0_1        | 8                                        |
      | id_variant1_1        | 5                                        |
      | id_variant2_1        | 3                                        |
      | Variable 3           | c = a + b                                |
      | In student response  | No superscripts                          |
      | id_answer_0          | c                                        |
      | id_fraction_0        | 100%                                     |
      | id_feedback_0        | The numerical part is right.             |
      | id_answer_1          | *                                        |
      | id_feedback_1        | Sorry, no.                               |
      | Unit 1               | match(m)                                 |
      | id_unitsfraction_0   | 100%                                     |
      | id_spaceinunit_0     | Remove all spaces before grading         |
      | id_unitsfeedback_0   | That is the right unit.                  |
      | Unit 2               | match(cm)                                |
      | id_unitsfraction_1   | 100%                                     |
      | id_spaceinunit_1     | Remove all spaces before grading         |
      | id_unitsfeedback_1   | That is the right unit 2.                |
      | id_otherunitfeedback | That is the wrong unit.                  |
      | Hint 1               | Please try again.                        |
      | Hint 2               | You may use a calculator if necessary.   |
    Then I should see "Variable numeric set with units question"

    # Preview it.
    When I choose "Preview" action for "Variable numeric set with units question" in the question bank
    And I switch to "questionpreview" window
    And I set the following fields to these values:
      | How questions behave | Interactive with multiple tries |
      | Marked out of        | 3                               |
      | Question variant     | 1                               |
      | Marks                | Show mark and max               |
    And I press "Start again with these options"
    Then I should see "What is 2 m + 8 m?"
    And the state of "What is 2 m + 8 m?" question is shown as "Tries remaining: 3"
    When I set the field "Answer:" to "10"
    And I press "Check"
    Then I should see "The numerical part is right."
    Then I should see "That is the wrong unit."
    And I should see "Please try again."
    When I press "Try again"
    Then the state of "What is 2 m + 8 m?" question is shown as "Tries remaining: 2"
    When I set the field "Answer:" to "10 m"
    And I press "Check"
    Then I should see "The numerical part is right."
    Then I should see "That is the right unit."
    And the state of "What is 2 m + 8 m?" question is shown as "Correct"
    And I should see "Mark 2.90 out of 3.00"
    And I switch to the main window

    # Spacing feedback
    When I choose "Edit question" action for "Variable numeric set with units question" in the question bank
    And I expand all fieldsets
    And I select "Remove all spaces before grading" from the "Spaces in units" singleselect
    # Wating for #257559 Mform disableif does not work on editor element [MDL-29701]. Once this merged, this should be uncommented.
    # And the "contenteditable" attribute of "div#id_spacesfeedback_0editable" "css_element" should contain "false"
    And I select "Preserve spaces, but don't require them" from the "Spaces in units" singleselect
    # Wating for #257559 Mform disableif does not work on editor element [MDL-29701]. Once this merged, this should be uncommented.
    # And the "contenteditable" attribute of "div#id_spacesfeedback_0editable" "css_element" should contain "false"
    And I select "Preserve spaces, and require a space between the number and the unit" from the "Spaces in units" singleselect
    Then the "#id_spacesfeedback_0" "css_element" should be enabled
    And the field "id_spacesfeedback_0" matches value "You are required to put a space between the number and the unit."
    And I press "id_submitbutton"
    When I choose "Preview" action for "Variable numeric set with units question" in the question bank
    And I switch to "questionpreview" window
    And I press "Start again with these options"
    And I set the field "Answer:" to "10m"
    And I press "Check"
    Then I should see "You are required to put a space between the number and the unit."
    And I switch to the main window

    # Backup the course and restore it.
    When I log out
    And I log in as "admin"
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    Then I should see "Course 2"
    When I navigate to "Question bank" in current page administration
    Then I should see "Variable numeric set with units question"

    # Edit the copy and verify the form field contents.
    When I choose "Edit question" action for "Variable numeric set with units question" in the question bank
    Then the following fields match these values:
      | Question name        | Variable numeric set with units question |
      | Question text        | What is [[a]] m + [[b]] m?               |
      | id_vartype_0         | Predefined variable                      |
      | Variable 1           | a                                        |
      | id_variant0_0        | 2                                        |
      | id_variant1_0        | 3                                        |
      | id_variant2_0        | 5                                        |
      | id_vartype_1         | Predefined variable                      |
      | Variable 2           | b                                        |
      | id_variant0_1        | 8                                        |
      | id_variant1_1        | 5                                        |
      | id_variant2_1        | 3                                        |
      | Variable 3           | c = a + b                                |
      | In student response  | No superscripts                          |
      | id_answer_0          | c                                        |
      | id_fraction_0        | 100%                                     |
      | id_feedback_0        | The numerical part is right.             |
      | id_answer_1          | *                                        |
      | id_feedback_1        | Sorry, no.                               |
      | Unit 1               | match(m)                                 |
      | id_unitsfraction_0   | 100%                                     |
      | id_unitsfeedback_0   | That is the right unit.                  |
      | id_spaceinunit_0     | Preserve spaces, and require a space between the number and the unit |
      | id_spacesfeedback_0  | You are required to put a space between the number and the unit.     |
      | Unit 2               | match(cm)                                |
      | id_unitsfraction_1   | 100%                                     |
      | id_spaceinunit_1     | Remove all spaces before grading         |
      | id_unitsfeedback_1   | That is the right unit 2.                |
      | id_otherunitfeedback | That is the wrong unit.                  |
      | Hint 1               | Please try again.                        |
      | Hint 2               | You may use a calculator if necessary.   |
    And I set the following fields to these values:
      | Question name | Edited question name |
    And I press "id_submitbutton"
    Then I should see "Edited question name"
