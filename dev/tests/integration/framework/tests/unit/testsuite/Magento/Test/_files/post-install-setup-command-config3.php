<?php

declare(strict_types=1);
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

return [
    [
        'command' => 'fake:command',
        'config' => [
            // arguments
            'foo',
            'bar',

            // options
            '--option1' => 'baz',
            '-option2' => 'qux',
        ],
    ],
];
