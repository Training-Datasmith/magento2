<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
/**
 * Class Actions
 */
class Actions extends Column
{
    /**
     * Prepare Data Source
     *
     * @return array
     */
    public function prepare_data_source(array $data_source)
    {
        $data_source = parent::prepare_data_source($data_source);
        if (empty($data_source['data']['items'])) {
            return $data_source;
        }
        foreach ($data_source['data']['items'] as &$item) {
            $item[$this->get_data('name')]['edit'] = ['href' => $this->context->get_url('bulk/bulk/details', ['uuid' => $item['uuid']]), 'label' => __('Details'), 'hidden' => false];
        }
        return $data_source;
    }
}