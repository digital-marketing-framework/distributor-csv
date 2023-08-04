<?php

namespace DigitalMarketingFramework\Distributor\Csv\Exception;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;

class FolderDoesNotExistException extends DigitalMarketingFrameworkException
{
    public function __construct($url)
    {
        parent::__construct(sprintf('Folder %s does not exist and needs', $url), 1683732162);
    }
}
