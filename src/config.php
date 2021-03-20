<?php

return [
    'db' => [
        'host' => '127.0.0.1',
        'user' => 'mysql',
        'name' => 'ufa_db',
        'pass' => 'mysql',
    ],
    'api' => [
        'v' => '2.0',
        'url' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']."/"
    ],
    'google_mail' => [
        'email' => 'simrakss71@gmail.com',
        'password' => 'zhmfilglnhihxrwq',
    ]
];
