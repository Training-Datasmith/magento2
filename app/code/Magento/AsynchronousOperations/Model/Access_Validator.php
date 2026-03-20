<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Authorization\Model\User_Context_Interface;
class Access_Validator
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     */
    private $bulk_summary_factory;
    public function __construct(private readonly User_Context_Interface $user_context, private readonly \Magento\Framework\Entity_Manager\Entity_Manager $entity_manager, \Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface_Factory $bulk_summary_factory)
    {
        $this->bulk_summary_factory = $bulk_summary_factory;
    }
    /**
     * Check if content allowed for current user
     *
     * @param int $bulkUuid
     * @return bool
     */
    public function is_allowed($bulk_uuid)
    {
        /** @var BulkSummaryInterface $bulkSummary */
        $bulk_summary = $this->entity_manager->load($this->bulk_summary_factory->create(), $bulk_uuid);
        if ((int) $bulk_summary->get_user_type() === User_Context_Interface::USER_TYPE_INTEGRATION) {
            return true;
        }
        return (int) $bulk_summary->get_user_id() === (int) $this->user_context->get_user_id();
    }
}