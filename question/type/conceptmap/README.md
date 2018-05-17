# Concept Map question type for Moodle
Question type for Moodle which uses a Concept Map editor for students to draw CMs as answers
------------------------------------------

Version: 1.4.0
------------------------------------------

Author: Jorge Villalon (villalon@gmail.com)

Release notes
-------------

1.4.0: Fixed bug showing concepts and relationships in random places when locale for integers included decimals. Tested on Moodle 2.5.
1.3.2: Added hack to fix Moodle's $USER->lang representation (all in lowercase) to support country specific languages (es_CL, us_GB, pt_BR, etc.).
1.3.1: Added support for brazilian portuguese (pt_BR).
1.3: Changed language files and form for compliance in Moodle 2.4.

NOTE
----

This question type release has been developed and tested for Moodle 2.7 and 2.8 in MySql. It is based
on the essay question type by The Open Univeristy.

Introduction
------------

Concept Maps (CMs) are widely used in education as a way to visualize
a person's knowledge (typically a student). CMs are usually drawn by
students on paper, or using a software tool like CmapTools or MindMap.

However, there was a need for a web based Concept Map editor, that allows
students to create their maps with no software installations required.

As part of my doctoral work on technology applied to education, I wrote
the code for a web based Concept Map editor called 'CmapWeb', that
implements a very simple interface to work with CMs.

I have integrated CmapWeb with Moodle 2.1 so it is easier for teachers 
to create Concept Mapping activities for their students. It was integrated
as a question type, so you can just add a quiz, and create a Concept Map
question and add it to the quiz. That will give you all the useful
features in Moodle quizzes (start and end dates, time frames, grading, etc.)

Installation
------------

I guess you already unzipped the conceptmap.zip file so you are reading
this document. If that is the case, simply copy and paste the folder in
your moodle question type folder (e.g: moodle/question/type/). You will
recognize the folder because it contains several other folders for question
types like 'essay' and 'multichoice'.

Once you copied the file, simply login as administrator in Moodle and
browse to Site administration->Notifications

Acnkowledgments, suggestions, complaints and bug reporting
----------------------------------------------------------

I'll be happy to get any useful feedback from you. Please feel free to
email me, my name and email address are in the top of this document.