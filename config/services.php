<?php

return [
    /*
     * CAS configuration
     * As a reminder, this application will only work with CAS 1.0
     * (usage of /v1/users endpoint to verify credentials)
     */
    'cas' => [
        'endpoint' => env('CAS_ENDPOINT'),
        'path' => env('CAS_PATH', '/cas')
    ]
];
