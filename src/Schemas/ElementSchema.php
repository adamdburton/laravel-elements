<?php

namespace Click\Elements\Schemas;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Exceptions\Property\DoubleDecimalsNotValidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Schema;
use Click\Elements\Types\AttributeType;
use Click\Elements\Types\RelationType;

/**
 * Class ElementSchema
 */
class ElementSchema extends Schema
{
    /**
     * Valid: foo, fooBar, FooBar, _foo, _fooBar, foo_bar, FOO, FOO_BAR
     * Invalid: 1, 12, 12foo, !foo, 'foo bar'
     *
     * @var string
     */
    protected static $validKeyRegex = '/^_?\\w*$/';

    /**
     * @var AttributeSchema[]
     */
    protected $attributes = [];

    /**
     * @var Element
     */
    protected $element;

    /**
     * ElementSchema constructor.
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }

    /**
     * @return AttributeSchema[]
     */
    public function getSchema()
    {
        return $this->attributes;
    }

    /**
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function boolean($key)
    {
        return $this->add($key, AttributeType::BOOLEAN);
    }

    /**
     * @param $key
     * @param $type
     * @param null $schema
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    protected function add($key, $type, $schema = null)
    {
        if (isset($this->attributes[$key])) {
            throw new AttributeAlreadyDefinedException($key, $this);
        }

        $this->validateKey($key);

        return $this->attributes[$key] = $this->schemaFactory($schema, $key, $type);
    }

    /**
     * @param string $key
     * @throws AttributeKeyInvalidException
     */
    protected function validateKey(string $key)
    {
        if (!preg_match(static::$validKeyRegex, $key)) {
            throw new AttributeKeyInvalidException($key);
        }
    }

    /**
     * @param string $schemaClass
     * @param string $key
     * @param string $type
     * @return AttributeSchema
     * @throws AttributeSchemaClassInvalidException
     */
    protected function schemaFactory($schemaClass, string $key, string $type)
    {
        $schemaClass = $schemaClass ?? AttributeSchema::class;

        $this->validateSchemaClass($schemaClass);

        return new $schemaClass($key, $type);
    }

    /**
     * @param string $schemaClass
     * @throws AttributeSchemaClassInvalidException
     */
    protected function validateSchemaClass(string $schemaClass)
    {
        if (!$schemaClass === AttributeSchema::class && !is_subclass_of($schemaClass, AttributeSchema::class)) {
            throw new AttributeSchemaClassInvalidException($schemaClass);
        }
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function integer($key)
    {
        return $this->add($key, AttributeType::INTEGER);
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function unsignedInteger($key)
    {
        return $this->add($key, AttributeType::UNSIGNED_INTEGER);
    }

    /**
     * @param $key
     * @param int $decimals
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     * @throws DoubleDecimalsNotValidException
     */
    public function double($key, $decimals = 2)
    {
        AttributeType::validateDoubleDecimals($decimals);

        return $this->add($key, AttributeType::DOUBLE)->decimals($decimals);
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function string($key)
    {
        return $this->add($key, AttributeType::STRING);
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeSchemaClassInvalidException
     */
    public function text($key)
    {
        return $this->add($key, AttributeType::TEXT);
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeSchemaClassInvalidException
     */
    public function array($key)
    {
        return $this->add($key, AttributeType::ARRAY);
    }

    /**
     * @param $key
     * @return AttributeSchema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeSchemaClassInvalidException
     */
    public function json($key)
    {
        return $this->add($key, AttributeType::JSON);
    }

    /**
     * @param string $key
     * @param string $elementAlias
     * @param string $relationType
     * @return AttributeSchema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws RelationTypeNotValidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function relation(string $key, string $elementAlias, string $relationType)
    {
        RelationType::validateValue($relationType);

        return $this->add($key, AttributeType::RELATION, RelationSchema::class)
            ->elementType($elementAlias)
            ->relationType($relationType);
    }

    /**
     * @param string $key
     * @param string $elementAlias
     * @param string|null $foreignKey
     * @return AttributeSchema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     * @throws RelationTypeNotValidException
     */
    public function belongsTo(string $key, string $elementAlias, string $foreignKey = null)
    {
        $foreignKey = $foreignKey ?? $this->element->getSingular();

        return $this->relation($key, $elementAlias, RelationType::BELONGS_TO)->reverse();
    }

    /**
     * @param string $key
     * @return AttributeSchema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeSchemaClassInvalidException
     */
    public function timestamp(string $key)
    {
        return $this->add($key, AttributeType::TIMESTAMP);
    }
}
