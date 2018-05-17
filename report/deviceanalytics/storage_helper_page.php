<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * helper page for the loggedin event. its only necessary because of the display size
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('lib.php');
ini_set('memory_limit' , '1024M');
require_login();

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/report/deviceanalytics/storage_helper_page.php');
$PAGE->set_pagelayout('redirect');
$PAGE->requires->css('/report/deviceanalytics/css/storage_helper_page_css.css');
$PAGE->requires->js('/report/deviceanalytics/libs/jquery-1.12.2.min.js', true);

$datastorage = new deviceanalytics_data_storage();
$insertid = $datastorage->deviceanalytics_user_loggedin();
$CFG->additionalhtmlhead .= '<noscript>
<meta http-equiv="refresh" content="0;url='.$CFG->wwwroot.'">
</noscript>';
echo $OUTPUT->header();
if (isset($SESSION->wantsurl)) {
	$urltogo = $SESSION->wantsurl;
} else {
	$urltogo = $CFG->wwwroot.'/';
}
unset($SESSION->wantsurl);
?>
<script type="text/javascript">
    $( document ).ready(function() {
        if(<?php echo $insertid;?> == 0){
            window.location.replace("<?php echo $urltogo; ?>");
        }else{
            var ajaxurl = 'ajaxcall.php?' + 'sesskey=' + M.cfg.sesskey + '&insertid=' + <?php echo $insertid; ?>;
            var screensize = {
                'device_display_size_x': screen.width, 
                'device_display_size_y': screen.height, 
                'device_window_size_x': $(window).width(), 
                'device_window_size_y': $(window).height()
            }
            $.ajax({
                type: "GET",
                url: ajaxurl,
                data: screensize,
            }).done(function(html) {
                window.location.replace("<?php echo $urltogo; ?>");
            });
        }
    });
</script>
<?php
echo $OUTPUT->footer();
