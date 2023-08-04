<?php

namespace DigitalMarketingFramework\Distributor\Csv\Route;

use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcherInterface;

class CsvRoute extends Route
{
    protected const KEY_FILE_IDENTIFIER = 'fileIdentifier';
    protected const DEFAULT_FILE_IDENTIFIER = 'form-submits.csv';
    protected const KEY_VALUE_DELIMITER = 'delimiter';
    protected const DEFAULT_VALUE_DELIMITER = ';';
    protected const KEY_VALUE_ENCLOSURE = 'enclosure';
    protected const DEFAULT_VALUE_ENCLOSURE = '"';


    protected function getDispatcherKeyword(): string
    {
        return 'csv';
    }

    public static function getDefaultConfiguration(): array
    {
        $config = [
                static::KEY_FILE_IDENTIFIER => static::DEFAULT_FILE_IDENTIFIER,
                static::KEY_VALUE_DELIMITER => static::DEFAULT_VALUE_DELIMITER,
                static::KEY_VALUE_ENCLOSURE => static::DEFAULT_VALUE_ENCLOSURE,
            ]
            + parent::getDefaultConfiguration();
        return $config;
    }

    protected function getDispatcher(): ?DataDispatcherInterface
    {
        try {
            /** @var CsvDataDispatcherInterface */
            $dispatcher = $this->registry->getDataDispatcher($this->getDispatcherKeyword());
            $dispatcher->setFileIdentifier($this->getConfig(static::KEY_FILE_IDENTIFIER));
            $dispatcher->setDelimiter($this->getConfig(static::KEY_VALUE_DELIMITER));
            $dispatcher->setEnclosure($this->getConfig(static::KEY_VALUE_ENCLOSURE));
            return $dispatcher;
        } catch (FileDoesNotExistException $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }
}
