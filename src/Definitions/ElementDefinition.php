<?php

namespace Click\Elements\Definitions;

use Click\Elements\Builder;
use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Element;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
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
    protected static $propertyModels = [];

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
     * @return string
     */
    public function getAlias()
    {
        return $this->element->getAlias();
    }

    /**
     * @return Element
     */
    public function install()
    {
        // ElementType is purposefully the first Element to be registered and installed because paradoxes.

        // Install the properties required for the Element.

        $properties = $this->getProperties();

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

        $this->propertyModels = $propertyModels->all();

        $this->installed = true;

        Log::debug('Creating newly installed element.', ['element' => $this->getClass()]);

        return ElementType::create([
            'class' => $this->getClass(),
            'type' => $this->getAlias()
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
     * @return string
     */
    public function getClass()
    {
        return get_class($this->element);
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
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function query()
    {
        return $this->factory()->query();
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
     * @param $key
     * @return PropertyDefinition|null
     */
    public function getPropertyDefinition($key)
    {
        $properties = $this->getProperties();

        return $properties[$key] ?? null;
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
     * @return Property[]
     */
    public function getPropertyModels()
    {
        if (!self::$propertyModels) {
            self::$propertyModels = Property::all()->groupBy('element')->keyBy('key')->all();
        }

        return self::$propertyModels[$this->getAlias()];
    }

    /**
     * @return bool
     * @throws ElementNotInstalledException
     */
    public function isInstalled()
    {
        return in_array($this->getAlias(), $this->getPropertyModels());
    }
}
