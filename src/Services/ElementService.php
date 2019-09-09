<?php

namespace Click\Elements\Services;

use Click\Elements\Elements\Element;
use Click\Elements\Exceptions\ElementNotDefinedException;
use Click\Elements\Exceptions\ElementTypeAlreadyExistsException;
use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Exceptions\PropertyNotDefinedException;
use Click\Elements\Models\Entity;
use Click\Elements\Models\EntityType;
use Click\Elements\Schema;

class ElementService
{
    protected $types = [];

    /**
     * @param string $class
     * @return EntityType
     * @throws PropertyAlreadyExistsException
     * @throws ElementTypeAlreadyExistsException
     */
    public function register(string $class)
    {
        if ($this->exists($class)) {
            throw new ElementTypeAlreadyExistsException($class);
        }

        /** @var Element $instance */
        $instance = app($class);

        elements()->properties()->registerSchema(
            $type = $instance->getEntityType(),
            $instance->getProperties()
        );

        $entityType = EntityType::create(['type' => $type, 'class' => $class]);

        $this->types[$class] = $entityType;

        return $entityType;
    }

    public function exists(string $class)
    {
        return isset($this->types[$class]);
    }

    /**
     * @param string $class
     * @param array $data
     * @return Schema
     * @throws ElementNotDefinedException
     * @throws PropertyMissingException
     * @throws PropertyNotDefinedException
     */
    public function factory(string $class, array $data)
    {
        if (!isset($this->types[$class])) {
            throw new ElementNotDefinedException($class);
        }

        /** @var Element $instance */
        $instance = new $class;

        $missingProperties = array_diff_key($instance->getEntityProperties(), $data);

        if (count($missingProperties)) {
            throw new PropertyMissingException(array_keys($missingProperties)[0]);
        }

        $extraData = array_diff_key($data, $instance->getEntityProperties());

        if (count($extraData)) {
            throw new PropertyNotDefinedException(array_keys($extraData)[0]);
        }

        return $instance->setAttributes($data);
    }

    /**
     * @param Schema $schema
     * @return Schema
     */
    public function create(Schema $schema)
    {
        $elementType = $this->getElementByType($schema->getEntityType());

        $entity = Entity::create(['type_id' => $elementType->id]);

        $schema->setAttribute('id', $entity->id);

        $this->save($schema);

        return $schema;
    }

    protected function getElementByType(string $type)
    {
        return EntityType::type($type)->firstOrFail();
    }

    /**
     * @param Schema $schema
     * @return bool
     */
    protected function save(Schema $schema)
    {
        $properties = $schema->getEntityProperties();
        $attributes = $schema->getAttributes();

        $relations = collect($properties)->mapWithKeys(function ($property, $key) use ($attributes) {
            return [$property->id => [$property->type . '_value' => $attributes[$key]]];
        })->all();

        return Entity::find($schema->id)->properties()->sync($relations);
    }

    public function update(Schema $schema)
    {
        $this->save($schema);

        return $schema;
    }
}
