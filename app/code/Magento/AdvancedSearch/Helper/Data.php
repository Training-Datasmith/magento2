<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Search\Helper;

use Magento\Framework\App\Helper\Abstract_Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Search\Engine_Resolver_Interface;
use Open_Search\Client;
class Data extends Abstract_Helper
{
    public const OPENSEARCH = 'opensearch';
    public const MAJOR_VERSION = '2';
    /**
     * @var EngineResolverInterface
     */
    public $engine_resolver;
    public function __construct(Context $context, Engine_Resolver_Interface $engine_resolver)
    {
        parent::__construct($context);
        $this->engine_resolver = $engine_resolver;
    }
    /**
     * Check if opensearch v2.x
     */
    public function is_client_open_search_v2(): bool
    {
        $search_engine = $this->engine_resolver->get_current_search_engine();
        if (stripos($search_engine, self::OPENSEARCH) === false) {
            return false;
        }
        if (substr(Client::VERSION, 0, 1) == self::MAJOR_VERSION) {
            return true;
        }
        return false;
    }
}