<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\App\Action\Plugin;

use Magento\Backend\App\Abstract_Action;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\View\Design_Loader;
/**
 * Workaround to load Design before Backend Action dispatch.
 *
 * @FIXME Remove when \Magento\Backend\App\AbstractAction::dispatch refactored.
 */
class Load_Design_Plugin
{
    public function __construct(private readonly Design_Loader $design_loader)
    {
    }
    /**
     * Initiates design before dispatching Backend Actions.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before_dispatch(Abstract_Action $backend_action, Request_Interface $request): void
    {
        $this->design_loader->load();
    }
}