<?php

return [
    'notifylk' => [
        'enabled' => env('SMS_ENABLED', false),
        'url' => env('NOTIFYLK_API_URL', 'https://app.notify.lk/api/v1/send'),
        'user_id' => env('NOTIFYLK_USER_ID'),
        'api_key' => env('NOTIFYLK_API_KEY'),
        'sender_id' => env('NOTIFYLK_SENDER_ID', 'Crow.lk'),
        'country_code' => env('SMS_COUNTRY_CODE', '94'),
    ],
];
