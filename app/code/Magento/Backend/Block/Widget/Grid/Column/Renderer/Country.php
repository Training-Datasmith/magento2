<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Country column type renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Country extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $locale_lists;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Locale\Lists_Interface $locale_lists, array $data = [])
    {
        parent::__construct($context, $data);
        $this->locale_lists = $locale_lists;
    }
    /**
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        if ($data = $row->get_data($this->get_column()->get_index())) {
            $name = $this->locale_lists->get_country_translation($data);
            if (empty($name)) {
                $name = $this->escape_html($data);
            }
            return $name;
        }
        return null;
    }
}