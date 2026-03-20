<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;

/**
 * Bundle option multi select type renderer
 *
 * @api
 * @since 100.0.2
 */
class Multi extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::catalog/product/view/type/bundle/option/multi.phtml';
    /**
     * @inheritdoc
     * @since 100.2.0
     */
    protected function assign_selection(\Magento\Bundle\Model\Option $option, $selection_id)
    {
        if (is_array($selection_id)) {
            foreach ($selection_id as $id) {
                if ($id && $option->get_selection_by_id($id)) {
                    $this->_selected_options[] = $id;
                }
            }
        } else {
            parent::assign_selection($option, $selection_id);
        }
    }
}