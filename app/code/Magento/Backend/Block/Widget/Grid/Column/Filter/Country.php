<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Country grid filter
 */
class Country extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_directories_factory;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $directoriesFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, \Magento\Directory\Model\Resource_Model\Country\Collection_Factory $directories_factory, array $data = [])
    {
        $this->_directories_factory = $directories_factory;
        parent::__construct($context, $resource_helper, $data);
    }
    /**
     * @return array
     */
    protected function _get_options()
    {
        $options = $this->_directories_factory->create()->load()->to_option_array(false);
        array_unshift($options, ['value' => '', 'label' => __('All Countries')]);
        return $options;
    }
}