<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Config\Menu;

use Dom_Element;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Menu configuration files handler
 * @api
 * @since 100.0.2
 */
class Dom extends \Magento\Framework\Config\Dom
{
    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @return DOMElement|null
     * @throws LocalizedException an exception is possible if original document contains
     * multiple fixed nodes
     */
    protected function _get_matched_node($node_path)
    {
        if (!$node_path || !preg_match('/^\/config(\/menu)?$/i', $node_path)) {
            return null;
        }
        return parent::_get_matched_node($node_path);
    }
}