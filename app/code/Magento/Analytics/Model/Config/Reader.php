<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * Composite reader for config.
 */
class Reader implements ReaderInterface
{
    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(private readonly Mapper $mapper, private $readers = [])
    {
    }

    /**
     * Read configuration scope.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $data = [];
        foreach ($this->readers as $reader) {
            $data = array_merge_recursive($data, $reader->read($scope));
        }

        return $this->mapper->execute($data);
    }
}
