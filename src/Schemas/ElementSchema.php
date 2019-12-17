<?php

namespace Click\Elements\Schemas;

use Click\Elements\Exceptions\Property\DoubleDecimalsNotValidException;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
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

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    public function boolean($key)
    {
        return $this->add($key, PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    public function integer($key)
    {
        return $this->add($key, PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    public function unsignedInteger($key)
    {
        return $this->add($key, PropertyType::UNSIGNED_INTEGER);
    }

    /**
     * @param $key
     * @param int $decimals
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     * @throws DoubleDecimalsNotValidException
     */
    public function double($key, $decimals = 2)
    {
        PropertyType::validateDoubleDecimals($decimals);

        return $this->add($key, PropertyType::DOUBLE)
            ->decimals($decimals);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
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
     * @throws PropertyAlreadyDefinedException
     */
    public function text($key)
    {
        return $this->add($key, PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     */
    public function array($key)
    {
        return $this->add($key, PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     */
    public function json($key)
    {
        return $this->add($key, PropertyType::JSON);
    }

    /**
     * @param $key
     * @param string $elementAlias
     * @param string $relationType
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     * @throws RelationTypeNotValidException
     */
    public function relation($key, string $elementAlias, string $relationType)
    {
        RelationType::validateValue($relationType);

        return $this->add($key, PropertyType::RELATION, RelationPropertySchema::class)
            ->elementType($elementAlias)
            ->relationType($relationType);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     */
    public function timestamp($key)
    {
        return $this->add($key, PropertyType::TIMESTAMP);
    }

    /**
     * @param $key
     * @param $type
     * @param null $schema
     * @return PropertySchema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    protected function add($key, $type, $schema = null)
    {
        if (isset($this->schema[$key])) {
            throw new PropertyAlreadyDefinedException($key);
        }

        $this->validateKey($key);

        /** @var string $schema */
        $schema = $schema ?: PropertySchema::class;

        return $this->schema[$key] = new $schema($key, $type);
    }

    /**
     * @param string $key
     * @throws PropertyKeyInvalidException
     */
    protected function validateKey(string $key)
    {
        if (!preg_match('/^_?\\w*$/', $key)) {
            throw new PropertyKeyInvalidException($key);
        }
    }

    /**
     * @return PropertySchema[]
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
