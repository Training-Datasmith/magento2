<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Scheduled_Structure;
/**
 * Class StructureManager
 *
 * Is responsible for managing layout structure items
 * By using this class developer can remove layout entities (block, uiComponent) from scheduled structure
 * Removed entities will not appear at rendered page
 * @api
 * @since 100.2.0
 */
class Structure_Manager
{
    /**
     * Removes scheduled element from structure by name, also removes child elements
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return bool
     * @since 100.2.0
     */
    public function remove_element(Scheduled_Structure $scheduled_structure, Structure $structure, $element_name, $is_child = false)
    {
        $elements_to_remove = array_keys($structure->get_children($element_name));
        $scheduled_structure->unset_element($element_name);
        foreach ($elements_to_remove as $element) {
            $this->remove_element($scheduled_structure, $structure, $element, true);
        }
        if (!$is_child) {
            $structure->unset_element($element_name);
        }
        return true;
    }
}