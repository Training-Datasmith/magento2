<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Fedex packaging source implementation
 */

namespace Magento\Fedex\Model\Source;

class Packaging extends \Magento\Fedex\Model\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'packaging';
}
