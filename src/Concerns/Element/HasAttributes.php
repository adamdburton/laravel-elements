<?php

namespace Click\Elements\Concerns\Element;

use Carbon\Carbon;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeNotDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeValidationFailedException;
use Click\Elements\Exceptions\Attribute\AttributeValueTypeInvalidException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\RelationNotDefinedException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Types\AttributeType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Provides typed properties for Elements
 * @method ElementDefinition getElementDefinition()
 * @property Element $element;
 */
trait HasAttributes
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
     * @param string $attribute
     * @return null
     * @throws ElementNotRegisteredException
     * @throws AttributeNotDefinedException
     * @throws RelationNotDefinedException
     */
    public function __get(string $attribute)
    {
        return $this->getAttribute($attribute);
    }

    /**
     * @param string $attribute
     * @param $value
     * @throws ManyRelationInvalidException
     * @throws AttributeValidationFailedException
     * @throws AttributeValueTypeInvalidException
     * @throws SingleRelationInvalidException
     * @throws AttributeNotDefinedException
     */
    public function __set(string $attribute, $value)
    {
        $this->setAttribute($attribute, $value);
    }

    /**
     * @param string $attribute
     * @return mixed
     * @throws ElementNotRegisteredException
     * @throws AttributeNotDefinedException
     * @throws RelationNotDefinedException
     */
    public function getAttribute($attribute)
    {
        if ($this->hasRelationLoaded($attribute)) {
            return $this->getLoadedRelation($attribute);
        }

        if ($this->hasRelation($attribute)) {
            return $this->getRelation($attribute);
        }

        if ($this->hasGetMutator($attribute)) {
            return $this->mutateAttribute($attribute);
        }

        if ($this->hasAttribute($attribute)) {
            return $this->getAttributeValue($attribute);
        }

        throw new AttributeNotDefinedException($attribute, $this->getElementDefinition());
    }

    /**
     * @param string $attribute
     * @return bool
     */
    protected function hasGetMutator(string $attribute)
    {
        return method_exists($this, 'get' . Str::studly($attribute) . 'Attribute');
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    protected function mutateAttribute(string $attribute)
    {
        $value = $this->getAttributeValue($attribute);

        return $this->{'get' . Str::studly($attribute) . 'Attribute'}($value);
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    protected function getAttributeValue(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    protected function hasAttribute(string $attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    /**
     * @param string $attribute
     * @param $value
     * @return $this
     * @throws ManyRelationInvalidException
     * @throws AttributeValidationFailedException
     * @throws AttributeValueTypeInvalidException
     * @throws SingleRelationInvalidException
     * @throws AttributeNotDefinedException
     */
    public function setAttribute($attribute, $value)
    {
        $this->validateAttribute($attribute, $value);

        if ($this->hasRelation($attribute)) {
            $this->setRelation($attribute, $value);
        } elseif ($this->hasSetter($attribute)) {
            $this->runSetter($attribute, $value);
        } else {
            $this->setAttributeValue($attribute, $value);
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param $value
     * @throws AttributeValidationFailedException
     */
    protected function validateAttribute(string $attribute, $value)
    {
        $rules = $this->getElementDefinition()->getValidationRules();

        if (!isset($rules[$attribute])) {
            return;
        }

        // TODO: Allow passing validation messages and custom attributes here

        $validator = Validator::make([$attribute => $value], [$attribute => $rules[$attribute]]);

        if ($validator->fails()) {
            throw new AttributeValidationFailedException(
                $this->getAlias(),
                $attribute,
                $validator->getMessageBag()->get($attribute)
            );
        }
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function hasSetter(string $attribute)
    {
        return method_exists($this, 'set' . Str::studly($attribute) . 'Attribute');
    }

    /**
     * @param string $attribute
     * @param $value
     * @return mixed
     */
    protected function runSetter(string $attribute, $value)
    {
        return $this->{'set' . Str::studly($attribute) . 'Attribute'}($value);
    }

    /**
     * @param string $attribute
     * @param $value
     * @throws AttributeValueTypeInvalidException
     * @throws AttributeNotDefinedException
     */
    protected function setAttributeValue(string $attribute, $value)
    {
        $this->validateAttributeValue($attribute, $value);

        $this->attributes[$attribute] = $value;
    }

    /**
     * @param string $attribute
     * @param $value
     * @throws AttributeValueTypeInvalidException
     * @throws AttributeNotDefinedException
     */
    public function validateAttributeValue(string $attribute, $value)
    {
        $definition = $this->getElementDefinition()->getAttributeDefinition($attribute);

        $type = $definition->getType();

        $skipTypeCheck = false;

        if ($type === AttributeType::JSON) {
            $type = 'array';
        } elseif ($type === AttributeType::UNSIGNED_INTEGER) {
            $type = 'integer';
        } elseif ($type === AttributeType::TEXT) {
            $type = 'string';
        } elseif ($type === AttributeType::TIMESTAMP) {
            if (!$value instanceof Carbon) {
                $type = Carbon::class;
            }

            $skipTypeCheck = true;
        }

        if (!$skipTypeCheck && gettype($value) !== $type) {
            throw new AttributeValueTypeInvalidException($this->getAlias(), $definition->getKey(), $type, $value);
        }
    }

    /**
     * @param string $attribute
     */
    public function __unset(string $attribute)
    {
        $this->unsetAttribute($attribute);
    }

    /**
     * @param string $attribute
     * @return $this
     */
    public function unsetAttribute(string $attribute)
    {
        if ($this->hasRelation($attribute)) {
            $this->unsetRelation($attribute);
        }

        return $this->unsetAttributeValue($attribute);
    }

    /**
     * @param string $attribute
     * @return $this
     */
    protected function unsetAttributeValue(string $attribute)
    {
        unset($this->attributes[$attribute]);

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeValues()
    {
        return collect($this->attributes)->mapWithKeys(function ($value, $key) {
            return [$key => $this->getAttribute($key)];
        })->all();
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws ManyRelationInvalidException
     * @throws AttributeValidationFailedException
     * @throws AttributeValueTypeInvalidException
     * @throws SingleRelationInvalidException
     * @throws AttributeNotDefinedException
     */
    public function setAttributes(array $attributes)
    {
        $this->validateAttributes($attributes);

        foreach ($attributes as $attribute => $value) {
            if ($this->hasRelation($attribute)) {
                $this->setRelation($attribute, $value);
            } elseif ($this->hasSetter($attribute)) {
                $this->runSetter($attribute, $value);
            } else {
                $this->setAttributeValue($attribute, $value);
            }
        }

        return $this;
    }

    /**
     * @param array $attributes
     * @throws AttributeValidationFailedException
     */
    protected function validateAttributes(array $attributes)
    {
        $rules = $this->getElementDefinition()->getValidationRules();

        // TODO: Allow passing validation messages and custom attributes here

        if (count($rules)) {
            $validator = Validator::make($attributes, $rules);

            if ($validator->fails()) {
                $key = $validator->errors()->keys()[0];
                $errors = $validator->errors()->get($key);

                throw new AttributeValidationFailedException($this->getAlias(), $key, $errors);
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
     * @param array $attributes
     * @return $this
     */
    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }
}
