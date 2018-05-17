<?php

/**
 * Callback for GeoGebraTube repository.
 *
 * @package        repository_geogebratube
 * @author         Christoph Stadlbauer <christoph.stadlbauer@geogebra.org>
 * @copyright  (c) International GeoGebra Institute 2014
 * @license        http://www.geogebra.org/license
 */
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . '/config.php');

$url = required_param('url', PARAM_RAW);
$author = required_param('author', PARAM_RAW);
$thumb = required_param('thumb', PARAM_RAW);

require_login();

$args = explode('/', $url);
$filename = '';
$filename = s(clean_param(array_pop($args) . '.html', PARAM_FILE)); // Hack to provide a file type known to moodle.

$js = <<<EOD
<html>
<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript">
    window.onload = function() {
        var resource = {};
        resource.title = "$filename";
        resource.source = "$url";
        resource.thumbnail = '$thumb';
        resource.author = "$author";
        parent.M.core_filepicker.select_file(resource);
    }
    </script>
</head>
<body><noscript></noscript></body>
</html>
EOD;

header('Content-Type: text/html; charset=utf-8');
die($js);
