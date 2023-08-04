<?php

namespace DigitalMarketingFramework\Distributor\Csv;

use DigitalMarketingFramework\Core\PluginInitialization;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcher;
use DigitalMarketingFramework\Distributor\Csv\Route\CsvRoute;

class DistributorPluginInitialization extends PluginInitialization
{
    protected const PLUGINS = [
        DataDispatcherInterface::class => [
            CsvDataDispatcher::class,
        ],
        RouteInterface::class => [
            CsvRoute::class,
        ],
    ];
}
