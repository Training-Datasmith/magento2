<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class for the handling of a new data collection for MBI.
 */
class ExportDataHandler implements ExportDataHandlerInterface
{
    /**
     * Subdirectory path for all temporary files.
     */
    private string $subdirectoryPath = 'analytics/';

    /**
     * Filename of archive with collected data.
     */
    private string $archiveName = 'data.tgz';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Archive $archive,
        /**
         * Resource for write data of reports into separate files.
         */
        private readonly ReportWriterInterface $reportWriter,
        /**
         * Resource for encrypting data.
         */
        private readonly Cryptographer $cryptographer,
        /**
         * Resource for registration a new file.
         */
        private readonly FileRecorder $fileRecorder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function prepareExportData(): bool
    {
        try {
            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
            $this->prepareDirectory($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $this->reportWriter->write($tmpDirectory, $this->getTmpFilesDirRelativePath());

            $tmpFilesDirectoryAbsolutePath = $this->validateSource($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $archiveAbsolutePath = $this->prepareFileDirectory($tmpDirectory, $this->getArchiveRelativePath());
            $this->pack(
                $tmpFilesDirectoryAbsolutePath,
                $archiveAbsolutePath
            );

            $this->validateSource($tmpDirectory, $this->getArchiveRelativePath());
            $this->fileRecorder->recordNewFile(
                $this->cryptographer->encode($tmpDirectory->readFile($this->getArchiveRelativePath()))
            );
        } finally {
            if (isset($tmpDirectory)) {
                $tmpDirectory->delete($this->getTmpFilesDirRelativePath());
                $tmpDirectory->delete($this->getArchiveRelativePath());
            }
        }

        return true;
    }

    /**
     * Return relative path to a directory for temporary files with reports data.
     */
    private function getTmpFilesDirRelativePath(): string
    {
        return $this->subdirectoryPath . 'tmp/' . $this->getInstanceIdentifier() . '/';
    }

    /**
     * Return unique identifier for an instance.
     */
    private function getInstanceIdentifier(): string
    {
        return hash('sha256', BP);
    }

    /**
     * Return relative path to a directory for an archive.
     */
    private function getArchiveRelativePath(): string
    {
        return $this->subdirectoryPath . $this->archiveName;
    }

    /**
     * Clean up a directory.
     *
     * @param string $path
     * @return string
     */
    private function prepareDirectory(WriteInterface $directory, $path)
    {
        $directory->delete($path);

        return $directory->getAbsolutePath($path);
    }

    /**
     * Remove a file and a create parent directory a file.
     *
     * @param string $path
     * @return string
     */
    private function prepareFileDirectory(WriteInterface $directory, $path)
    {
        $directory->delete($path);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (dirname($path) !== '.') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $directory->create(dirname($path));
        }

        return $directory->getAbsolutePath($path);
    }

    /**
     * Packing data into an archive.
     *
     * @param string $source
     * @param string $destination
     */
    private function pack($source, $destination): bool
    {
        $this->archive->pack(
            $source,
            $destination,
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            is_dir($source) ?: false
        );

        return true;
    }

    /**
     * Validate that data source exist.
     *
     * Return absolute path in a validated data source.
     *
     * @param string $path
     * @return string
     * @throws LocalizedException If source is not exist.
     */
    private function validateSource(WriteInterface $directory, $path)
    {
        if (!$directory->isExist($path)) {
            throw new LocalizedException(__('The "%1" source doesn\'t exist.', $directory->getAbsolutePath($path)));
        }

        return $directory->getAbsolutePath($path);
    }
}
