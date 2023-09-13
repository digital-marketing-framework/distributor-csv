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
        try {
            $csvString = $this->fileStorage->getFileContents($this->fileIdentifier);

            $outputString = $this->parseCsv($csvString, $data);

            $this->fileStorage->putFileContents($this->fileIdentifier, $outputString);
        }
        catch (\Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $csvString
     * @param array<mixed> $data
     * @return string
     */
    protected function parseCsv(string $csvString, array $data): string {
        // Parse the CSV string into an array
        $csvArray = [];
        $lines = explode(PHP_EOL, $csvString);
        $header = str_getcsv(array_shift($lines), $this->delimiter, $this->enclosure);
        foreach ($lines as $line) {
            $values = str_getcsv($line, $this->delimiter);
            if ($values[0]) {
                $csvArray[] = array_combine($header, $values);
            }
        }
        $finalArray = array_merge($csvArray, [$data]);

        // Get the headers in the order they appear
        $headers = [];
        foreach ($finalArray as $item) {
            $headers = array_merge($headers, array_keys($item));
        }
        $headers = array_unique($headers);

        // Prepare the CSV header row
        $headerRow = implode($this->delimiter, $headers);

        // Initialize a string variable to store the result
        $outputString = $headerRow . PHP_EOL;

        // Prepare and append the data rows
        foreach ($finalArray as $item) {
            $rowData = [];
            foreach ($headers as $header) {
                $rowData[] = $item[$header] ?? '';
            }
            $outputString .= implode($this->delimiter, $rowData) . PHP_EOL;
        }
        return $outputString;
    }
}
