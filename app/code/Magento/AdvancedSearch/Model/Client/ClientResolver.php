<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Client;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Search\Engine_Resolver_Interface;
/**
 * @api
 * @since 100.1.0
 */
class Client_Resolver
{
    /**
     * Scope configuration
     *
     * @var ScopeConfigInterface
     * @since 100.1.0
     * @deprecated 100.3.0 since it is not used anymore
     * @see not used
     */
    protected $scope_config;
    /**
     * Config path
     *
     * @var string
     * @since 100.1.0
     * @deprecated 100.3.0 since it is not used anymore
     * @see not used
     */
    protected $path;
    /**
     * Config Scope
     *
     * @var string
     * @since 100.1.0
     * @deprecated 100.3.0 since it is not used anymore
     * @see not used
     */
    protected $scope;
    public function __construct(
        /**
         * Object Manager instance
         *
         * @since 100.1.0
         */
        protected \Magento\Framework\Object_Manager_Interface $object_manager,
        /**
         * Pool of existing client factories
         */
        private array $client_factory_pool,
        /**
         * Pool of client option classes
         */
        private array $client_options_pool,
        private readonly Engine_Resolver_Interface $engine_resolver
    )
    {
    }
    /**
     * Returns configured search engine
     *
     * @return string
     * @since 100.1.0
     */
    public function get_current_engine()
    {
        return $this->engine_resolver->get_current_search_engine();
    }
    /**
     * Create client instance
     *
     * @param string $engine
     * @return ClientInterface
     * @since 100.1.0
     */
    public function create($engine = '', array $data = [])
    {
        $engine = $engine ?: $this->get_current_engine();
        if (!isset($this->client_factory_pool[$engine])) {
            throw new \LogicException('There is no such client factory: ' . $engine);
        }
        $factory_class = $this->client_factory_pool[$engine];
        $factory = $this->object_manager->create($factory_class);
        if (!$factory instanceof Client_Factory_Interface) {
            throw new \InvalidArgumentException('Client factory must implement \Magento\AdvancedSearch\Model\Client\ClientFactoryInterface');
        }
        $options_class = $this->client_options_pool[$engine];
        $client_options = $this->object_manager->create($options_class);
        if (!$client_options instanceof Client_Options_Interface) {
            throw new \InvalidArgumentException('Client options must implement \Magento\AdvancedSearch\Model\Client\ClientInterface');
        }
        return $factory->create($client_options->prepare_client_options($data));
    }
}