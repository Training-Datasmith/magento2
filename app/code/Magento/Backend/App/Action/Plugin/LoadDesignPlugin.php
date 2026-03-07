<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\DesignLoader;

/**
 * Workaround to load Design before Backend Action dispatch.
 *
 * @FIXME Remove when \Magento\Backend\App\AbstractAction::dispatch refactored.
 */
class LoadDesignPlugin
{
    public function __construct(private readonly DesignLoader $designLoader)
    {
    }

    /**
     * Initiates design before dispatching Backend Actions.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $backendAction, RequestInterface $request): void
    {
        $this->designLoader->load();
    }
}
