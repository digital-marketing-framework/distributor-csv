<?php

namespace DigitalMarketingFramework\Distributor\Csv\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Core\FileStorage\FileStorageInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use function Symfony\Component\String\s;

class CsvDataDispatcher extends DataDispatcher implements CsvDataDispatcherInterface, FileStorageAwareInterface
{
    use FileStorageAwareTrait;

    private string $fileIdentifier;
    private string $delimiter;
    private string $enclosure;

    public function setFileIdentifier(string $fileIdentifier): void
    {
        $this->fileIdentifier = $fileIdentifier;
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function setEnclosure(string $enclosure): void
    {
        $this->enclosure = $enclosure;
    }

    public function send(array $data): void
    {
        /** @var FileStorageInterface $storage */
        $storage = $this->getFileStorage();
        if (!$storage->fileExists($this->fileIdentifier)) {
            $storage->createFile($this->fileIdentifier);
        }
        $filePath = $storage->getFileInfo($this->fileIdentifier, PATHINFO_ALL);
        try {
            if ($csvFile = fopen($filePath, 'a')) {
                if (filesize($filePath) == 0) {
                    // Excel needs BOM to understand utf-8 encoding
                    fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));
                    // Add Header row
                    fputcsv($csvFile, array_keys($data), $this->delimiter, $this->enclosure);
                }
                // Add content row
                fputcsv($csvFile, array_values($data), $this->delimiter, $this->enclosure);
                fclose($csvFile);
            } else {
                if (!is_writable($filePath)) {
                    $this->logger->error('CSV file is not writeable on: ' . $filePath);
                }
                $this->logger->error('Error writing CSV file on: ' . error_get_last());
            }
        }
        catch (\Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
