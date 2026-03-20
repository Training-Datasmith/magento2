<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\Factory_Options;
/**
 * Class ConnectionValidator - validates Amqp related settings
 */
class Connection_Validator
{
    public function __construct(private readonly Connection_Factory $connection_factory)
    {
    }
    /**
     * Checks Amqp Connection
     *
     * @param string $virtualHost
     * @param string[]|null $sslOptions
     * @return bool true if the connection succeeded, false otherwise
     */
    public function is_connection_valid(string $host, string $port, string $user, string $password = '', ?string $virtual_host = '', bool $ssl = false, ?array $ssl_options = null): bool
    {
        try {
            $options = new Factory_Options();
            $options->set_host($host);
            $options->set_port($port);
            $options->set_username($user);
            $options->set_password($password);
            $options->set_virtual_host($virtual_host);
            $options->set_ssl_enabled($ssl);
            if ($ssl_options) {
                $options->set_ssl_options($ssl_options);
            }
            $connection = $this->connection_factory->create($options);
            $connection->close();
        } catch (\Exception) {
            return false;
        }
        return true;
    }
}