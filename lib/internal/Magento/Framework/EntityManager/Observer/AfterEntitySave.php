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
 * Class AfterEntitySave
 */
class After_Entity_Save implements Observer_Interface
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
            if (method_exists($entity->get_resource(), 'loadAllAttributes')) {
                $entity->get_resource()->load_all_attributes($entity);
            }
            $entity->get_resource()->after_save($entity);
            $entity->after_save();
            $entity->get_resource()->add_commit_callback([$entity, 'afterCommitCallback']);
            if ($entity->get_resource() instanceof Abstract_Db) {
                $entity->get_resource()->unserialize_fields($entity);
            }
            $entity->set_has_data_changes(false);
        }
    }
}