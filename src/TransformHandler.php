<?php
namespace fostercommerce\appsearch;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class TransformHandler
{
    public function handle($mapping, $element): array
    {
        $transformer = $mapping->getTransformer();
        $resource = new Item($element, $transformer);

        $fractal = new Manager();
        $fractal->setSerializer(new ElementSerializer());

        $data = $fractal->createData($resource)->toArray();

        $data['id'] = "{$element->siteId}_{$element->id}";
        return $data;
    }
}

