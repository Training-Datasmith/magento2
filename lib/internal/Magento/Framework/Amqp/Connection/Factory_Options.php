<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Amqp\Connection;

/**
 * Options a connection will be created according to.
 */
class Factory_Options
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $port;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $virtual_host;
    /**
     * @var bool
     */
    private $ssl_enabled = false;
    /**
     * @var array|null
     */
    private $ssl_options;
    /**
     * @return string
     */
    public function get_host(): string
    {
        return $this->host;
    }
    /**
     * @param string $host
     *
     * @return void
     */
    public function set_host(string $host)
    {
        $this->host = $host;
    }
    /**
     * @return string
     */
    public function get_port(): string
    {
        return $this->port;
    }
    /**
     * @param string $port
     *
     * @return void
     */
    public function set_port(string $port)
    {
        $this->port = $port;
    }
    /**
     * @return string
     */
    public function get_username(): string
    {
        return $this->username;
    }
    /**
     * @param string $username
     *
     * @return void
     */
    public function set_username(string $username)
    {
        $this->username = $username;
    }
    /**
     * @return string
     */
    public function get_password(): string
    {
        return $this->password;
    }
    /**
     * @param string $password
     *
     * @return void
     */
    public function set_password(string $password)
    {
        $this->password = $password;
    }
    /**
     * @return string|null
     */
    public function get_virtual_host()
    {
        return $this->virtual_host;
    }
    /**
     * @param string|null $virtualHost
     *
     * @return void
     */
    public function set_virtual_host(?string $virtual_host = null)
    {
        $this->virtual_host = $virtual_host;
    }
    /**
     * @return bool
     */
    public function is_ssl_enabled(): bool
    {
        return $this->ssl_enabled;
    }
    /**
     * @param bool $sslEnabled
     *
     * @return void
     */
    public function set_ssl_enabled(bool $ssl_enabled)
    {
        $this->ssl_enabled = $ssl_enabled;
    }
    /**
     * @return array|null
     */
    public function get_ssl_options()
    {
        return $this->ssl_options;
    }
    /**
     * @param array|null $sslOptions
     *
     * @return void
     */
    public function set_ssl_options(?array $ssl_options = null)
    {
        $this->ssl_options = $ssl_options;
    }
}