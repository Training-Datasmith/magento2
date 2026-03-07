<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Client;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @api
 * @since 100.1.0
 */
class ClientResolver
{
    /**
     * Scope configuration
     *
     * @var ScopeConfigInterface
     * @since 100.1.0
     * @deprecated 100.3.0 since it is not used anymore
     * @see not used
     */
    protected $scopeConfig;

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
        protected \Magento\Framework\ObjectManagerInterface $objectManager,
        /**
         * Pool of existing client factories
         */
        private array $clientFactoryPool,
        /**
         * Pool of client option classes
         */
        private array $clientOptionsPool,
        private readonly EngineResolverInterface $engineResolver
    ) {
    }

    /**
     * Returns configured search engine
     *
     * @return string
     * @since 100.1.0
     */
    public function getCurrentEngine()
    {
        return $this->engineResolver->getCurrentSearchEngine();
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
        $engine = $engine ?: $this->getCurrentEngine();

        if (!isset($this->clientFactoryPool[$engine])) {
            throw new \LogicException(
                'There is no such client factory: ' . $engine
            );
        }
        $factoryClass = $this->clientFactoryPool[$engine];
        $factory = $this->objectManager->create($factoryClass);
        if (!($factory instanceof ClientFactoryInterface)) {
            throw new \InvalidArgumentException(
                'Client factory must implement \Magento\AdvancedSearch\Model\Client\ClientFactoryInterface'
            );
        }

        $optionsClass = $this->clientOptionsPool[$engine];
        $clientOptions = $this->objectManager->create($optionsClass);
        if (!($clientOptions instanceof ClientOptionsInterface)) {
            throw new \InvalidArgumentException(
                'Client options must implement \Magento\AdvancedSearch\Model\Client\ClientInterface'
            );
        }

        return $factory->create($clientOptions->prepareClientOptions($data));
    }
}
