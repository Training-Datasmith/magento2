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
use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
/**
 * Class BeforeEntitySave
 */
class Before_Entity_Save implements Observer_Interface
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
            if ($entity->get_resource() instanceof Abstract_Db) {
                $entity = $entity->get_resource()->serialize_fields($entity);
            }
            $entity->validate_before_save();
            $entity->before_save();
            $entity->set_parent_id((int) $entity->get_parent_id());
            $entity->get_resource()->before_save($entity);
        }
    }
}