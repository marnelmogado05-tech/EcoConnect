<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID credentials
    |--------------------------------------------------------------------------
    |
    | Identifies this application to the browser push services. Generate the pair once
    | and keep it stable: rotating it invalidates every subscription already stored.
    |
    | These used to live in config/firebase.php, published by kreait/laravel-firebase —
    | a package the application no longer uses. Two further notification services were
    | built on it (FirebaseNotificationService and FirebaseService) and neither was ever
    | called; web push via minishlink/web-push is the only path in use.
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', env('APP_URL')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

];
