<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Analytics\Api\LinkProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides link to file with collected report data.
 */
class LinkProvider implements LinkProviderInterface
{
    /**
     * @var LinkInterfaceFactory
     */
    private $linkFactory;

    public function __construct(
        LinkInterfaceFactory $linkFactory,
        private readonly FileInfoManager $fileInfoManager,
        private readonly StoreManagerInterface $storeManager
    ) {
        $this->linkFactory = $linkFactory;
    }

    /**
     * Returns base url to file according to store configuration
     */
    private function getBaseUrl(FileInfo $fileInfo): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $fileInfo->getPath();
    }

    /**
     * Verify is requested file ready
     */
    private function isFileReady(FileInfo $fileInfo): bool
    {
        return $fileInfo->getPath() && $fileInfo->getInitializationVector();
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $fileInfo = $this->fileInfoManager->load();
        if (!$this->isFileReady($fileInfo)) {
            throw new NoSuchEntityException(__('File is not ready yet.'));
        }
        return $this->linkFactory->create(
            [
                'url' => $this->getBaseUrl($fileInfo),
                'initializationVector' => base64_encode($fileInfo->getInitializationVector()),
            ]
        );
    }
}
