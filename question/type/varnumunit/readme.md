# Variable numeric set with units question type [![Build Status](https://travis-ci.org/moodleou/moodle-qtype_varnumunit.svg?branch=master)](https://travis-ci.org/moodleou/moodle-qtype_varnumunit)

A numerical question type for Moodle where one question can have
several variants with different numerical values. These variants
are given as a pre-defined list. The student must also give their
response with the correct unit, which is graded using the
'Pattern match' text matching syntax.

If the [Superscript/subscript editor](https://moodle.org/plugins/editor_ousupsub) is installed
then it can be used to let students enter their answer in scientific notation and units
that include superscripts and subscripts. However, this is optional.


## Acknowledgements

The question type was created by Jamie Pratt (http://jamiep.org/) for
the Open University (http://www.open.ac.uk/).


## Installation and set-up

This plugin requires that both Variable numeric set and
Pattern-match question types are installed.

### Install from the plugins database

Install from the Moodle plugins database
* https://moodle.org/plugins/qtype_varnumericset
* https://moodle.org/plugins/qtype_pmatch
* https://moodle.org/plugins/qtype_varnumunit
* https://moodle.org/plugins/editor_ousupsub

### Install using git

Or you can install using git. Type this commands in the root of your Moodle install

    git clone https://github.com/moodleou/moodle-qtype_varnumericset.git question/type/varnumericset
    echo /question/type/varnumericset/ >> .git/info/exclude
    git clone https://github.com/moodleou/moodle-qtype_pmatch.git question/type/pmatch
    echo /question/type/pmatch/ >> .git/info/exclude
    git clone https://github.com/moodleou/moodle-qtype_varnumunit.git question/type/varnumunit
    echo /question/type/varnumunit/ >> .git/info/exclude
    git clone https://github.com/moodleou/moodle-editor_ousupsub.git lib/editor/ousupsub
    echo /lib/editor/ousupsub/ >> .git/info/exclude

Then run the moodle update process
Site administration > Notifications
