<?php
namespace fostercommerce\appsearch\variables;

use fostercommerce\appsearch\Plugin;

use Craft;

class AppsearchVariable
{
    public function publicSearchKey() : string
    {
        return Plugin::$instance->getSettings()->publicSearchKey;
    }
}
