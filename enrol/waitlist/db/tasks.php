<?php

defined('MOODLE_INTERNAL') || die();


$tasks = [
    [
        'classname' => 'enrol_waitlist\task\update_enrolments',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    
    // ecastro ULPGC
    [
        'classname' => 'enrol_waitlist\task\send_reminders',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    
];
