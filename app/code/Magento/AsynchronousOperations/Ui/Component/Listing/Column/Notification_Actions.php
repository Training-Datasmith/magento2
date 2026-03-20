<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Listing\Column;

use Magento\Framework\Bulk\Bulk_Summary_Interface;
use Magento\Ui\Component\Listing\Columns\Column;
/**
 * Class Actions
 */
class Notification_Actions extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepare_data_source(array $data_source)
    {
        $data_source = parent::prepare_data_source($data_source);
        if (empty($data_source['data']['items'])) {
            return $data_source;
        }
        foreach ($data_source['data']['items'] as &$item) {
            if (isset($item['uuid'])) {
                $item[$this->get_data('name')]['details'] = ['callback' => [['provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk', 'target' => 'destroyInserted'], ['provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk', 'target' => 'updateData', 'params' => ['uuid' => $item['uuid']]], ['provider' => 'notification_area.notification_area.modalContainer.modal', 'target' => 'openModal']], 'href' => '#', 'label' => __('View Details')];
                if (isset($item['status']) && ($item['status'] === Bulk_Summary_Interface::FINISHED_SUCCESSFULLY || $item['status'] === Bulk_Summary_Interface::FINISHED_WITH_FAILURE)) {
                    $item[$this->get_data('name')]['details']['callback'][] = ['provider' => 'ns = notification_area, index = columns', 'target' => 'dismiss', 'params' => [0 => $item['uuid']]];
                }
            }
        }
        return $data_source;
    }
}