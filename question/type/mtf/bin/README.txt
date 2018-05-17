Das Skript mig_multichoice_to_mtf.php migriert alte ETHZ multichoice Fragen (mit Multi-Answers) in
den neuen Fragentyp qtype_mtf. Es werden keine Fragen überschrieben
oder gelöscht, sondern immer nur neue Fragen erstellt. Es werden nur
multichoice Fragen migriert, die höchstens vier Optionen und höchstens 2
Anworten haben.

Nur Website-Administratoren dürfen das Skript ausführen. 

Das Skript akzeptiert folgende Parameter in der URL:

 - courseid : Die Moodle ID des Kurses, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - categoryid: Die Moodle ID der Fragen-Kategory, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - dryrun: Wenn 1, dann werden keine neuen Fragen erstellt. Es wird nur
   Information über die zu migrierenden Fragen ausgegeben. Default 0.

 - all: Wenn 1, dann werden alle Fragen der Plattform migriert, ohne
   Einschränkungen.  Default 0.

Ein Aufruf geschieht dann in einem Browser z.B. wiefolgt:
   <URL zum Moodle>/question/type/mtf/bin/mig_multichoice_to_mtf.php?courseid=12345&dryrun=1
oder 
   <URL zum Moodle>/question/type/mtf/bin/mig_multichoice_to_mtf.php?categoryid=56789&dryrun=1
   
** ENGLISH **
********** Multichoice (with Multi Answers Only) migration to qtype_mtf (Multichoice ETHz). **********

You should specify either:
- 'courseid' or
- 'categoryid' or
- set the parameter 'all' to 1.

Note: No migration will be done without restrictions!

Examples:
	
Specific Course: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?courseid=55
Specific Question Category: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?categoryid=1
All Multi question: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1
DRY RUN: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=1
