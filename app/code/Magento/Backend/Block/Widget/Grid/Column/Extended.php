<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $this->_renderer_types['options'] = \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Extended::class;
        $this->_filter_types['options'] = \Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended::class;
        $this->_renderer_types['select'] = \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select\Extended::class;
        $this->_renderer_types['checkbox'] = \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes\Extended::class;
        $this->_renderer_types['radio'] = \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio\Extended::class;
        parent::__construct($context, $data);
    }
}