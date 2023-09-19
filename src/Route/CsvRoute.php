<?php

namespace DigitalMarketingFramework\Distributor\Csv\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcherInterface;

class CsvRoute extends Route
{
    /*
     * example configurations
     *
     * fileIdentifier:form-submits.csv
     * delimiter:;
     * enclosure:"
     */
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

    protected function getDispatcher(): DataDispatcherInterface
    {
        /** @var CsvDataDispatcherInterface */
        $dispatcher = $this->registry->getDataDispatcher($this->getDispatcherKeyword());
        $dispatcher->setFileIdentifier($this->getConfig(static::KEY_FILE_IDENTIFIER));
        $dispatcher->setDelimiter($this->getConfig(static::KEY_VALUE_DELIMITER));
        $dispatcher->setEnclosure($this->getConfig(static::KEY_VALUE_ENCLOSURE));

        return $dispatcher;
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FILE_IDENTIFIER, new StringSchema(static::DEFAULT_FILE_IDENTIFIER));
        $schema->addProperty(static::KEY_VALUE_DELIMITER, new StringSchema(static::DEFAULT_VALUE_DELIMITER));
        $schema->addProperty(static::KEY_VALUE_ENCLOSURE, new StringSchema(static::DEFAULT_VALUE_ENCLOSURE));

        return $schema;
    }
}
