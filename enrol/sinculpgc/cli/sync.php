<?php
if (! defined ( 'CLI_SCRIPT' )) {
	define ( 'CLI_SCRIPT', true );
}

require(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('verbose'=>false, 'help'=>false, 'fecha'=>false), array('v'=>'verbose', 'h'=>'help', 'f'=>'fecha'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute enrol sync with external database.
The enrol_database plugin must be enabled and properly configured.

Options:
-v, --verbose         Print verbose progress information
-h, --help            Print out this help
-f, --fecha           Fecha a partir de la cual se sincronizan las matrículas, en formato dd/mm/yyyy

Example:
sync.php -f=09/09/2013
";

    echo $help;
    die;
}

if (!enrol_is_enabled('sinculpgc')) {
    cli_error('enrol_sinculpgc plugin is disabled, synchronisation stopped', 2);
}

if (!empty($options['verbose'])) {
    $trace = new text_progress_trace();
} else {
	$trace = false;
}

/** @var enrol_database_plugin $enrol  */
$enrol = enrol_get_plugin('sinculpgc');
$result = 0;
$result = $result | $enrol->sync_enrolments($trace);

exit($result);
