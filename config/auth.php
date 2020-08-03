<?php

use App\Model\Cms\UserCms;
use App\Model\Website\User;

return [

    'defaults' => [
        'guard' => 'website',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'website' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'cms' => [
            'driver' => 'session',
            'provider' => 'users_cms',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Model\Website\User::class,
        ],

        'users_cms' => [
            'driver' => 'eloquent',
            'model' => App\Model\Cms\UserCms::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],

        'users_cms' => [
            'provider' => 'users_cms',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
