<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Early Check-in Window (minutes)
    |--------------------------------------------------------------------------
    |
    | How many minutes before the scheduled check-in time a user is allowed
    | to register attendance and still be considered on time.
    |
    */
    'early_check_in_minutes' => (int) env('ATTENDANCE_EARLY_CHECK_IN_MINUTES', 30),
];
