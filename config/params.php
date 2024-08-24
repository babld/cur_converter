<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'curParser' => [
        'ru' => [
            'class' => \app\components\parsers\Cbrf::class,
            'code' => 'ru',
            'id' => 1,
        ],
        'th' => [
            'class' => \app\components\parsers\Tha::class,
            'code' => 'th',
            'id' => 2
        ],
    ]
];
