<?php

namespace Click\Elements\Definitions;

use Click\Elements\Builder;
use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Element;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\ElementSchema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Element definition container
 */
class ElementDefinition implements DefinitionContract
{
    /**
     * @var Collection
     */
    protected static $propertyModels;

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
     * @param Element $element
     * @param ElementSchema $schema
     * @param bool $load
     * @throws ElementNotInstalledException
     */
    public function __construct(Element $element, ElementSchema $schema, $load = true)
    {
        $this->element = $element;
        $this->schema = $schema;

        if ($load) {
            $this->getPropertyModels();
        }
    }

    /**
     * @return Property[]
     * @throws ElementNotInstalledException
     */
    public function getPropertyModels()
    {
        if (!isset(static::$propertyModels[$this->getAlias()])) {
            static::$propertyModels = Property::all()->groupBy('element');
        }

        if (!isset(static::$propertyModels[$this->getAlias()])) {
            throw new ElementNotInstalledException($this->getClass());
        }

        return static::$propertyModels[$this->getAlias()]->keyBy('key')->all();
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->element->getAlias();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return get_class($this->element);
    }

    /**
     * @return Element
     */
    public function install()
    {
        // ElementType is purposefully the first Element to be registered and installed because paradoxes.

        // Install the properties required for the Element.

        $properties = $this->getPropertyDefinitions();

        Log::debug(
            'Creating property models for element.',
            [
                'properties' => implode(', ', array_keys($properties)),
                'element' => $this->getClass()
            ]
        );

        $propertyModels = collect($properties)->map(function (PropertyDefinition $property) {
            return $property->install();
        });

        // Make a new ElementType.

        static::$propertyModels = $propertyModels->all();

        Log::debug('Creating newly installed element.', ['element' => $this->getClass()]);

        $element = ElementType::createRaw([
            'class' => $this->getClass()
        ]);

        return $element;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getPropertyDefinitions()
    {
        if (!$this->properties) {
            $this->properties = $this->buildPropertyDefinitions();
        }

        return $this->properties;
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

    /**
     * @param null $attributes
     * @param null $meta
     * @param null $relations
     * @return Element
     */
    public function factory($attributes = null, $meta = null, $relations = null)
    {
        $class = $this->getClass();

        /** @var Element $element */
        $element = new $class($attributes);

        if ($meta) {
            $element->setMeta($meta);
        }

        if ($relations) {
            $element->setRelations($relations);
        }

        return $element;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $class = $this->getClass();

        return new Builder(new $class);
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return collect($this->properties)->map(function (PropertyDefinition $property) {
            return $property->getMeta('validation', null);
        })->filter()->all();
    }

    /**
     * @param string $key
     * @return PropertyDefinition|null
     * @throws PropertyNotRegisteredException
     */
    public function getPropertyDefinition(string $key)
    {
        $properties = $this->getPropertyDefinitions();

        if (!isset($properties[$key])) {
            throw new PropertyNotRegisteredException($key);
        }

        return $properties[$key];
    }

    /**
     * @param $property
     * @return Property
     * @throws ElementNotInstalledException
     * @throws PropertyNotInstalledException
     */
    public function getPropertyModel($property)
    {
        $propertyModels = $this->getPropertyModels();

        if (!isset($propertyModels[$property])) {
            throw new PropertyNotInstalledException($property);
        }

        return $propertyModels[$property];
    }

    /**
     * @return bool
     * @throws ElementNotInstalledException
     */
    public function isInstalled()
    {
        return collect($this->getPropertyModels())->pluck('element')->contains($this->getAlias());
    }
}
