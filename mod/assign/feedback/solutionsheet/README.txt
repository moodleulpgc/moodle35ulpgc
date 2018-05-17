Solution sheet plugin for the Moodle assignment module

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details:

http://www.gnu.org/copyleft/gpl.html

=== Description ===

The "Solution sheet" plugin adds a feature to Moodle's assignment module
that allows the teacher to upload solutions to the assignment in the form
of files. The teacher can decide whether to hide or show the solutions
to students, including the option to make the solutions visible at a defined
time after the assignment due date. In addition, the solution can be hidden
after a configurable cutoff date.

For further information, please see:
    http://docs.moodle.org/33/en/Solution_sheet

=== Installation instructions ===

Place the code of the module into the mod/assign/feedback/solutionsheet
directory of your Moodle directory root. That is, the present file should
be located at: mod/assign/feedback/solutionsheet/README.txt

For further installation instructions please see:
    http://docs.moodle.org/en/Installing_plugins

This plugin is intended for Moodle 3.3 and above.

=== Authors ===

Current maintainer:
 Henning Bostelmann, University of York <henning.bostelmann@york.ac.uk>

=== Release notes ===

--- Version 3.5 ---

Added features:

* Stricter checks for releasing solution sheets: confirmation dialogue, 
  specific capability; see CONTRIB-7136
* Optionally, show solution sheet only to students who have made a submission;
  see CONTRIB-7029

--- Version 3.3 ---

Minor fixes and updated behat tests.

--- Version 3.2 ---

First public release of the plugin.
