<?php

namespace Click\Elements\Concerns\Element;

use Carbon\Carbon;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyNotDefinedException;
use Click\Elements\Exceptions\Property\PropertyValidationFailedException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\RelationNotDefinedException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Types\PropertyType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Provides typed properties for Elements
 * @method ElementDefinition getElementDefinition()
 * @property Element $element;
 */
trait HasTypedProperties
{
    use HasRelations;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param $key
     * @return null
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotDefinedException
     * @throws RelationNotDefinedException
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailedException
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotDefinedException
     * @throws RelationNotDefinedException
     */
    public function getAttribute($key)
    {
        if ($this->hasGetMutator($key) ||
            array_key_exists($key, $this->attributes) ||
            $this->hasRelationLoaded($key) ||
            $this->hasRelation($key)) {
            return $this->getAttributeValue($key);
        }

        throw new PropertyNotDefinedException($key, $this->getElementDefinition());
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }


    /**
     * @param string $key
     * @return mixed
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws RelationNotDefinedException
     */
    public function getAttributeValue($key)
    {
        $value = $this->attributes[$key] ?? null;

        if ($this->hasRelation($key)) {
            return $this->getRelation($key);
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailedException
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     * @thr«ows PropertyNotRegisteredException
     */
    public function setAttribute($key, $value)
    {
        $this->validateAttribute($key, $value);

        if ($this->hasRelation($key)) {
            $this->setRelation($key, $value);
        } elseif ($this->hasSetter($key)) {
            $this->runSetter($key, $value);
        } else {
            $this->setAttributeValue($key, $value);
        }

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @throws PropertyValidationFailedException
     */
    protected function validateAttribute($key, $value)
    {
        $rules = $this->getElementDefinition()->getValidationRules();

        if (!isset($rules[$key])) {
            return;
        }

        // TODO: Allow passing validation messages and custom attributes here

        $validator = Validator::make([$key => $value], [$key => $rules[$key]]);

        if ($validator->fails()) {
            throw new PropertyValidationFailedException(
                $this->getAlias(),
                $key,
                $validator->getMessageBag()->get($key)
            );
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasSetter($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function runSetter($key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * @param $key
     * @param $value
     * @return HasTypedProperties
     * @throws PropertyValueInvalidException
     */
    public function setAttributeValue($key, $value)
    {
        $this->validatePropertyValue($key, $value);

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @throws PropertyValueInvalidException
     */
    public function validatePropertyValue(string $key, $value)
    {
        /** @var PropertyDefinition $property */
        $definition = $this->getElementDefinition()->getPropertyDefinition($key);

        $type = $definition->getType();
        $checkType = true;

        if ($type === PropertyType::JSON) {
            $type = 'array';
        } elseif ($type === PropertyType::UNSIGNED_INTEGER) {
            $type = 'integer';
        } elseif ($type === PropertyType::TEXT) {
            $type = 'string';
        } elseif ($type === PropertyType::TIMESTAMP) {
            if (!$value instanceof Carbon) {
                throw new PropertyValueInvalidException($definition->getKey(), Carbon::class, $value);
            }

            $checkType = false;
        }

        if ($checkType && gettype($value) !== $type) {
            throw new PropertyValueInvalidException($definition->getKey(), $type, $value);
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return collect($this->attributes)->mapWithKeys(function ($key) {
            return [$key => $this->getAttribute($key)];
        })->all();
    }

    /**
     * @param $attributes
     * @return $this
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailedException
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     */
    public function setAttributes($attributes)
    {
        $this->validateAttributes($attributes);

        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param $attributes
     * @throws PropertyValidationFailedException
     */
    protected function validateAttributes($attributes)
    {
        $rules = $this->getElementDefinition()->getValidationRules();

        // TODO: Allow passing validation messages and custom attributes here

        if (count($rules)) {
            $validator = Validator::make($attributes, $rules);

            if ($validator->fails()) {
                $key = $validator->errors()->keys()[0];
                $errors = $validator->errors()->get($key);

                throw new PropertyValidationFailedException($this->getAlias(), $key, $errors);
            }
        }
    }

    /**
     * @return array
     */
    public function getRawAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $attributes
     * @return HasTypedProperties
     */
    public function setRawAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }
}
