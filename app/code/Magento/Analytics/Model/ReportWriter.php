<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Model;

use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;

/**
 * Writes reports in files in csv format
 */
class ReportWriter implements ReportWriterInterface
{
    /**
     * File name for error reporting file in archive
     */
    private string $errorsFileName = 'errors.csv';

    /**
     * ReportWriter constructor.
     */
    public function __construct(private readonly ConfigInterface $config, private readonly ReportValidator $reportValidator, private readonly ProviderFactory $providerFactory)
    {
    }

    /**
     * @inheritdoc
     */
    public function write(WriteInterface $directory, $path): bool
    {
        $errorsList = [];
        foreach ($this->config->get() as $file) {
            $provider = reset($file['providers']);
            if (isset($provider['parameters']['name'])) {
                $error = $this->reportValidator->validate($provider['parameters']['name']);
                if ($error) {
                    $errorsList[] = $error;
                    continue;
                }
            }
            $this->prepareData($provider, $directory, $path);
        }
        if ($errorsList) {
            $errorStream = $directory->openFile($path . $this->errorsFileName, 'w+');
            foreach ($errorsList as $error) {
                $errorStream->lock();
                $errorStream->writeCsv($error);
                $errorStream->unlock();
            }
            $errorStream->close();
        }

        return true;
    }

    /**
     * Prepare report data
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function prepareData(array $provider, WriteInterface $directory, string $path): void
    {
        /** @var  $providerObject */
        $providerObject = $this->providerFactory->create($provider['class']);
        $fileName = $provider['parameters'] ? $provider['parameters']['name'] : $provider['name'];
        $fileFullPath = $path . $fileName . '.csv';

        $stream = $directory->openFile($fileFullPath, 'w+');
        $stream->lock();

        if ($providerObject instanceof \Magento\Analytics\ReportXml\BatchReportProviderInterface) {
            $writeHeaders = true;
            $fileData = $providerObject->getBatchReport(...array_values($provider['parameters']));
            do {
                $this->doWrite($fileData, $stream, $writeHeaders);
                $writeHeaders = false;
                $fileData = $providerObject->getBatchReport(...array_values($provider['parameters']));
                $fileData->rewind();
            } while ($fileData->valid());
        } else {
            $fileData = $providerObject->getReport(...array_values($provider['parameters']));
            $this->doWrite($fileData, $stream);
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * Write data to file
     */
    private function doWrite(\Traversable $fileData, FileWriteInterface $stream, bool $writeHeaders = true): void
    {
        foreach ($fileData as $row) {
            if ($writeHeaders) {
                $headers = array_keys($row);
                $stream->writeCsv($headers);
                $writeHeaders = false;
            }
            $stream->writeCsv($this->prepareRow($row));
        }
    }

    /**
     * Replace wrong symbols in row
     *
     * Strip backslashes before double quotes so they will be properly escaped in the generated csv
     *
     * @see fputcsv()
     */
    private function prepareRow(array $row): array
    {
        return preg_replace('/\\\+(?=\")/', '', $row);
    }
}
