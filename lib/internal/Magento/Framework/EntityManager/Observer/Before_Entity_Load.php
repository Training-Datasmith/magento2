<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Model\Abstract_Model;
/**
 * Class BeforeEntityLoad
 */
class Before_Entity_Load
{
    /**
     * Apply model before load operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $identifier = $observer->get_event()->get_identifier();
        $entity = $observer->get_event()->get_entity();
        if ($entity instanceof Abstract_Model) {
            $entity->before_load($identifier);
        }
    }
}