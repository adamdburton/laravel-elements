<?php

namespace Click\Elements\Schemas;

use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Exceptions\Property\RelationTypeInvalidException;
use Click\Elements\Exceptions\SchemaPropertyAlreadyDefined;
use Click\Elements\Schema;
use Click\Elements\Types\PropertyType;
use Click\Elements\Types\RelationType;

/**
 * Class ElementSchema
 */
class ElementSchema extends Schema
{
    /**
     * @var PropertySchema[]
     */
    protected $schema = [];

    public function __construct()
    {
        $this->add('type', PropertyType::STRING)->label('Element Type');
    }

    /**
     * @param $key
     * @param $type
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    protected function add($key, $type)
    {
        if (isset($this->schema[$key])) {
            throw new SchemaPropertyAlreadyDefined($key, $this);
        }

        return $this->schema[$key] = new PropertySchema($key, $type);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function boolean($key)
    {
        return $this->add($key, PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function integer($key)
    {
        return $this->add($key, PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function double($key)
    {
        return $this->add($key, PropertyType::DOUBLE);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function string($key)
    {
        return $this->add($key, PropertyType::STRING);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function text($key)
    {
        return $this->add($key, PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function array($key)
    {
        return $this->add($key, PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function json($key)
    {
        return $this->add($key, PropertyType::JSON);
    }

    public function belongsTo($key, string $elementAlias)
    {
        return $this->relation($key, $elementAlias, RelationType::BELONGS_TO);
    }

    /**
     * @param $key
     * @param string $elementAlias
     * @param string $relationType
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     * @throws RelationTypeInvalidException
     */
    public function relation($key, string $elementAlias, string $relationType)
    {
        RelationType::validateValue($key, $relationType, $relationType);

        return $this->add($key, PropertyType::RELATION)
            ->relationElement($elementAlias)
            ->relationType($relationType);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function timestamp($key)
    {
        return $this->add($key, PropertyType::TIMESTAMP);
    }

    /**
     * @return PropertySchema[]
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
