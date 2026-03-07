<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Config\Argument;

/**
 * Convert node to array
 *
 * @api
 */
interface ParserInterface
{
    /**
     * Parse xml node to array
     *
     * @param array $data
     * @param \DOMNode $node
     * @return array
     */
    public function parse(array $data, \DOMNode $node);
}
