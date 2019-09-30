<?php

namespace Click\Elements;

use Click\Elements\Definitions\PropertyDefinition;
use Illuminate\Http\Request;

class Query
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Element $element)
    {
        $this->builder = new Builder($element);
    }

    /**
     * @param Request $request
     * @throws Exceptions\ElementTypeNotRegisteredException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function fromRequest(Request $request)
    {
        $params = $request->all();

        // Search properties

        $properties = $this->builder->factory()->getElementDefinition()->getProperties();

        collect($properties)->each(function (PropertyDefinition $property) use ($params) {
            if (isset($params[$key = $property->getKey()])) {
                $this->builder->where($key, $params[$key]);
            }
        });
    }

    /**
     * @param mixed $type
     * @return Query
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function where($property, $comparison, $value = null)
    {
        $this->wheres[] = [];
    }
}
