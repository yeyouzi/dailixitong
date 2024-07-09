<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            0 => 'easysms',
        ],
        'sms_notice' => [
            0 => 'easysms',
        ],
        'sms_check' => [
            0 => 'easysms',
        ],
        'admin_login_init' => [
            0 => 'loginbg',
        ],
    ],
    'route' => [
    ],
    'service' => [
    ],
];
