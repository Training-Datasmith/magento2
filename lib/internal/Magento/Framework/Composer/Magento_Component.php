<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

/**
 * Magento component.
 */
class Magento_Component
{
    /**
     * Get matched Magento component or empty array, if it's not a Magento component
     *
     * @param string $key
     * @return string[] ['type' => '<type>', 'area' => '<area>', 'name' => '<name>']
     *             Ex.: ['type' => 'module', 'name' => 'catalog']
     *                  ['type' => 'theme', 'area' => 'frontend', 'name' => 'blank']
     */
    public static function match_magento_component($key)
    {
        $type_pattern = 'module|theme|language|framework';
        $area_pattern = 'frontend|adminhtml';
        $name_pattern = '[a-z0-9_-]+';
        $regex = '/^magento\/(?P<type>' . $type_pattern . ')(?:-(?P<area>' . $area_pattern . '))?(?:-(?P<name>' . $name_pattern . '))?$/';
        if (preg_match($regex, $key, $matches)) {
            return $matches;
        }
        return [];
    }
}