<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=postgres',
            'username' => 'postgres',
            'password' => 'root',
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql' => [
                    'class' => 'yii\db\pgsql\Schema',
                    'defaultSchema' => 'public'
                ]
            ], // PostgreSQL MFL
        ],
        'db1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=mfl_nids',
            'username' => 'postgres',
            'password' => 'root',
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql' => [
                    'class' => 'yii\db\pgsql\Schema',
                    'defaultSchema' => 'public'
                ]
            ], // PostgreSQL NIDS
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'allapps.noreply@gmail.com',
                'password' => 'ldpbzoekqyreoljo',
                //'password' => 'ldpbzoekqyreoljo',
                'port' => 465,
                'encryption' => 'ssl',
            ],
        ],
    ],
];
