<?php

use DigitalMarketingFramework\Core\FileStorage\FileStorageInterface;
use PHPUnit\Framework\TestCase;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcher;

class CsvDataDispatcherTest extends TestCase
{
    public function testSendMethod()
    {
        $csvDataDispatcher = new CsvDataDispatcher(
            'csv'
        );

        // Set up some test data
        $fileIdentifier = 'test.csv';
        $delimiter = ';';
        $enclosure = '"';
        $data = [
            'Name' => 'John',
            'Last Name' => 'Doe',
            'Email' => 'johndoe@example.com',
        ];

        // Set the properties of the CsvDataDispatcher instance
        $csvDataDispatcher->setFileIdentifier($fileIdentifier);
        $csvDataDispatcher->setDelimiter($delimiter);
        $csvDataDispatcher->setEnclosure($enclosure);

        // Simulate reading an existing CSV file
        $existingCsvFileContent = "Name;Email;Age\nAlice;alice@example.com;25\nBob;bob@example.com;32\n";
        $fileStorageMock = $this->getMockBuilder(FileStorageInterface::class)->getMock();
        $fileStorageMock->method('getFileContents')->willReturn($existingCsvFileContent);

        // Expect new headers to be appended to original headers
        $expectedUpdatedCsvContent = "Name;Email;Age;Last Name\nAlice;alice@example.com;25;\nBob;bob@example.com;32;\nJohn;johndoe@example.com;;Doe\n";
        $fileStorageMock->expects($this->once())->method('putFileContents')->with($fileIdentifier, $expectedUpdatedCsvContent);

        // Set the file storage for the CsvDataDispatcher instance
        $csvDataDispatcher->setFileStorage($fileStorageMock);

        // Call the send method to update the CSV data
        $csvDataDispatcher->send($data);
    }
}
