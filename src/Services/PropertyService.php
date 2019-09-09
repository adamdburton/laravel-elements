<?php

namespace Click\Elements\Services;

use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Models\Property;
use Click\Elements\Schema;
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
     * @return Property[]
     */
    public function getProperties()
    {
        if (!$this->_cache) {
            $this->_cache = Property::all()->keyBy('property');
        }

        return $this->_cache->all();
    }

    /**
     * @param string $elementType
     * @param array $properties
     * @throws PropertyAlreadyExistsException
     */
    public function registerSchema(string $elementType, array $properties)
    {
        foreach ($properties as $property => $propertyConfig) {
            $propertyName = $elementType . '.' . $property;
            $propertyType = $propertyConfig['type'];

            if ($this->exists($propertyName)) {
                throw new PropertyAlreadyExistsException($propertyName);
            }

            $this->create($propertyName, $propertyType);
        }
    }

    /**
     * @param string $property
     * @return bool
     */
    public function exists(string $property)
    {
        $this->getProperties();

        return $this->_cache->has($property);
    }

    /**
     * @param string $propertyName
     * @param $type
     * @return Property
     */
    protected function create(string $propertyName, $type)
    {
        $property = Property::create(['property' => $propertyName, 'type' => $type]);

        $this->_cache->put($propertyName, $property);

        return $property;
    }

    /**
     * @param Schema $entity
     * @param $property
     * @return mixed
     * @throws PropertyMissingException
     */
    public function getPropertyForEntity(Schema $entity, $property)
    {
        return $this->getProperty($entity->getEntityType() . '.' . $property);
    }

    /**
     * @param $property
     * @return mixed
     * @throws PropertyMissingException
     */
    public function getProperty($property)
    {
        $this->getProperties();

        if (!isset($this->_cache[$property])) {
            throw new PropertyMissingException($property);
        }

        return $this->_cache[$property];
    }
}
