<?php
namespace fostercommerce\appsearch\jobs;

use fostercommerce\appsearch\Plugin;

use Craft;
use craft\queue\BaseJob;

class DeleteDocuments extends BaseJob
{
    public $engine;
    public $ids = [];

    public function execute($queue)
    {
        Plugin::$instance->appsearchService
                         ->getClient()
                         ->deleteDocuments(
                            $this->engine,
                            $this->ids
                         );
    }

    protected function defaultDescription(): string
    {
        return Craft::t('appsearch',
            sprintf('Deleting %s elements from %s', count($this->ids), $this->engine)
        );
    }
}

