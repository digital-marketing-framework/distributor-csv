<?php

use DigitalMarketingFramework\Core\FileStorage\FileStorageInterface;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\DistributorRegistryTestTrait;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcher;
use DigitalMarketingFramework\Distributor\Csv\DistributorCsvInitialization;
use DigitalMarketingFramework\Distributor\Csv\Route\CsvRoute;
use PHPUnit\Framework\TestCase;

class CsvDataDispatcherTest extends TestCase
{
    use DistributorRegistryTestTrait;

    protected CsvRoute $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initRegistry();
        $initialization = new DistributorCsvInitialization();
        $initialization->init(RegistryDomain::CORE, $this->registry);
        $initialization->init(RegistryDomain::DISTRIBUTOR, $this->registry);
    }

    /**
     * @dataProvider csvDataProvider
     * @param array<mixed> $data
     */
    public function testSendMethod(string|null $existingCsvContent = '', array $data = [], string $expectedCsvContent = '')
    {
        $csvDataDispatcher = new CsvDataDispatcher('csv', $this->registry);

        // Set up some test data
        $fileIdentifier = 'test.csv';
        $delimiter = ';';
        $enclosure = '"';
        // Set the properties of the CsvDataDispatcher instance
        $csvDataDispatcher->setFileIdentifier($fileIdentifier);
        $csvDataDispatcher->setDelimiter($delimiter);
        $csvDataDispatcher->setEnclosure($enclosure);

        // Simulate file existence and content
        $fileStorageMock = $this->getMockBuilder(FileStorageInterface::class)->getMock();
        $fileStorageMock->method('getFileContents')->willReturn($existingCsvContent);
        $fileStorageMock->expects(self::once())->method('putFileContents')->with($fileIdentifier, $expectedCsvContent);

        // Set the file storage for the CsvDataDispatcher instance
        $csvDataDispatcher->setFileStorage($fileStorageMock);

        // Call the send method to update the CSV data
        $csvDataDispatcher->send($data);
    }

    public function csvDataProvider()
    {
        return [
            'Test when file doesnt exist, expect new CSV file with headers and data' =>
                [null, ['Name' => 'John', 'Last Name' => 'Doe', 'Email' => 'johndoe@example.com'], "Name;Last Name;Email\nJohn;Doe;johndoe@example.com\n"],
            'Test when file is empty, expect new headers and data' =>
                ['', ['Name' => 'John', 'Last Name' => 'Doe', 'Email' => 'johndoe@example.com'], "Name;Last Name;Email\nJohn;Doe;johndoe@example.com\n"],

            'If a value contains line-break then surround it with quotes and replace linebreaks with PHP_EOL' =>
                ['', ['Name' => 'John', 'Last Name' => 'Doe', 'Email' => "john\ndoe@example.com"], "Name;Last Name;Email\nJohn;Doe;\"john\ndoe@example.com\"\n"],

            'If a value contains delimiter or enclosure then surround it with quotes' =>
                ['', ['Name' => 'Jo;hn', 'Last Name' => '"Doe"', 'Email' => '"john;doe@example.com"'], "Name;Last Name;Email\n\"Jo;hn\";\"\"\"Doe\"\"\";\"\"\"john;doe@example.com\"\"\"\n"],

            'Test when file exists, expect data appended to existing CSV' =>
                ["Name;Email;Age\nAlice;alice@example.com;25\nBob;bob@example.com;32\n", ['Name' => 'John', 'Last Name' => 'Doe', 'Email' => 'johndoe@example.com'], "Name;Email;Age;Last Name\nAlice;alice@example.com;25\nBob;bob@example.com;32\nJohn;johndoe@example.com;;Doe\n"],

            'Test when file exists, and existing CSV contains double quotes' =>
                ["Name;Email;Age\n\"\"\"Alice\"\"\";\"\"\"alice@example.com\"\"\";\"\"\"25\"\"\"\nBob;bob@example.com;32\n", ['Name' => 'John', 'Last Name' => 'Doe', 'Email' => 'johndoe@example.com'], "Name;Email;Age;Last Name\n\"\"\"Alice\"\"\";\"\"\"alice@example.com\"\"\";\"\"\"25\"\"\"\nBob;bob@example.com;32\nJohn;johndoe@example.com;;Doe\n"],
        ];
    }
}
