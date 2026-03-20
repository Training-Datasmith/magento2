<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Framework\App\Action\Http_Post_Action_Interface;
/**
 * Get most viewed products controller.
 */
class Products_Viewed extends Ajax_Block implements Http_Post_Action_Interface
{
    /**
     * Gets most viewed products list
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $output = $this->layout_factory->create()->create_block(\Magento\Backend\Block\Dashboard\Tab\Products\Viewed::class)->to_html();
        $result_raw = $this->result_raw_factory->create();
        return $result_raw->set_contents($output);
    }
}