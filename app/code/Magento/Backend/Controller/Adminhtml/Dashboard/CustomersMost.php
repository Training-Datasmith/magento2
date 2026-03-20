<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class Customers_Most extends Ajax_Block
{
    /**
     * Gets the list of most active customers
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $output = $this->layout_factory->create()->create_block(\Magento\Backend\Block\Dashboard\Tab\Customers\Most::class)->to_html();
        $result_raw = $this->result_raw_factory->create();
        return $result_raw->set_contents($output);
    }
}