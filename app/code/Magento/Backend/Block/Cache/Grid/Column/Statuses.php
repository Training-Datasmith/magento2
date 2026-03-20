<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Cache\Grid\Column;

/**
 * @api
 * @since 100.0.2
 */
class Statuses extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cache_type_list;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\App\Cache\Type_List_Interface $cache_type_list, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_cache_type_list = $cache_type_list;
    }
    /**
     * Add to column decorated status
     *
     * @return array
     */
    public function get_frame_callback()
    {
        return [$this, 'decorateStatus'];
    }
    /**
     * Decorate status column values
     *
     * @param string $value
     * @param  \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorate_status($value, $row, $column, $is_export)
    {
        $invalided_types = $this->_cache_type_list->get_invalidated();
        if (isset($invalided_types[$row->get_id()])) {
            $cell = '<span class="grid-severity-minor"><span>' . __('Invalidated') . '</span></span>';
        } else if ($row->get_status()) {
            $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>' . $value . '</span></span>';
        }
        return $cell;
    }
}