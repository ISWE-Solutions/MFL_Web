<?php

return [
    'adminEmail' => 'mfl@noreply.gov.zm',
    'senderEmail' => 'mfl@noreply.gov.zm',
    'senderName' => 'MFL',
    'supportEmail' => 'mfl@noreply.gov.zm',
    'senderEmail' => 'mfl@noreply.gov.zm',
    'bsVersion' => '4',
    'user.passwordResetTokenExpire' => 3600,
    'maskMoneyOptions' => [
        'prefix' => 'ZMW ',
        'suffix' => '',
        'affixesStay' => true,
        'thousands' => ',',
        'decimal' => '.',
        'precision' => 2,
        'allowZero' => false,
        'allowNegative' => false,
    ],
    //Google maps configs
    'center_lat' => -13.889884,
    'center_lng' => 28.368405,
    'polygon_zoom' => 6,
    'polygon_strokeColor' => '#FF0000',
    'polygon_strokeColor' => '#FF0000',
    'cache_duration' => 60,
    //AMQ Configs
    'amqHost' => 'localhost',
    'amqPort' => 5672,
    'amqUsername' => 'guest',
    'amqPassword' => 'guest',
    //Queues should be in an array i.e ['queue one','queue two',...,'nth queue']
    'amqQueues' => [
        'HPCZ', 'MFL'
    ],
    'api_url' => "http://localhost:8082/mfl/v2/encode/{key}",
];
