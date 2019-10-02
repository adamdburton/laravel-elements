<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Element;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\ElementNotInstalledException;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Schemas\PropertySchema;
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

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @param Element $element
     * @param ElementSchema $schema
     * @param bool $load
     */
    public function __construct(Element $element, ElementSchema $schema, $load = true)
    {
        $this->element = $element;
        $this->schema = $schema;

        if ($load) {
            $this->load();
        }
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
            'alias' => $this->getAlias()
        ]);
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->properties = $this->buildPropertyDefinitions();
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
     * @return string
     */
    public function getAlias()
    {
        return $this->element->getAlias();
    }

    /**
     * @param null $attributes
     * @param null $meta
     * @return Element
     */
    public function factory($attributes = null, $meta = null)
    {
        $class = $this->getClass();

        /** @var Element $element */
        $element = new $class($attributes);

        if ($meta) {
            $element->setMeta($meta);
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
     * @return bool
     */
    public function isInstalled()
    {
        return $this->installed;
    }

    /**
     * @param $key
     * @return PropertyDefinition|null
     */
    public function getPropertyDefinition($key)
    {
        $properties = $this->getProperties();

        return $properties[$key] ?? null;
    }

    /**
     * @return array
     * @throws ElementNotInstalledException
     */
    public function getPropertyModels()
    {
        if (!$this->loaded) {
            $this->load();
        }

        if (!$this->isInstalled()) {
            throw new ElementNotInstalledException($this->getClass());
        }

        return $this->propertyModels;
    }

    /**
     * @param $property
     * @return Property|null
     * @throws ElementNotInstalledException
     */
    public function getPropertyModel($property)
    {
        $propertyModels = $this->getPropertyModels();

        return $propertyModels[$property];
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        dd('remove me');
        return Str::camel($this->element->getElementTypeName());
    }

    protected function load()
    {
        $this->propertyModels = Property::where('element', $this->getAlias())->get()->keyBy('key')->all();

        $this->loaded = true;
        $this->installed = true;
    }

    /**
     * @return array
     */
    protected function buildPropertyDefinitions()
    {
        return collect($this->schema->getSchema())
            ->map(function ($schema) {
                return new PropertyDefinition($this, $schema);
            })
            ->all();
    }
}
