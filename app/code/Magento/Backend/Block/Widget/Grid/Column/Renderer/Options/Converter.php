<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Options;

/**
 * @api
 * @since 100.0.2
 */
class Converter
{
    /**
     * Convert data from tree format to flat format
     *
     * @param array $treeData
     * @return array
     */
    public function to_flat_array($tree_data)
    {
        $options = [];
        if (is_array($tree_data)) {
            foreach ($tree_data as $item) {
                if (isset($item['value']) && isset($item['label'])) {
                    $options[$item['value']] = $item['label'];
                }
            }
        }
        return $options;
    }
    /**
     * Convert data from flat format to tree format
     *
     * @param array $flatData
     * @return array
     */
    public function to_tree_array($flat_data)
    {
        $options = [];
        if (is_array($flat_data)) {
            foreach ($flat_data as $key => $item) {
                $options[] = ['value' => $key, 'label' => $item];
            }
        }
        return $options;
    }
}