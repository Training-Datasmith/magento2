<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Checkout;

use Magento\Cardinal_Commerce\Model\Config;
use Magento\Cardinal_Commerce\Model\Request\Token_Builder;
use Magento\Checkout\Model\Config_Provider_Interface;
/**
 * Configuration provider.
 */
class Config_Provider implements Config_Provider_Interface
{
    /**
     * @var TokenBuilder
     */
    private $request_jwt_builder;
    /**
     * @var Config
     */
    private $config;
    /**
     * @param TokenBuilder $requestJwtBuilder
     * @param Config $config
     */
    public function __construct(Token_Builder $request_jwt_builder, Config $config)
    {
        $this->request_jwt_builder = $request_jwt_builder;
        $this->config = $config;
    }
    /**
     * @inheritdoc
     */
    public function get_config(): array
    {
        $config['cardinal'] = ['environment' => $this->config->get_environment(), 'requestJWT' => $this->request_jwt_builder->build()];
        return $config;
    }
}