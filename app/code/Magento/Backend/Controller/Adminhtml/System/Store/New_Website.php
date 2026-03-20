<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class New_Website extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_core_registry->register('store_type', 'website');
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $result_forward = $this->result_forward_factory->create();
        return $result_forward->forward('newStore');
    }
}