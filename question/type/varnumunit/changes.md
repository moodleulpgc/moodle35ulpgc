# Change log for the Variable numeric with units question type


## Changes in 1.7

* Support for Moodle mobile app for questions that don't use the superscripts/subscript editor.
* Better grading when the allowed error is very small.
* Update Behat tests to pass with Moodle 3.8.


## Changes in 1.6

* Fix Behat tests to work in Moodle 3.6.


## Changes in 1.5

* Fix version dependency error with pmatch.


## Changes in 1.4

* New option, requiring a space between the number and the unit for thr response to be correct. 
* Privacy API implementation.
* Update to use the newer editor_ousupsub, instead of editor_supsub.
* Setup Travis-CI automated testing integration.
* Fix a backup and restore bug.
* Fix the feedback given when the unit was missing from the student's response.
* Fix a bug when analysing responses for the quiz statistics report if there was a blank response.
* Minor usability improvement on the editing form.
* Fix some automated tests to pass with newer versions of Moodle.
* Fix some coding style.
* Due to privacy API support, this version now only works in Moodle 3.4+
  For older Moodles, you will need to use a previous version of this plugin.


## 1.3 and before

Changes were not documented here.
