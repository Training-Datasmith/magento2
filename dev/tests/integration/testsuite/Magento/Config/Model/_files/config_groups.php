<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

return [
    [
        'groups' => [
            'restrict' => ['fields' => ['allow_ips' => ['value' => '']]],
            'debug' => [
                'fields' => [
                    'template_hints_admin' => ['value' => '0'],
                    'template_hints_blocks' => ['value' => '0'],
                ],
            ],
        ],
    ],
];
