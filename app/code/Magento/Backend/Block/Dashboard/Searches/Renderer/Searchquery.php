<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Searches\Renderer;

/**
 * Dashboard search query column renderer
 * @api
 * @since 100.0.2
 */
class Searchquery extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * String helper
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string_helper;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Stdlib\String_Utils $string_helper, array $data = [])
    {
        $this->string_helper = $string_helper;
        parent::__construct($context, $data);
    }
    /**
     * Renders a column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $value = $row->get_data($this->get_column()->get_index());
        if ($this->string_helper->strlen($value) > 30) {
            $value = '<span title="' . $this->escape_html($value) . '">' . $this->escape_html($this->filter_manager->truncate($value, ['length' => 30])) . '</span>';
        } else {
            $value = $this->escape_html($value);
        }
        return $value;
    }
}