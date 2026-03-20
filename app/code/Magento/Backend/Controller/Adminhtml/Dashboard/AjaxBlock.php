<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Dashboard\Totals;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\Raw_Factory;
use Magento\Framework\View\Layout_Factory;
/**
 * Class used to retrieve content of dashboard totals block via ajax
 */
class Ajax_Block extends Dashboard implements Http_Post_Action_Interface
{
    /**
     * @var RawFactory
     */
    protected $result_raw_factory;
    /**
     * @var LayoutFactory
     */
    protected $layout_factory;
    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(Context $context, Raw_Factory $result_raw_factory, Layout_Factory $layout_factory)
    {
        parent::__construct($context);
        $this->result_raw_factory = $result_raw_factory;
        $this->layout_factory = $layout_factory;
    }
    /**
     * Retrieve block content via ajax
     *
     * @return Raw
     */
    public function execute()
    {
        $output = '';
        $block_tab = $this->get_request()->get_param('block');
        if ($block_tab === 'totals') {
            $output = $this->layout_factory->create()->create_block(Totals::class)->to_html();
        }
        /** @var Raw $resultRaw */
        $result_raw = $this->result_raw_factory->create();
        return $result_raw->set_contents($output);
    }
}