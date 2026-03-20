<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Pricing_Import_Export\Controller\Adminhtml\Export;

use Magento\Advanced_Pricing_Import_Export\Model\Export\Advanced_Pricing as ExportAdvancedPricing;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPostActionInterface;
use Magento\Framework\Controller\Result_Factory;
use Magento\Import_Export\Controller\Adminhtml\Export as ExportController;
use Magento\Import_Export\Model\Export\Entity_Filters_Provider_Interface;
class Get_Filter extends Export_Controller implements Http_Get_Action_Interface, Http_Post_Action_Interface
{
    public function __construct(Context $context, private readonly Entity_Filters_Provider_Interface $filters_provider)
    {
        parent::__construct($context);
    }
    /**
     * Get grid-filter of entity attributes action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->get_request()->get_params();
        if ($this->get_request()->is_xml_http_request() && $data) {
            try {
                if ($data['entity'] == Export_Advanced_Pricing::ENTITY_ADVANCED_PRICING) {
                    $data['entity'] = Catalog_Product::ENTITY;
                }
                /** @var \Magento\Framework\View\Result\Layout $resultLayout */
                $result_layout = $this->result_factory->create(Result_Factory::TYPE_LAYOUT);
                /** @var $attrFilterBlock \Magento\ImportExport\Block\Adminhtml\Export\Filter */
                $attr_filter_block = $result_layout->get_layout()->get_block('export.filter');
                /** @var $export \Magento\ImportExport\Model\Export */
                $export = $this->_object_manager->create(\Magento\Import_Export\Model\Export::class);
                $export->set_data($data);
                $attr_filter_block->prepare_collection($this->filters_provider->get_filters($export));
                return $result_layout;
            } catch (\Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
            }
        } else {
            $this->message_manager->add_error_message(__('Please correct the data sent.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        $result_redirect->set_path('adminhtml/*/index');
        return $result_redirect;
    }
}