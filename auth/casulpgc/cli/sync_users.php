<?php
if (! defined ( 'CLI_SCRIPT' )) {
	define ( 'CLI_SCRIPT', true );
}

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array('noupdate'=>false, 'verbose'=>false, 'help'=>false, 'fecha'=>false), array('n'=>'noupdate', 'v'=>'verbose', 'h'=>'help', 'f'=>'fecha'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute user account sync with external database.
The auth_db plugin must be enabled and properly configured.

Options:
-n, --noupdate        Skip update of existing users
-v, --verbose         Print verbose progess information
-h, --help            Print out this help

Example:
sync.php
";

    echo $help;
    die;
}

if (!is_enabled_auth('casulpgc')) {
    echo "Plugin not enabled!";
    exit(1);
}

$verbose = !empty($options['verbose']);
$update = empty($options['noupdate']);

$casulpgcauth = get_auth_plugin('casulpgc');
return $casulpgcauth->sync_users($update, $verbose);

