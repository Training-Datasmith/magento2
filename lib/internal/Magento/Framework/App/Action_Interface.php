<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Magento application action controller type. Every action controller in Application should implement this interface.
 *
 * @api
 * @since 100.0.2
 */
interface Action_Interface
{
    public const FLAG_NO_DISPATCH = 'no-dispatch';
    public const FLAG_NO_POST_DISPATCH = 'no-postDispatch';
    public const FLAG_NO_DISPATCH_BLOCK_EVENT = 'no-beforeGenerateLayoutBlocksDispatch';
    public const PARAM_NAME_BASE64_URL = 'r64';
    public const PARAM_NAME_URL_ENCODED = 'uenc';
    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute();
}