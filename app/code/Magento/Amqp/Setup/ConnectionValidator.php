<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Amqp\Setup;

use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\FactoryOptions;

/**
 * Class ConnectionValidator - validates Amqp related settings
 */
class ConnectionValidator
{
    public function __construct(private readonly ConnectionFactory $connectionFactory)
    {
    }

    /**
     * Checks Amqp Connection
     *
     * @param string $virtualHost
     * @param string[]|null $sslOptions
     * @return bool true if the connection succeeded, false otherwise
     */
    public function isConnectionValid(
        string $host,
        string $port,
        string $user,
        string $password = '',
        ?string $virtualHost = '',
        bool $ssl = false,
        ?array $sslOptions = null
    ): bool {
        try {
            $options = new FactoryOptions();
            $options->setHost($host);
            $options->setPort($port);
            $options->setUsername($user);
            $options->setPassword($password);
            $options->setVirtualHost($virtualHost);
            $options->setSslEnabled($ssl);

            if ($sslOptions) {
                $options->setSslOptions($sslOptions);
            }

            $connection = $this->connectionFactory->create($options);

            $connection->close();
        } catch (\Exception) {
            return false;
        }

        return true;
    }
}
