<?php

namespace DigitalMarketingFramework\Distributor\Csv;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;
use DigitalMarketingFramework\Distributor\Csv\DataDispatcher\CsvDataDispatcher;
use DigitalMarketingFramework\Distributor\Csv\Route\CsvOutboundRoute;

class DistributorCsvInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::DISTRIBUTOR => [
            DataDispatcherInterface::class => [
                CsvDataDispatcher::class,
            ],
            OutboundRouteInterface::class => [
                CsvOutboundRoute::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct(string $packageAlias = '')
    {
        parent::__construct('distributor-csv', '1.0.0', $packageAlias);
    }
}
