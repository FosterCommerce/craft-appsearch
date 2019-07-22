<?php
namespace fostercommerce\appsearch\jobs;

use fostercommerce\appsearch\Plugin;

use Craft;
use craft\queue\BaseJob;

class IndexDocuments extends BaseJob
{
    public $engine;
    public $documents = [];

    public function execute($queue)
    {
        $res = Plugin::$instance->appsearchService
                         ->getClient()
                         ->indexDocuments(
                            $this->engine,
                            $this->documents
                         );
        // TODO Add errors
    }

    protected function defaultDescription(): string
    {
        return Craft::t('appsearch',
            sprintf('Indexing %s elements to %s', count($this->documents), $this->engine)
        );
    }
}
