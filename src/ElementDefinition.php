<?php

namespace Click\Elements;

use Click\Elements\Contracts\DefinitionContract;
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
     * @var string
     */
    protected $elementClass;

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

    public function __construct(string $class)
    {
        $this->elementClass = $class;

        $this->fromElement(new $class());
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->elementClass;
    }

    /**
     * @param Element $element
     * @return void
     */
    protected function fromElement(Element $element)
    {
        $element->getDefinition($schema = new ElementSchema());

        $this->properties = collect($schema->getSchema())->mapInto(PropertyDefinition::class)->all();
    }

    /**
     * @return Element
     */
    public function install()
    {
        // ElementType is purposefully the first Element to be registered and installed because paradoxes.

        // Install the properties required for the Element.

        Log::debug('Creating property models for element.', ['properties' => implode(', ', array_keys($this->properties)), 'element' => $this->getClass()]);

        $propertyModels = collect($this->properties)->map(function (PropertyDefinition $property) {
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
     * @param null $attributes
     * @param null $id
     * @return Element
     */
    public function element($attributes = null, $id = null)
    {
        $class = $this->elementClass;

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
}
