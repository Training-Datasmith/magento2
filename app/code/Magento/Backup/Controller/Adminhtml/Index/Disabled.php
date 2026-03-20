<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\View\Result\Page_Factory;
/**
 * Inform that backup is disabled.
 */
class Disabled extends Action implements Http_Get_Action_Interface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::backup';
    /**
     * @var PageFactory
     */
    private $page_factory;
    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, Page_Factory $page_factory)
    {
        parent::__construct($context);
        $this->page_factory = $page_factory;
    }
    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->page_factory->create();
    }
}