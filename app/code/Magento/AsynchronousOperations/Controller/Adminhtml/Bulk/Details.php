<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Controller\Adminhtml\Bulk;

/**
 * Class View Operation Details Controller
 */
class Details extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * Details constructor.
     * @param string $menuId
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        private readonly \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        private readonly \Magento\AsynchronousOperations\Model\AccessValidator $accessValidator,
        private $menuId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations'
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Magento_Logging::system_magento_logging_bulk_operations')
            && $this->accessValidator->isAllowed($this->getRequest()->getParam('uuid'));
    }

    /**
     * Bulk details action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $bulkId = $this->getRequest()->getParam('uuid');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu($this->menuId);
        $resultPage->getConfig()->getTitle()->prepend(__('Action Details - #' . $bulkId));

        return $resultPage;
    }
}
