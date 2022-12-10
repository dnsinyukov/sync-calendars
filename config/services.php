<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'redirect_callback' => env('GOOGLE_REDIRECT_CALLBACK'),
        'scopes' => [
            \Google_Service_Calendar::CALENDAR_EVENTS_READONLY,
            \Google_Service_Calendar::CALENDAR_READONLY,
            \Google_Service_Oauth2::OPENID,
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_Oauth2::USERINFO_PROFILE,
        ],
        'approval_prompt' => env('GOOGLE_APPROVAL_PROMPT', 'force'),
        'access_type' => env('GOOGLE_ACCESS_TYPE', 'offline'),
        'include_granted_scopes' => true,
    ],
];
