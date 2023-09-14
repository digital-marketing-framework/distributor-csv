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
            echo 'START2';
            echo $outputString;
            $this->fileStorage->putFileContents($this->fileIdentifier, $outputString);
        }
        catch (\Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array<mixed> $data
     */
    protected function parseCsv(string $csvString, array $data): string
    {
        $headers = [];
        $firstLine = '';
        if (!empty($csvString)) {
            $firstLine = substr($csvString, 0, strpos($csvString, PHP_EOL)). "\n";
            $headers = str_getcsv($firstLine, $this->delimiter, $this->enclosure);
        }

        $newData = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $headers)) {
                $headers[] = $key;
            }
        }
        foreach ($headers as $header) {
            $newData[$header] = !empty($data[$header]) ? $data[$header] : '';
        }
        $newHeader = $this->makeCsvLine($headers);
        if (!empty($csvString)) {
            $csvString = str_replace($firstLine, $newHeader, $csvString);
        } else {
            $csvString = $newHeader;
        }
        $newData = $this->makeCsvLine($newData);
        $csvString .= $newData;
        return $csvString;
    }

    /**
     * If a value contains delimiter or an enclousure, a newline, or a linefeed,
     * then surround it with quotes and replace any quotes inside it with two quotes
     * @param array<mixed> $values
     */
    protected function makeCsvLine(array $values): string
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

                $values[$key] = $this->enclosure . str_replace([$this->enclosure], $this->enclosure.$this->enclosure, $value) . $this->enclosure;
            }
        }

        // now create the CSV line by joining with delimiter
        return implode($this->delimiter, $values) . PHP_EOL;
    }
}
