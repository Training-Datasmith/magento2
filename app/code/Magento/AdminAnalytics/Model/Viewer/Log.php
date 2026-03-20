<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\Model\Viewer;

use Magento\Framework\Data_Object;
/**
 * Admin Analytics log resource
 */
class Log extends Data_Object
{
    /**
     * Get log id
     *
     * @return int
     */
    public function get_id(): ?int
    {
        return $this->get_data('id');
    }
    /**
     * Get last viewed product version
     *
     * @return string
     */
    public function get_last_view_version(): ?string
    {
        return $this->get_data('last_viewed_in_version');
    }
}