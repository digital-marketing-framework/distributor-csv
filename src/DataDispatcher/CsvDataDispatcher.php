<?php

namespace DigitalMarketingFramework\Distributor\Csv\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;

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
            if (!$csvString) {
                $csvString = '';
            }
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
        $headerRow = $this->makeCsvLine($headers);

        // Initialize a string variable to store the result
        $outputString = $headerRow;

        // Prepare and append the data rows
        foreach ($finalArray as $item) {
            $rowData = [];
            foreach ($headers as $header) {
                $rowData[] = $item[$header] ?? '';
            }
            $outputString .= $this->makeCsvLine($rowData);
        }
        return $outputString;
    }

    /**
     * If a value contains delimiter or an enclousure, a newline, or a linefeed,
     * then surround it with quotes and replace any quotes inside it with two quotes
     * @param array<mixed> $values
     */
    protected function makeCsvLine(array $values)
    {
        // iterate through the array ele by ele.
        foreach($values as $key => $value)
        {
            // check for presence of special char.
            if ((strpos($value, $this->delimiter)  !== false) ||
                (strpos($value, $this->enclosure)  !== false) ||
                (strpos($value, "\n") !== false) ||
                (strpos($value, "\r") !== false))
            {
                $values[$key] = $this->enclosure . str_replace($this->enclosure, $this->enclosure.$this->enclosure, $value) . $this->enclosure;
            }
        }

        // now create the CSV line by joining with delimiter
        return implode($this->delimiter, $values) . PHP_EOL;
    }
}
