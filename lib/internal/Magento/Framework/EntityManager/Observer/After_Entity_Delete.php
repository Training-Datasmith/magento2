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
 * Class AfterEntityDelete
 */
class After_Entity_Delete implements Observer_Interface
{
    /**
     * Apply model delete operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->get_event()->get_entity();
        if ($entity instanceof Abstract_Model) {
            $entity->get_resource()->after_delete($entity);
            $entity->is_deleted(true);
            $entity->after_delete();
            $entity->get_resource()->add_commit_callback([$entity, 'afterDeleteCommit']);
        }
    }
}