<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Amqp\Connection;

use Magento\Framework\App\Object_Manager;
use Php_Amqp_Lib\Connection\Abstract_Connection;
use Php_Amqp_Lib\Connection\Amqp_Connection_Config;
use Php_Amqp_Lib\Connection\Amqp_Connection_Factory;
/**
 * Create connection based on options.
 */
class Factory
{
    /**
     * Create connection according to given options.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param FactoryOptions $options
     * @return AbstractConnection
     */
    public function create(Factory_Options $options): Abstract_Connection
    {
        $config = Object_Manager::get_instance()->create(Amqp_Connection_Config::class);
        // Set host, port, user, password, and vhost from options
        $config->set_host($options->get_host());
        $config->set_port((int) $options->get_port());
        $config->set_user($options->get_username());
        $config->set_password($options->get_password());
        $config->set_vhost($options->get_virtual_host() !== null ? $options->get_virtual_host() : '/');
        // Set SSL options if SSL is enabled
        if ($options->is_ssl_enabled()) {
            $config->set_is_secure(true);
            $ssl_options = $options->get_ssl_options();
            if ($ssl_options) {
                if (isset($ssl_options['cafile'])) {
                    $config->set_ssl_ca_cert($ssl_options['cafile']);
                }
                if (isset($ssl_options['local_cert'])) {
                    $config->set_ssl_cert($ssl_options['local_cert']);
                }
                if (isset($ssl_options['local_pk'])) {
                    $config->set_ssl_key($ssl_options['local_pk']);
                }
                if (isset($ssl_options['verify_peer'])) {
                    $config->set_ssl_verify($ssl_options['verify_peer']);
                }
                if (isset($ssl_options['verify_peer_name'])) {
                    $config->set_ssl_verify_name($ssl_options['verify_peer_name']);
                }
                if (isset($ssl_options['passphrase'])) {
                    $config->set_ssl_pass_phrase($ssl_options['passphrase']);
                }
                if (isset($ssl_options['ciphers'])) {
                    $config->set_ssl_ciphers($ssl_options['ciphers']);
                }
            } else {
                // Default SSL verification option
                $config->set_ssl_verify(true);
            }
        } else {
            $config->set_is_secure(false);
        }
        // Use the connection factory to create the connection
        return Amqp_Connection_Factory::create($config);
    }
}