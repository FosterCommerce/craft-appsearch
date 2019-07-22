<?php
namespace fostercommerce\appsearch;

use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use League\Fractal\TransformerAbstract;

class ElementTransformer extends TransformerAbstract
{
    public function transform(ElementInterface $element): array
    {
        return ArrayHelper::toArray($element);
    }
}

