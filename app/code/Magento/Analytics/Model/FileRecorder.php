<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class for the handling of registration a new file for MBI.
 */
class FileRecorder
{
    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * Subdirectory path for an encoded file.
     */
    private string $fileSubdirectoryPath = 'analytics/';

    /**
     * File name of an encoded file.
     */
    private string $encodedFileName = 'data.tgz';

    public function __construct(
        /**
         * Resource for managing FileInfo object.
         */
        private readonly FileInfoManager $fileInfoManager,
        FileInfoFactory $fileInfoFactory,
        private readonly Filesystem $filesystem
    ) {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * Save new encrypted file, register it and remove old registered file.
     */
    public function recordNewFile(EncodedContext $encodedContext): bool
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $fileRelativePath = $this->getFileRelativePath();
        $directory->writeFile($fileRelativePath, $encodedContext->getContent());

        $fileInfo = $this->fileInfoManager->load();
        $this->registerFile($encodedContext, $fileRelativePath);
        $this->removeOldFile($fileInfo, $directory);

        return true;
    }

    /**
     * Return relative path to encoded file.
     */
    private function getFileRelativePath(): string
    {
        return $this->fileSubdirectoryPath . hash('sha256', time())
            . '/' . $this->encodedFileName;
    }

    /**
     * Register encoded file.
     *
     * @param string $fileRelativePath
     */
    private function registerFile(EncodedContext $encodedContext, $fileRelativePath): bool
    {
        $newFileInfo = $this->fileInfoFactory->create(
            [
                'path' => $fileRelativePath,
                'initializationVector' => $encodedContext->getInitializationVector(),
            ]
        );
        $this->fileInfoManager->save($newFileInfo);

        return true;
    }

    /**
     * Remove previously registered file.
     */
    private function removeOldFile(FileInfo $fileInfo, WriteInterface $directory): bool
    {
        if (!$fileInfo->getPath()) {
            return true;
        }

        $directory->delete($fileInfo->getPath());
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $directoryName = dirname($fileInfo->getPath());
        if ($directoryName !== '.') {
            $directory->delete($directoryName);
        }

        return true;
    }
}
