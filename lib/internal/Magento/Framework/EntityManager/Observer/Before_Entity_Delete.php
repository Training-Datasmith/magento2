<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Model\Abstract_Model;
/**
 * Class BeforeEntityDelete
 */
class Before_Entity_Delete implements Observer_Interface
{
    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->get_event()->get_entity();
        if ($entity instanceof Abstract_Model) {
            $entity->before_delete();
            $entity->get_resource()->before_delete($entity);
        }
    }
}