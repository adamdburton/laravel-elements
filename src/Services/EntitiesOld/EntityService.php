<?php

namespace Click\Elements\Services\EntitiesOld;

use Click\Elements\Exceptions\ElementNotDefinedException;
use Click\Elements\Exceptions\Entity\SchemaModifiedException;
use Click\Elements\Exceptions\PropertyAlreadyExistsException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;
use Click\Elements\Models\Entity;
use Click\Elements\Schema;
use Click\Elements\Services\PropertyService;

class EntityService
{
    /**
     * @var MigrationService
     */
    protected $migrationService;

    public function __construct(MigrationService $migrationService)
    {
        $this->migrationService = $migrationService;
    }

    public function migrations()
    {
        return $this->migrationService;
    }

    /**
     * @param string $schemaClass
     * @return Entity
     * @throws PropertyAlreadyExistsException
     * @throws PropertyTypeInvalidException
     * @throws SchemaModifiedException
     */
    public function define(string $schemaClass)
    {
        if(!is_subclass_of($schemaClass, Schema::class)) {
            throw new \Exception('invalid schema');
        }

        $schema = app()->make($schemaClass);

        $type = $schema->getType();
        $entityProperties = $schema->getProperties();

        if ($this->isDefined($type)) {
            throw new SchemaModifiedException($type, $this->migrations()->generateMigration($schema));
        }

        $entity = Entity::create(['type' => $type]);

        foreach ($entityProperties as $key => $type) {
            elements()->properties()->define($entity, $key, $type);
        }

        return $entity;
    }

    /**
     * @param $type
     * @return bool
     */
    public function isDefined(string $type)
    {
        return Entity::type($type)->exists();
    }

    public function factory($type, $attributes = [])
    {
        if(!$type instanceof \Click\Elements\Entity) {
            throw new ElementNotDefinedException($type);
        }

        return new $type($attributes);
    }
}
