Moodle 2.3+ plugin: Drag and Drop Fischer Projection Question type

Student must construct Fischer projections by dragging groups onto a
template which you predefine.

Carl LeBlond


INSTALLATION:

This will NOT work with Moodle 2.0 or older, since it uses the new
question API implemented in Moodle 2.1.

This is a Moodle question type. It should come as a self-contained 
"easyofischer" folder which should be placed inside the "question/type" folder
which already exists on your Moodle web server.

Once you have done that, visit your Moodle admin page - the database 
tables should automatically be upgraded to include an extra table for
the Fiacher Projection question type.

USAGE:


    1) Provide a Question Name and Question Text.
    2) Choose whether the correct answer can be rotated 180 degrees.
    3) Choose the number of stereocenters.
    4) Use the Fischer projection editor to create your answer.
    5) Press the "Insert from editor" button to insert the code into the answer box.

Ask question such as;

    Draw L-alanine?
    Convert the following structure into a Fischer projection?
