<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\App\Backpressure\Identity_Provider_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Creates context for current request
 */
class Context_Factory
{
    /**
     * @var RequestTypeExtractorInterface
     */
    private Request_Type_Extractor_Interface $extractor;
    /**
     * @var IdentityProviderInterface
     */
    private Identity_Provider_Interface $identity_provider;
    /**
     * @var RequestInterface
     */
    private Request_Interface $request;
    /**
     * @param RequestTypeExtractorInterface $extractor
     * @param IdentityProviderInterface $identityProvider
     * @param RequestInterface $request
     */
    public function __construct(Request_Type_Extractor_Interface $extractor, Identity_Provider_Interface $identity_provider, Request_Interface $request)
    {
        $this->extractor = $extractor;
        $this->identity_provider = $identity_provider;
        $this->request = $request;
    }
    /**
     * Create context if possible
     *
     * @param ActionInterface $action
     * @return ContextInterface|null
     */
    public function create(Action_Interface $action): ?Context_Interface
    {
        $type_id = $this->extractor->extract($this->request, $action);
        if ($type_id === null) {
            return null;
        }
        return new Controller_Context($this->request, $this->identity_provider->fetch_identity(), $this->identity_provider->fetch_identity_type(), $type_id, $action);
    }
}