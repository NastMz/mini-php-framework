<?php
declare(strict_types=1);

return [
    'rate_limit' => [
        'max_requests' => 60,  // requests per window
        'window_size'  => 60,  // window in seconds
    ],
    // e.g.
//    'db' => [
//        'dsn' => 'mysql:host=127.0.0.1;dbname=mydb;charset=utf8mb4',
//        'user' => 'root',
//        'pass' => 'secret',
//    ],
];
