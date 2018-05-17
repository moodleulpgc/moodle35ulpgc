<?
if (! defined ( 'CLI_SCRIPT' )) {
	define ( 'CLI_SCRIPT', true );
}
require_once (dirname ( __FILE__ ) . '/../../config.php');

function array_ficheros($dir) {
	$dirFiles = array ();
	if ($handle = opendir ( $dir )) {
		while ( false !== ($file = readdir ( $handle )) ) {
			// hides folders, writes out ul of images and thumbnails from two
			// folders
			if ($file != "." && $file != ".." && $file != "index.php" && $file != ".DS_Store") {
				$dirFiles [] = $file;
			}
		}
		closedir ( $handle );
	}
	sort ( $dirFiles, SORT_NUMERIC );
	return $dirFiles;
}
$ruta = $CFG->dataroot . '/multibackup';
$dirFiles = array_ficheros ( $ruta );
foreach ( $dirFiles as $file ) {
	$v_filename = explode ( '-', $file );
	array_splice ( $v_filename, 5, 2 );
	$v_filename = implode ( '-', $v_filename );
	if (rename ( $ruta . '/' . $file, $ruta . '/' . $v_filename )) {
		echo 'Renombrado '.$file.' a '. $v_filename."\n";
	}
}
?>