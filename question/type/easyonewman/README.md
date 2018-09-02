Moodle 2.3+ plugin: Drag and Drop Newman Projections Question type

Carl LeBlond


INSTALLATION:

This will NOT work with Moodle 2.0 or older, since it uses the new
question API implemented in Moodle 2.1.

This is a Moodle question type. It should come as a self-contained 
"easyonewman" folder which should be placed inside the "question/type" folder
which already exists on your Moodle web server.

Once you have done that, visit your Moodle admin page - the database 
tables should automatically be upgraded to include an extra table for
the Newman Projection question type.

USAGE:

1) Provide a Question Name and Question Text.
2) Choose a staggered or eclipsed template option.
3) Choose whether the conformation is important.
4) Choose whether the perspective or view is important.
5) Use the Newman editor to create your answer.
6) Press the "Insert from editor" button to insert the code into the answer box.
7) Save your question.

Note that absolute stereochemistry is maintained.  For example if you ask students
to draw butan-2-ol you must provide both R and S enantiomers as possible answers.
However you can simply ask the students to draw (R)-butan-2-ol and only provide one answer.
Its often best to limit the number of possible answers by specifying a perspective
(e.g. viewing up a specific bond).  If conformation important is false then any of
the possible conformations will be valid answers and you do not need to provide all of them.

Ask question such as;

    Draw (S)-butan-2-ol looking up its C2-C3 bond?
    Draw the most stable conformation of 2-methylbutane?


Examples are located in the example folder (moodle XML format).
