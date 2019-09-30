<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Element;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\ElementTypeNotInstalledException;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\ElementSchema;
use Illuminate\Support\Facades\Log;

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
     * @var Element
     */
    protected $element;

    /**
     * @var PropertyDefinition[]
     */
    protected $properties;

    /**
     * @var Property[]
     */
    protected $propertyModels;

    /**
     * @var bool
     */
    private $installed = false;

    public function __construct(ElementSchema $schema, Element $element)
    {
        $this->schema = $schema;
        $this->element = $element;
    }

    /**
     * @return Element
     */
    public function install()
    {
        // ElementType is purposefully the first Element to be registered and installed because paradoxes.

        // Install the properties required for the Element.

        $properties = $this->getProperties();

        Log::debug('Creating property models for element.', ['properties' => implode(', ', array_keys($properties)), 'element' => $this->getClass()]);

        $propertyModels = collect($properties)->map(function (PropertyDefinition $property) {
            return $property->install();
        });

        // Make a new ElementType.

        $this->propertyModels = $propertyModels->all();

        $this->installed = true;

        Log::debug('Creating newly installed element.', ['element' => $this->getClass()]);

        return ElementType::create([
            'name' => $this->getClass(),
            'properties' => $propertyModels->pluck('id', 'key') // Key to ID lookup field
        ]);
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->properties = collect($this->schema->getSchema())->mapInto(PropertyDefinition::class)->all();
        }

        return $this->properties;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return get_class($this->element);
    }

    /**
     * @param null $attributes
     * @param null $id
     * @return Element
     */
    public function factory($attributes = null, $id = null)
    {
        $class = $this->getClass();

        /** @var Element $element */
        $element = new $class($attributes);

        if ($id) {
            $element->setPrimaryKey($id);
        }

        return $element;
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return collect($this->properties)->map(function (PropertyDefinition $property) {
            return $property->getValidation();
        })->filter()->all();
    }

    /**
     * @param $key
     * @return PropertyDefinition|null
     */
    public function getPropertyDefinition($key)
    {
        return $this->properties[$key] ?? null;
    }

    /**
     * @return array
     * @throws ElementTypeNotInstalledException
     */
    public function getPropertyModels()
    {
        if (!$this->installed) {
            throw new ElementTypeNotInstalledException($this->getClass());
        }

        return $this->propertyModels;
    }

    /**
     * @param $property
     * @return Property|null
     */
    public function getPropertyModel($property)
    {
        return $this->propertyModels[$property];
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return Str::camel($this->element->getElementTypeName());
    }
}
