<?php

namespace DigitalMarketingFramework\Distributor\Csv\DataDispatcher;

use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;

interface CsvDataDispatcherInterface extends DataDispatcherInterface
{
    public function setFileIdentifier(string $fileIdentifier): void;

    public function setDelimiter(string $delimiter): void;

    public function setEnclosure(string $enclosure): void;
}
