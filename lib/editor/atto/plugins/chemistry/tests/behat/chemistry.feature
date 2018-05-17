@editor @editor_atto @atto @atto_chemistry @_bug_phantomjs
Feature: Atto chemistry editor
  To teach chemistry to students, I need to write chemical equations

  @javascript
  Scenario: Create a chemistry equation
    Given I log in as "admin"
    When I navigate to "Edit profile" node in "My profile settings"
    And I set the field "Description" to "<p>Chemistry test</p>"
    # Set field on the bottom of page, so chemistry editor dialogue is visible.
    And I expand all fieldsets
    And I set the field "Picture description" to "Test"
    And I select the text in the "Description" Atto editor
    And I click on "Show more buttons" "button"
    And I click on "Chemistry editor" "button"
    And I set the field "Edit chemistry using" H+ + OH-"
    And I click on "\bond{<=>}" "button"
    And I set the field "Edit chemistry using" to " H20"
    And I click on "Save chemistry" "button"
    And I click on "Update profile" "button"
    Then "equilibrium equation" "text" should exist

  @javascript
  Scenario: Edit a chemistry equation
    Given I log in as "admin"
    When I navigate to "Edit profile" node in "My profile settings"
    And I set the field "Description" to "<p>\(\ce{ H2SO4 }\)</p>"
    # Set field on the bottom of page, so chemistry editor dialogue is visible.
    And I expand all fieldsets
    And I set the field "Picture description" to "Test"
    And I select the text in the "Description" Atto editor
    And I click on "Show more buttons" "button"
    And I click on "Chemistry editor" "button"
    Then the field "Edit chemistry using" matches value " H2SO4 "
    And I click on "Save chemistry" "button"
    And the field "Description" matches value "<p>\(\ce{ H2SO4 \)</p>"
