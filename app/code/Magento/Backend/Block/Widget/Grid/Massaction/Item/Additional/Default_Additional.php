<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action default
 */
class Default_Additional extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\Additional_Interface
{
    /**
     * @inheritDoc
     */
    public function create_from_configuration(array $configuration)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create();
        foreach ($configuration as $item_id => $item) {
            $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
            $form->add_field($item_id, $item['type'], $item);
        }
        $this->set_form($form);
        return $this;
    }
}