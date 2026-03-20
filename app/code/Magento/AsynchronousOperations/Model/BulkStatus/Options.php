<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Bulk_Status;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
class Options implements \Magento\Framework\Data\Option_Source_Interface
{
    /**
     * @inheritDoc
     */
    public function to_option_array(): array
    {
        return [['value' => Bulk_Summary_Interface::NOT_STARTED, 'label' => __('Not Started')], ['value' => Bulk_Summary_Interface::IN_PROGRESS, 'label' => __('In Progress')], ['value' => Bulk_Summary_Interface::FINISHED_SUCCESSFULLY, 'label' => __('Finished Successfully')], ['value' => Bulk_Summary_Interface::FINISHED_WITH_FAILURE, 'label' => __('Finished with Failure')]];
    }
}