@qtype @qtype_kprime @qtype_kprime_step_7
Feature: Step 7

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
    And the following "question categories" exist:
      | contextlevel         | reference      | name             |
      | Course               | c1             | Default for c1   |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                | template         |
      | Default for c1       | kprime         | KPrime-Question-001 | question_one     |
    Given I log in as "teacher1"
    

  @javascript @_switch_window
  Scenario: TESTCASE 7.
  # When creating a KPrime question add "Image"
  # (and other html-editor possibilities) in the
  # stem and in the options.
  # Images and so on are displayed and work.

    And I output "[Kprime - TESTCASE 7 - begin]"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "KPrime-Question-001" "table_row"

  # Add image to question stem
    And I click on "Insert or edit image" "button" in the "#id_generalheader" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://127.0.0.1/question/type/kprime/tests/media/testimage1.png"
    And I press "Download"
    And I click on "testimage1.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage1AltDescription"
    And I click on "Save image" "button"
    And I press "Save changes and continue editing"

  # Add video to question stem
    And I click on "Insert or edit an audio/video file" "button" in the "#id_generalheader" "css_element"
    And I click on css ".nav-tabs a:contains('Video')"
    And I click on "Browse repositories..." "button" in the "#id_questiontext_video" "css_element"
    And I click on "Upload a file" "link" in the ".fp-repo-area" "css_element"
    And I attach file "/var/www/moodle/question/type/kprime/tests/media/testvideo1.mp4" to "input[name='repo_upload_file']"
    And I press "Upload this file"
    And I click on "Insert media" "button"
    And I press "Save changes and continue editing"

  # Add image to optiontext
    And I click on "Insert or edit image" "button" in the ".optiontext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://127.0.0.1/question/type/kprime/tests/media/testimage2.png"
    And I press "Download"
    And I click on "testimage2.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage2AltDescription"
    And I click on "Save image" "button"
    And I press "Save changes and continue editing"

  # Add image to feedback
    And I click on "Insert or edit image" "button" in the ".feedbacktext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://127.0.0.1/question/type/kprime/tests/media/testimage3.png"
    And I press "Download"
    And I click on "testimage3.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage3AltDescription"
    And I click on "Save image" "button"
    And I press "id_submitbutton"

  # Preview
    When I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then element with xpath "[alt='testimage1AltDescription']" should exist
    And I should not see "testimage1AltDescription"
    And element with xpath "[title='testvideo1.mp4']" should exist
    And element with xpath "[alt='testimage2AltDescription']" should exist
    And I should not see "testimage2AltDescription"
    
    When I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on css "tr:contains('option text 1') input[value=1]"
    And I click on css "tr:contains('option text 2') input[value=1]"
    And I click on css "tr:contains('option text 3') input[value=2]"
    And I click on css "tr:contains('option text 4') input[value=2]"
    And I press "Check"
    Then element with xpath "[alt='testimage3AltDescription']" should exist
    And I should not see "testimage3AltDescription"
    And I output "[Kprime - TESTCASE 7 - end]"



