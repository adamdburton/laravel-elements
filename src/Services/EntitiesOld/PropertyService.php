<?php

namespace Click\Elements\Services\EntitiesOld;

use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;
use Click\Elements\Models\Entity;
use Click\Elements\Models\Property;
use Click\Elements\PropertyType;

class PropertyService
{
    /**
     * @param Entity $entity
     * @param $propertyName
     * @param $type
     * @return Property
     * @throws PropertyAlreadyExistsException
     * @throws PropertyTypeInvalidException
     */
    public function define(Entity $entity, $propertyName, $type)
    {
        if (!in_array($type, PropertyType::getTypes())) {
            throw new PropertyTypeInvalidException($type);
        }

        $fullName = $entity->type . '.' . $propertyName;

        if ($this->exists($fullName)) {
            throw new PropertyAlreadyExistsException($fullName);
        }

        return Property::create(['property' => $fullName, 'type' => $type]);
    }

    /**
     * @param string $property
     * @return bool
     */
    public function exists(string $property)
    {
        return Property::property($property)->exists();
    }
}
