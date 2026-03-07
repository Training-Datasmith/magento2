<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AsyncConfigPublisher implements \Magento\AsyncConfig\Api\AsyncConfigPublisherInterface
{
    /**
     * @var AsyncConfigMessageInterfaceFactory
     */
    private $asyncConfigFactory;

    public function __construct(
        AsyncConfigMessageInterfaceFactory $asyncConfigFactory,
        private readonly PublisherInterface $messagePublisher,
        private readonly Json $serializer,
        private readonly \Magento\Framework\Filesystem\DirectoryList $dir,
        private readonly File $file
    ) {
        $this->asyncConfigFactory = $asyncConfigFactory;
    }

    /**
     * @inheritDoc
     */
    public function saveConfigData(array $configData): void
    {
        $asyncConfig = $this->asyncConfigFactory->create();
        $this->saveImages($configData);
        $asyncConfig->setConfigData($this->serializer->serialize($configData));
        $this->messagePublisher->publish('async_config.saveConfig', $asyncConfig);
    }

    /**
     * Save Images to temporary Path
     *
     * @throws FileSystemException
     */
    private function saveImages(array &$configData): void
    {
        if (isset($configData['groups']['placeholder'])) {
            $this->changeImagePath($configData['groups']['placeholder']['fields']);
        } elseif (isset($configData['groups']['identity'])) {
            $this->changeImagePath($configData['groups']['identity']['fields']);
        }
    }

    /**
     * Change Placeholder Data path if exists
     *
     * @throws FileSystemException
     */
    private function changeImagePath(array &$fields): void
    {
        foreach ($fields as &$data) {
            if (!empty($data['value']['tmp_name'])) {
                $newPath =
                    $this->dir->getPath(DirectoryList::MEDIA) . '/' .
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    pathinfo((string) $data['value']['tmp_name'])['filename'];
                $this->file->mv(
                    $data['value']['tmp_name'],
                    $newPath
                );
                $data['value']['tmp_name'] = $newPath;
            }
        }
    }
}
