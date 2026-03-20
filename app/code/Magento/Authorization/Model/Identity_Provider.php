<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Authorization\Model;

use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\App\Backpressure\Identity_Provider_Interface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\Php_Environment\Remote_Address;
/**
 * Utilizes UserContext for backpressure identity
 */
class Identity_Provider implements Identity_Provider_Interface
{
    /**
     * User context identity type map
     */
    private const USER_CONTEXT_IDENTITY_TYPE_MAP = [User_Context_Interface::USER_TYPE_CUSTOMER => Context_Interface::IDENTITY_TYPE_CUSTOMER, User_Context_Interface::USER_TYPE_ADMIN => Context_Interface::IDENTITY_TYPE_ADMIN];
    public function __construct(private readonly User_Context_Interface $user_context, private readonly Remote_Address $remote_address)
    {
    }
    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function fetch_identity_type(): int
    {
        if (!$this->user_context->get_user_id()) {
            return Context_Interface::IDENTITY_TYPE_IP;
        }
        $user_type = $this->user_context->get_user_type();
        if ($user_type !== null && isset(self::USER_CONTEXT_IDENTITY_TYPE_MAP[$user_type])) {
            return self::USER_CONTEXT_IDENTITY_TYPE_MAP[$user_type];
        }
        throw new RuntimeException(__('User type not defined'));
    }
    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function fetch_identity(): string
    {
        $user_id = $this->user_context->get_user_id();
        if ($user_id) {
            return (string) $user_id;
        }
        $address = $this->remote_address->get_remote_address();
        if (!$address) {
            throw new RuntimeException(__('Failed to extract remote address'));
        }
        return $address;
    }
}