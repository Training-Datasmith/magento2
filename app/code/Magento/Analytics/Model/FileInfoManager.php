<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;

/**
 * Manage saving and loading FileInfo object.
 */
class FileInfoManager
{
    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * Flag code for a stored FileInfo object.
     */
    private string $flagCode = 'analytics_file_info';

    /**
     * Parameters which have to be saved into encoded form.
     */
    private array $encodedParameters = [
        'initializationVector',
    ];

    public function __construct(
        private readonly FlagManager $flagManager,
        FileInfoFactory $fileInfoFactory
    ) {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * Save FileInfo object.
     *
     * @throws LocalizedException
     */
    public function save(FileInfo $fileInfo): bool
    {
        $parameters = [];
        $parameters['initializationVector'] = $fileInfo->getInitializationVector();
        $parameters['path'] = $fileInfo->getPath();

        $emptyParameters = array_diff($parameters, array_filter($parameters));
        if ($emptyParameters) {
            throw new LocalizedException(
                __('These arguments can\'t be empty "%1"', implode(', ', array_keys($emptyParameters)))
            );
        }

        foreach ($this->encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = $this->encodeValue($parameters[$encodedParameter]);
        }

        $this->flagManager->saveFlag($this->flagCode, $parameters);

        return true;
    }

    /**
     * Load FileInfo object.
     *
     * @return FileInfo
     */
    public function load()
    {
        $parameters = $this->flagManager->getFlagData($this->flagCode) ?: [];

        $encodedParameters = array_intersect($this->encodedParameters, array_keys($parameters));
        foreach ($encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = $this->decodeValue($parameters[$encodedParameter]);
        }

        return $this->fileInfoFactory->create($parameters);
    }

    /**
     * Encode value.
     */
    private function encodeValue(string $value): string
    {
        return base64_encode($value);
    }

    /**
     * Decode value.
     *
     * @param string $value
     */
    private function decodeValue($value): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode($value);
    }
}
