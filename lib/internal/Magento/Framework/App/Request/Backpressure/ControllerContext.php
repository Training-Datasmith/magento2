<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Controller request context
 */
class Controller_Context implements Context_Interface
{
    /**
     * @var RequestInterface
     */
    private Request_Interface $request;
    /**
     * @var string
     */
    private string $identity;
    /**
     * @var int
     */
    private int $identity_type;
    /**
     * @var string
     */
    private string $type_id;
    /**
     * @var ActionInterface
     */
    private Action_Interface $action;
    /**
     * @param RequestInterface $request
     * @param string $identity
     * @param int $identityType
     * @param string $typeId
     * @param ActionInterface $action
     */
    public function __construct(Request_Interface $request, string $identity, int $identity_type, string $type_id, Action_Interface $action)
    {
        $this->request = $request;
        $this->identity = $identity;
        $this->identity_type = $identity_type;
        $this->type_id = $type_id;
        $this->action = $action;
    }
    /**
     * @inheritDoc
     */
    public function get_request(): Request_Interface
    {
        return $this->request;
    }
    /**
     * @inheritDoc
     */
    public function get_identity(): string
    {
        return $this->identity;
    }
    /**
     * @inheritDoc
     */
    public function get_identity_type(): int
    {
        return $this->identity_type;
    }
    /**
     * @inheritDoc
     */
    public function get_type_id(): string
    {
        return $this->type_id;
    }
    /**
     * Controller instance
     *
     * @return ActionInterface
     */
    public function get_action(): Action_Interface
    {
        return $this->action;
    }
}