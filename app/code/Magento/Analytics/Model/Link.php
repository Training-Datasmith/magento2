<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterface;

/**
 * Represents link with collected data and initialized vector for decryption.
 */
class Link implements LinkInterface
{
    /**
     * @param string $url
     * @param string $initializationVector
     */
    public function __construct(private $url, private $initializationVector)
    {
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
