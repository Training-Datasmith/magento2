<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Search\Config\Structure;

use Magento\Config\Model\Config\Structure_Element_Interface;
/**
 * Element builder interface
 *
 * @api
 */
interface Element_Builder_Interface
{
    /**
     * @param StructureElementInterface $structureElement
     * @return array
     */
    public function build(Structure_Element_Interface $structure_element);
}