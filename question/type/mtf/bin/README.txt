#qtype_multichoice Migration > qtype_mtf#
##GERMAN##

######Beschreibung:######
Das Skript mig_multichoice_to_mtf.php migriert Fragen des Typs 
qtype_multichoice in den Fragentyp qtype_mtf. Es werden keine 
Fragen überschrieben oder gelöscht, sondern immer nur neue Fragen 
erstellt.

Nur Website-Administratoren können das Skript ausführen. 

######Erforderliche Parameter (einer auswählbar):######
 - courseid (Akzeptierte Werte: eine gültige Kurs-ID)
 - categoryid (Akzeptierte Werte: eine gültige Category-ID)
 - all (Akzeptierte Werte: 1)

######Conditional Parameters (0-n auswählbar):######
 - dryrun (Akzeptierte Werte: 0,1)
 - migratesingleanswer (Akzeptierte Werte: 0,1)
 - migratechildren (Akzeptierte Werte: 0,1)

  Dryrun ist standardmässig aktiviert (1). Falls Dryrun aktiviert
  ist, werden keine Änderungen an der Datenbank vorgenommen. Lassen
  Sie Dryun aktiviert um mögliche Fehler und Probleme vor der 
  eigentlichen Migration ausfindig zu machen.

  Die migratesingleanswer Option ist standardmässig deaktiviert (0).
  Fragen mit nur einer richtigen Lösung können als Multichoice Frage 
  abgebildet werden, jedoch wäre auch eine Migration zum Singlechoice 
  Fragentyp sinnvoll. Wenn migratesingleanswer aktiviert ist (1),
  werden diese Fragen zu Multichoice Fragen migriert.

  Die migratechildren Option ist standardmässig deaktiviert (0).
  Wenn migratechildren aktiviert ist, so werden auch Multichoice Fragen 
  migriert, welche ein Parent-Attribut haben. Diese Fragen werden 
  migriert ohne dabei die Parent-Child Struktur beizubehalten.

##ENGLISH##

######Description:######
The Script mig_multichoice_to_mtf.php migrates questions of the type 
qtype_multichoice to the questiontype qtype_mtf. No questions will 
be overwritten or deleted, the script will solely create new questions.

######Required Parameters (choose 1):######
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

######Conditional Parameters (choose 0-n):######
 - dryrun (values: 0,1)
 - migratesingleanswer (values: 0,1)
 - migratechildren (values: 0,1)

  The Dryrun Option is enabled (1) by default.
  With Dryrun enabled no changes will be made to the database.
  Use Dryrun to receive information about possible issues before 
  migrating.

  The MigrateSingleAnswer Option is disabled (0) by default.
  With migratesingleanswer enabled those Multichoice Questions 
  with only one correct option are included into the Migration 
  to MTF as well.

  The MigrateChildren Option is disabled (0) by default.
  With migratechildren enabled those Multichoice Questions which 
  have a parent are migrated. However the questions are migrated 
  on their own without maintaining the parent-child structure.

######Examples######

 - Migrate MTF Questions in a specific course:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?courseid=55
 - Migrate MTF Questions in a specific category:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?categoryid=1
 - Migrate all MTF Questions:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1
 - Disable Dryrun:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0
 - Enable MigrateSingleAnswer:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0&migratesingleanswer=1
 - Enable MigrateChildren:
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0&migratechildren=1







#qtype_mtf Migration > qtype_multichoice#
##GERMAN##

######Beschreibung:######
Das Skript mig_mtf_to_multichoice.php migriert Fragen des Typs qtype_mtf
in den Fragentyp qtype_multichoice. Es werden keine Fragen überschrieben 
oder gelöscht, sondern immer nur neue Fragen erstellt.

Nur Website-Administratoren können das Skript ausführen. 

######Erforderliche Parameter (einer auswählbar):######
 - courseid (Akzeptierte Werte: eine gültige Kurs-ID)
 - categoryid (Akzeptierte Werte: eine gültige Category-ID)
 - all (Akzeptierte Werte: 1)

######Conditional Parameters (0-n auswählbar):######
 - dryrun (Akzeptierte Werte: 0,1)
 - autoweights (Akzeptierte Werte: 0,1)
 - migratechildren (Akzeptierte Werte: 0,1)

  Dryrun ist standardmässig aktiviert (1). Falls Dryrun aktiviert
  ist, werden keine Änderungen an der Datenbank vorgenommen. Lassen
  Sie Dryun aktiviert um mögliche Fehler und Probleme vor der 
  eigentlichen Migration ausfindig zu machen.

  Die Autoweights Option ist standardmässig deaktiviert (0).
  In der Regel werden bei der Migration von MTF zu Multichoice Fragen
  Grades für richtige und falsche Antworten gleichmässig verteilt.
  Trotzdem kommt es in manchen Fällen vor, dass die Summe dieser
  Grades auf richtige oder falsche Antworten nicht 100% ergibt.
  Wenn Autoweights aktiviert wurde, werden die einzelnen Grades so gesetzt,
  dass ihre Werte in der Summe 100% ergeben. Wenn Autoweights deaktiviert 
  ist, werden die betroffenen Fragen bei der Migration übersprungen.

  Die migratechildren Option ist standardmässig deaktiviert (0).
  Wenn migratechildren aktiviert ist, so werden auch Multichoice Fragen 
  migriert, welche ein Parent-Attribut haben. Diese Fragen werden 
  migriert ohne dabei die Parent-Child Struktur beizubehalten.

##ENGLISH##

######Description:######
The Script mig_multichoice_to_mtf.php migrates questions of the type 
qtype_mtf to the questiontype qtype_mtf. No questions will be overwritten 
or deleted, the script will solely create new questions.

######Required Parameters (choose 1):######
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

######Conditional Parameters (choose 0-n):######
 - dryrun (values: 0,1)
 - autoweights (values: 0,1)
 - migratechildren (values: 0,1)

  The Dryrun Option is enabled (1) by default.
  With Dryrun enabled no changes will be made to the database.
  Use Dryrun to receive information about possible issues before 
  migrating.

  The Autoweights Options is disabled (0) by default.
  While migrating from MTF to Multichoice, grades for correct or 
  incorrect answers are usually set equal. However in some cases 
  the SUM of all grades does not match 100%. With Autoweights enabled 
  different grades will be set to match a SUM of 100%. With Autoweights 
  disabled the affected question will be ignored in migration.

  The MigrateChildren Option is disabled (0) by default.
  With migratechildren enabled those Multichoice Questions which 
  have a parent are migrated. However the questions are migrated 
  on their own without maintaining the parent-child structure.

######Examples######

 - Migrate MTF Questions in a specific course:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?courseid=55
 - Migrate MTF Questions in a specific category:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?categoryid=1
 - Migrate all MTF Questions:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1
 - Disable Dryrun:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1&dryrun=0
 - Enable AutoWeights:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1&dryrun=0&autoweights=1
 - Enable MigrateChildren:
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1&dryrun=0&migratechildren=1