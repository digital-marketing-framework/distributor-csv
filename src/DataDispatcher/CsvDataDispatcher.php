<?php

namespace DigitalMarketingFramework\Distributor\Csv\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;
use Exception;

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
            $csvString = $this->fileStorage->getFileContents($this->fileIdentifier) ?? '';
            $outputString = $this->parseCsv($csvString, $data);
            $this->fileStorage->putFileContents($this->fileIdentifier, $outputString);
        } catch (Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    protected function parseCsv(string $csvString, array $data): string
    {
        $headers = [];
        $firstLine = '';
        if ($csvString !== '') {
            $firstLine = substr($csvString, 0, strpos($csvString, PHP_EOL)) . "\n";
            $headers = str_getcsv($firstLine, $this->delimiter, $this->enclosure);
        }

        $newData = [];
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $headers)) {
                $headers[] = $key;
            }
        }

        foreach ($headers as $header) {
            $newData[$header] = empty($data[$header]) ? '' : (string)$data[$header];
        }

        $newHeader = $this->makeCsvLine($headers);
        $csvString = $csvString === '' ? $newHeader : substr_replace($csvString, $newHeader, 0, strlen($firstLine));

        $newData = $this->makeCsvLine($newData);

        return $csvString . $newData;
    }

    /**
     * If a value contains delimiter or an enclousure, a newline, or a linefeed,
     * then surround it with quotes and replace any quotes inside it with two quotes
     *
     * @param array<string> $values
     */
    protected function makeCsvLine(array $values): string
    {
        // iterate through the array ele by ele.
        foreach ($values as $key => $value) {
            // check for presence of special char.
            if (str_contains($value, $this->delimiter)
                || str_contains($value, $this->enclosure)
                || str_contains($value, "\n")
                || str_contains($value, "\r")) {
                $values[$key] = $this->enclosure . str_replace($this->enclosure, $this->enclosure . $this->enclosure, $value) . $this->enclosure;
            }
        }

        // now create the CSV line by joining with delimiter
        return implode($this->delimiter, $values) . PHP_EOL;
    }
}
