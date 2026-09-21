<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Practicum Start Date
    |--------------------------------------------------------------------------
    |
    | The first day of week 1. Every counselling log's week number is counted
    | from this date in blocks of seven days.
    |
    */

    'start_date' => env('PRACTICUM_START_DATE', '2026-09-13'),

    /*
    |--------------------------------------------------------------------------
    | Number of Weeks
    |--------------------------------------------------------------------------
    |
    | How many weekly sheets (W01, W02, ...) the log book template contains.
    |
    */

    'weeks' => 18,

    /*
    |--------------------------------------------------------------------------
    | Log Book Cover Details
    |--------------------------------------------------------------------------
    |
    | Printed on the cover sheet of the exported log book.
    |
    */

    'student' => [
        'name' => env('PRACTICUM_STUDENT_NAME'),
        'matriculation_no' => env('PRACTICUM_MATRIC_NO'),
        'semester' => env('PRACTICUM_SEMESTER'),
        'site_supervisor' => env('PRACTICUM_SITE_SUPERVISOR'),
        'academic_supervisor' => env('PRACTICUM_ACADEMIC_SUPERVISOR'),
        'organization' => env('PRACTICUM_ORGANIZATION'),
        'site_address' => env('PRACTICUM_SITE_ADDRESS'),
    ],

];
