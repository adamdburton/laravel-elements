<?php

namespace Click\Elements\Services\Entities;

use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;
use Click\Elements\Models\Entity;
use Click\Elements\Models\Property;
use Click\Elements\PropertyType;
use Illuminate\Database\Eloquent\Collection;

class PropertyService
{
    /** @var Collection */
    protected $_cache = [];

    public function __construct()
    {
        $this->getProperties();
    }

    /**
     * @param string $entityType
     * @param array $properties
     * @throws PropertyMissingException
     * @throws PropertyAlreadyExistsException
     */
    public function sync(string $entityType, array $properties)
    {
        foreach($properties as $property => $type)
        {
            $propertyName = $entityType.'.'.$property;

            if(!$this->exists($propertyName)) {
                $this->create($propertyName, $type);
            } elseif ($this->getProperty($propertyName)->type !== $type) {
                throw new PropertyAlreadyExistsException($propertyName);
            }
        }
    }

    /**
     * @param $property
     * @return mixed
     * @throws PropertyMissingException
     */
    public function getProperty($property)
    {
        $this->getProperties();

        if(!isset($this->_cache[$property])) {
            throw new PropertyMissingException($property);
        }

        return $this->_cache[$property];
    }

    public function getProperties()
    {
        if(!$this->_cache) {
            $this->_cache = Property::all()->keyBy('property');
        }

        return $this->_cache;
    }

    /**
     * @param string $property
     * @return bool
     */
    public function exists(string $property)
    {
        $this->getProperties();

        return isset($this->_cache[$property]);
    }

    /**
     * @param string $propertyName
     * @param $type
     * @return Property
     */
    protected function create(string $propertyName, $type)
    {
        $property = Property::create(['property' => $propertyName, 'type' => $type]);

        $this->_cache[$propertyName] = $property;

        return $this->_cache[$propertyName];
    }
}
