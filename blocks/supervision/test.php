<?php
$date = '7-08-2012';

$time = strtotime($date);
echo $time."\n";

$time2 = strtotime('+2 days', $time);
echo date("D M j G:i:s T Y", $time2)."\n";
$day = getdate($time2);
echo 'wday= '.$day['wday']."\n";
$calendar_weekend = isset($CFG->calendar_weekend) ? intval($CFG->calendar_weekend) : 65;
if($calendar_weekend & (1 << ($day['wday'] % 7))) {
    echo "is weekend \n";
}
die;