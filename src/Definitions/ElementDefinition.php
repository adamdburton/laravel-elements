<?php

namespace Click\Elements\Definitions;

use Click\Elements\Builder;
use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\Attribute\AttributeNotDefinedException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Models\Attribute;
use Click\Elements\Schemas\AttributeSchema;
use Click\Elements\Schemas\ElementSchema;

/**
 * Element definition container
 */
class ElementDefinition implements DefinitionContract
{
    /**
     * @var ElementSchema
     */
    protected $schema;

    /**
     * @var string
     */
    protected $elementClass;

    /**
     * @var string
     */
    protected $elementAlias;

    /**
     * @var AttributeDefinition[]
     */
    protected $attributes;

    /**
     * @var Attribute[]
     */
    protected $models = [];

    /**
     * @param string $elementClass
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function __construct(string $elementClass)
    {
        /** @var Element $instance */
        $instance = new $elementClass;

        $this->elementClass = $elementClass;
        $this->elementAlias = $instance->getAlias();

        $schema = new ElementSchema($instance);

        $instance->buildMetaDefinition($schema);
        $instance->buildDefinition($schema);

        $this->schema = $schema;
    }

    /**
     * @return void
     */
    public function install()
    {
        foreach ($this->getAttributeDefinitions() as $attribute) {
            if (!$attribute->isInstalled()) {
                $this->models[$attribute->getKey()] = $attribute->install();
            }
        }
    }

    /**
     * @return AttributeDefinition[]
     */
    public function getAttributeDefinitions()
    {
        if (!$this->attributes) {
            $this->attributes = collect($this->schema->getSchema())
                ->map(function (AttributeSchema $schema) {
                    return new AttributeDefinition($this, $schema);
                })
                ->all();
        }

        return $this->attributes;
    }

    /**
     * @return Element
     */
    public function factory()
    {
        $class = $this->getClass();

        /** @var Element $element */
        $element = new $class();

        return $element;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->elementClass;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->elementAlias;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        $class = $this->getClass();

        return new Builder(new $class);
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return collect($this->attributes)->map(function (AttributeDefinition $attribute) {
            return $attribute->getMeta('validation', null);
        })->filter()->all();
    }

    /**
     * @param string $attribute
     * @return AttributeDefinition|null
     * @throws AttributeNotDefinedException
     */
    public function getAttributeDefinition(string $attribute)
    {
        $this->validateAttributeExists($attribute);

        $attributes = $this->getAttributeDefinitions();

        return $attributes[$attribute];
    }

    /**
     * @param string $attribute
     * @throws AttributeNotDefinedException
     */
    protected function validateAttributeExists(string $attribute)
    {
        $attributes = $this->getAttributeDefinitions();

        if (!isset($attributes[$attribute])) {
            throw new AttributeNotDefinedException($attribute, $this);
        }
    }

    /**
     * @param string $attribute
     * @return Attribute
     * @throws AttributeNotDefinedException
     */
    public function getAttributeModel(string $attribute)
    {
        $this->validateAttributeExists($attribute);

        $attributeModels = $this->getAttributeModels();

        return $attributeModels[$attribute];
    }

    /**
     * @return Attribute[]
     */
    public function getAttributeModels()
    {
        return $this->models = Attribute::all()->keyBy('key')->all();
    }
}
