<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\Controller\Result_Factory;
class Grid extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->result_factory->create(Result_Factory::TYPE_PAGE);
    }
}