<?php

declare(strict_types=1);
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

return [
    [
        'command' => 'setup:db-schema:add-slave',
        'config' => [
            '--host' => '/tmp/mysql.sock',
            '--dbname' => 'magento_replica',
            '--username' => 'root',
            '--password' => 'secret',
        ],
    ],
];
