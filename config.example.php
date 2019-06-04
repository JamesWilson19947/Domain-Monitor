<?php

namespace Config;

use Modules\SSL;
use Modules\DomainExpiry;

class Domains {

    public static $domains = 
    [
        'https://youtube.co.uk' => [
            'alert' => [
                'days' => 30, # Days To Alert
                'emails' => [
                    'example@example.com',
                ],
            ],
            'checks' => [
                'domainexpiry',
                'ssl'
            ]
        ],

        'https://google.co.uk' => [
            'alert' => [
                'days' => 30, # Days
                'emails' => [
                    'example@example.com',
                ],
            ],
            'checks' => [
                'ssl',
            ]
        ],

    ];
}