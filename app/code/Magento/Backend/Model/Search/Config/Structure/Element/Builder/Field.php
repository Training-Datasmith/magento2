<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search\Config\Structure\Element\Builder;

use Magento\Backend\Model\Search\Config\Structure\Element_Builder_Interface;
use Magento\Config\Model\Config\Structure_Element_Interface;
class Field implements Element_Builder_Interface
{
    /**
     * @inheritdoc
     */
    public function build(Structure_Element_Interface $structure_element)
    {
        $element_path_parts = explode('/', $structure_element->get_path());
        return ['section' => $element_path_parts[0], 'group' => $element_path_parts[1], 'field' => $structure_element->get_id()];
    }
}