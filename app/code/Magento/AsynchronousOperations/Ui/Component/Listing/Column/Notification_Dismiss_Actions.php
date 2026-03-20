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
 * Class NotificationDismissActions
 */
class Notification_Dismiss_Actions extends Column
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
            if (isset($item['status']) && ($item['status'] === Bulk_Summary_Interface::FINISHED_SUCCESSFULLY || $item['status'] === Bulk_Summary_Interface::FINISHED_WITH_FAILURE)) {
                $item[$this->get_data('name')]['dismiss'] = ['callback' => [['provider' => 'ns = notification_area, index = columns', 'target' => 'dismiss', 'params' => [0 => $item['uuid']]]], 'href' => '#', 'label' => __('Dismiss')];
            }
        }
        return $data_source;
    }
}