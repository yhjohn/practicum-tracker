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

    /*
    |--------------------------------------------------------------------------
    | Progress Dashboard Targets
    |--------------------------------------------------------------------------
    |
    | Required hours per component, matching the XLSX template's SUMMARY sheet
    | "PROGRESS DASHBOARD" section. The three totals are the program's own
    | minimums and are not simply the sum of the components above them.
    |
    */

    'targets' => [
        'individual_counselling' => 66,
        'group_counselling' => 30,
        'career_family_couple_counselling' => 0,
        'professional_development' => 0,
        'guidance_and_administration' => 141,
        'supervision' => 0,
        'face_to_face_seminar' => 15,
        'direct_client_contact_total' => 96,
        'other_activities_total' => 156,
        'grand_total' => 252,
    ],

];
