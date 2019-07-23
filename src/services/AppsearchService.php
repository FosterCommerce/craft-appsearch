<?php
namespace fostercommerce\appsearch\services;

use fostercommerce\appsearch\Plugin;

use Craft;
use craft\base\Component;
use fostercommerce\appsearch\models\EngineMapping;
use fostercommerce\appsearch\jobs\IndexDocuments;
use fostercommerce\appsearch\jobs\DeleteDocuments;
use fostercommerce\appsearch\ElementSerializer;
use Elastic\AppSearch\Client\ClientBuilder;

class AppsearchService extends Component
{
    private $mappings = [];
    private $client = null;

    public function getClient()
    {
        if (!$this->client) {
            $settings = Plugin::$instance->getSettings();
            $clientBuilder = ClientBuilder::create(
                $settings->apiEndpoint,
                $settings->privateApiKey
            );

            $this->client = $clientBuilder->build();
        }

        return $this->client;
    }

    public function getMappings()
    {
        if (!count($this->mappings)) {
            $mappingsConfig = Plugin::$instance->getSettings()->engines;
            foreach ($mappingsConfig as $mappingConfig) {
                $this->mappings[] = new EngineMapping($mappingConfig);
            }
        }

        return $this->mappings;
    }

    public function indexElements($elements)
    {
        foreach ($this->getMappings() as $mapping) {
            $this->indexElementsForMapping($mapping, $elements);
        }
    }

    public function indexElementsForMapping($mapping, $elements)
    {
        $toIndex = null;
        $toDelete = [];
        foreach ($elements as $element) {
            // Add support for 3.2 drafts and revisions
            if (method_exists($element, 'getIsDraft') && method_exists($element, 'getIsRevision')) {
                if ($element->getIsDraft() || $element->getIsRevision()) {
                    continue;
                }
            }

            if ($mapping->elementType === get_class($element)) {
                if ($mapping->canIndexElement($element)) {
                    $toIndex = array_merge($toIndex ?: [], $this->transformElement($mapping, $element));
                } elseif ($mapping->canDeleteElement($element)) {
                    $toDelete[] = "{$element->siteId}_{$element->id}";
                }
            }
        }

        if ($toIndex) {
            $indexGroups = array_chunk($toIndex, 100);
            foreach ($indexGroups as $group) {
                $job = new IndexDocuments([
                    'engine' => $mapping->engine,
                    'documents' => $group,
                ]);
                Craft::$app->queue->push($job);
            }
        }

        if (count($toDelete) > 0) {
            $job = new DeleteDocuments([
                'engine' => $mapping->engine,
                'ids' => $toDelete,
            ]);
            Craft::$app->queue->push($job);
        }
    }

    public function deleteElements($elements)
    {
        foreach ($this->getMappings() as $mapping) {
            $toDelete = [];
            foreach ($elements as $element) {
                // Add support for 3.2 drafts and revisions
                if (method_exists($element, 'getIsDraft') && method_exists($element, 'getIsRevision')) {
                    if ($element->getIsDraft() || $element->getIsRevision()) {
                        continue;
                    }
                }

                if ($mapping->elementType === get_class($element) && $mapping->canDeleteElement($element)) {
                    $toDelete[] = "{$element->siteId}_{$element->id}";
                }
            }

            if (count($toDelete) > 0) {
                $job = new DeleteDocuments([
                    'engine' => $mapping->engine,
                    'ids' => $toDelete,
                ]);
                Craft::$app->queue->push($job);
            }
        }
    }

    private function isAssociativeArray($arr) {
        if (array() === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function transformElement(EngineMapping $mapping, $element)
    {
        $handler = $mapping->getTransformHandler();

        if (is_callable($handler)) {
            $data = $handler($mapping, $element);
        } else {
            $data = $handler->handle($mapping, $element);
        }


        if ($this->isAssociativeArray($data)) {
            return [$data];
        }

        return $data;
    }
}
