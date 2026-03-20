<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Grid column filter block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Abstract_Filter extends \Magento\Backend\Block\Abstract_Block implements \Magento\Backend\Block\Widget\Grid\Column\Filter\Filter_Interface
{
    /**
     * Column related to filter
     *
     * @var \Magento\Backend\Block\Widget\Grid\Column
     */
    protected $_column;
    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resource_helper;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, array $data = [])
    {
        $this->_resource_helper = $resource_helper;
        parent::__construct($context, $data);
    }
    /**
     * Set column related to filter
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
     */
    public function set_column($column)
    {
        $this->_column = $column;
        return $this;
    }
    /**
     * Retrieve column related to filter
     *
     * @return \Magento\Backend\Block\Widget\Grid\Column
     */
    public function get_column()
    {
        return $this->_column;
    }
    /**
     * Retrieve html name of filter
     *
     * @return string
     */
    protected function _get_html_name()
    {
        return $this->escape_html($this->get_column()->get_id());
    }
    /**
     * Retrieve html id of filter
     *
     * @return string
     */
    protected function _get_html_id()
    {
        return $this->escape_html($this->get_column()->get_html_id());
    }
    /**
     * Retrieve escaped value
     *
     * @param mixed $index
     * @return string
     */
    public function get_escaped_value($index = null)
    {
        return $this->escape_html((string) $this->get_value($index));
    }
    /**
     * Retrieve condition
     *
     * @return array
     */
    public function get_condition()
    {
        $like_expression = $this->_resource_helper->add_like_escape($this->get_value(), ['position' => 'any']);
        return ['like' => $like_expression];
    }
    /**
     * Retrieve filter html
     *
     * @return string
     */
    public function get_html()
    {
        return '';
    }
}