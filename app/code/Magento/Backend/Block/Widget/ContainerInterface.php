<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

/**
 * @api
 * @since 100.0.2
 */
interface Container_Interface extends \Magento\Backend\Block\Widget\Button\Context_Interface
{
    /**
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return $this
     */
    public function add_button($button_id, $data, $level = 0, $sort_order = 0, $region = 'toolbar');
    /**
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @return $this
     */
    public function remove_button($button_id);
    /**
     * Public wrapper for protected _updateButton method
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return $this
     */
    public function update_button($button_id, $key, $data);
}