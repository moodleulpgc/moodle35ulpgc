# qtype_mtf Multiple True/False (MTF with multi-answers) ETHz (Seperated from qtype_scmc) question type for moodle 2.6+ to 3.x

*** Info regarding migration from qtype_multichoice (with Multi Answers Only) to qtype_mtf ***

You should specify either:
- 'courseid' or
- 'categoryid' or
- set the parameter 'all' to 1.

Note: No migration will be done without restrictions!

Examples:
	
- Specific Course: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?courseid=55
- Specific Question Category: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?categoryid=1
- All Multi question: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1
- DRY RUN: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=1

Script should be run in SSH / Shell Command Line unless the number of questions is less than 1K to avoid interruption by browser.
