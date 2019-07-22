<?php
namespace fostercommerce\appsearch;

use League\Fractal\Serializer\ArraySerializer;

class ElementSerializer extends ArraySerializer
{
    function toSnakeCase($key) {
        $key[0] = strtolower($key[0]);

        $next = function ($key) {
            return "_".strtolower($key[1]);
        };
        return preg_replace_callback('/([A-Z])/', $next, $key);
    }

    public function item($resourceKey, array $data)
    {

        $serialized = [];

        foreach ($data as $key => $value) {
            $serialized[$this->toSnakeCase($key)] = $value;
        }

        return $serialized;
    }
}


