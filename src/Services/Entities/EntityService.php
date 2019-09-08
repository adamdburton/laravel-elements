<?php

namespace Click\Elements\Services\Entities;

use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Exceptions\PropertyNotDefinedException;
use Click\Elements\Schema;

class EntityService
{
    /**
     * @param string $class
     * @throws PropertyAlreadyExistsException
     * @throws PropertyMissingException
     */
    public function register(string $class)
    {
        $instance = app($class);

        elements()->properties()->sync(
            $instance->getEntityType(),
            $instance->getEntityProperties()
        );
    }

    /**
     * @param Schema $instance
     * @param array $data
     * @return Schema
     * @throws PropertyNotDefinedException
     * @throws PropertyMissingException
     */
    public function factory(Schema $instance, array $data)
    {
        $missingProperties = array_diff_key($instance->getEntityProperties(), $data);

        if(count($missingProperties)) {
            throw new PropertyMissingException(array_keys($missingProperties)[0]);
        }

        $extraData = array_diff_key($data, $instance->getEntityProperties());

        if(count($extraData)) {
            throw new PropertyNotDefinedException(array_keys($diff)[0]);
        }

        return $instance->setRawAttributes($data);
    }

    /**
     * @param Schema $entity
     */
    public function save(Schema $entity)
    {
        $properties = $entity->getEntityProperties();

        
    }
}