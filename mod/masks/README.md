MASK : Maskable PDF Activity Module
-----------------------------------

Purpose
-------
MASK provides a simple solution for creating an interactive activity from a pdf document.

The teacher can upload their pdf file to Moodle, lay down masks that obscure parts of the underlying document and add questions for the students to answer in order to make the masks disappear.

The student is encouraged to try to answer questions correctly on their first attempt and receives a corresponding grade in the Moodle grade book when they have found the correct answers to all of the questions in the document.
The teacher can add hint texts to be displayed after a student answers questions incorrectly in order to help them to identify the correct answer.

The teacher can additionally hide pages such as title pages and blank pages in the original pdf that are not of interest for their use within Moodle.

NOTE: For anyone considering using this module in assessments it is important to know that a student who uses the web developper functionality built into all modern browsers is able to view the document pages without the overlaid masks. As a result, in an assessment context the answers to questions should not be present in the pdf document.


Installation
------------
This plugin relies on the presence on the server of the free to use application 'pdf2svg' which is available as a standard package under Debian and Ubuntu linux distributions and also exists for Microsft Windows and Mac.

Other than this requirement, MASK is a very standard Moodle plugin and can be installed and uninstalled via standard Moodle processes.

The only configuration option that needs to be set at install time is the command line to execute to run the pdf2svg application.


Getting Started
---------------
Once installed the plugin allows a teacher editing a course to add MASK activities.

Each such activity is based on a single pdf file, so the teacher's first job is to select the pdf file that they would like to upload.

Once the document is uploaded they can browse through its pages using the standard page navigation menu dispalyed at the top right of the screen, add masks and popup notes to the page by clicking the ADD button or perform maintenance operations (such as hiding and unhiding pages) by clicking on the cog icon.

Masks can be selected, moved and resized by clicking and dragging with the mouse. They can be edited, hidden, deleted and recoloured via the context menu that appears whenever a mask is selected.

Changes to the layout are not automatically saved. A SAVE buttonn appears next to the ADD button whenever there are unsaved changes.


Copyright & License
-------------------
This plugin was developed as an extension to Moodle by Edunao SA on behalf of ENIT.
It is an open source plugin that can be freely used and modified.

copyright  2016 Edunao SAS (contact@edunao.com)
license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


Design Philosophy and future extension
--------------------------------------
This module is intended to be extremely simple to use.

It would not be desirable to add options that can be confusing or cause it to bloat.

It is desirable to improve the ergonomics, user feedback, clarity of presentation and any other such features that make the plugin easier or more pleasant to use.

The module has been designed to allow easy addition of new question types. It is generally prefereable to create a new question type instead of complexifying an existing question type to try to make it behave in several different ways.

The database models have been designed to allow future development of different views for the teacher that  could allow them to better understand how their material is being used. It includes information such as the number of failed attempts that each student has at answering each of the questions, the times at which each question were first viewed and finally answered and so on.

Development of simple summary views for the teacher to use could be extremely worthwhile.

