<?php
namespace fostercommerce\appsearch\models;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use fostercommerce\appsearch\ElementTransformer;

class EngineMapping extends Model
{
    public $engine;

    public $elementType;

    public $criteria;

    public $transformer = ElementTransformer::class;

    public function canIndexElement(Element $element)
    {
        if (isset($this->criteria['site']) && $element->site->handle !== $this->criteria['site']) {
            return false;
        }

        if (isset($this->criteria['siteId']) && (int) $element->site->id !== (int) $this->criteria['siteId']) {
            return false;
        }

        return $this->getElementQuery($element)->count() > 0;
    }

    public function canDeleteElement(Element $element)
    {
        if (isset($this->criteria['siteId']) && (int) $element->site->id !== (int) $this->criteria['siteId']) {
            return false;
        }

        return $this->getElementQuery($element)->count() === 0 || $this->getElementQuery($element)->status(null)->count() > 0;
    }

    public function getTransformer()
    {
        if (is_callable($this->transformer) || $this->transformer instanceof TransformerAbstract) {
            return $this->transformer;
        }

        return Craft::createObject($this->transformer);
    }

    public function rules()
    {
        return [
            'engine'   => ['required', 'string'],
            'elementType' => [
                'string',
                'default' => Entry::class,
            ],
        ];
    }

    public function getElementQuery(Element $element = null): ElementQueryInterface
    {
        /** @var string|ElementInterface $elementType */
        $elementType = $this->elementType;
        $query = $elementType::find();
        if (!is_null($element)) {
            $query->id($element->id);
        }
        Craft::configure($query, $this->criteria);

        return $query;
    }
}

