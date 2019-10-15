<?php

namespace Click\Elements\Models;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Pivots\EntityProperty;
use Click\Elements\Scopes\ElementScope;
use Click\Elements\Types\PropertyType;
use Click\Elements\Types\RelationType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Model for storing entities
 * @property Collection properties
 * @property int id
 * @property string type
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Collection relatedElements
 */
class Entity extends Model
{
    protected $table = 'elements_entities';

    protected $fillable = ['type', 'version'];

    protected static function boot()
    {
        parent::boot();

        // Force properties() to auto-load which has the pivot
        // data needed for an entity to mean anything useful.

        static::addGlobalScope(new ElementScope);
    }

    // Relationships

    /**
     * @return BelongsToMany
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'elements_entity_properties')
            ->using(EntityProperty::class)
            ->withPivot(
                'boolean_value',
                'integer_value',
                'unsigned_integer_value', // Also used for relatedElements() as foreign key
                'double_value',
                'string_value',
                'text_value',
                'json_value', // Also used for relatedElements() as pivot
                'timestamp_value'
            );
    }

    /**
     * @return BelongsToMany
     */
    public function relatedElements()
    {
        return $this->belongsToMany(
            Entity::class,
            'elements_entity_properties',
            'entity_id',
            'unsigned_integer_value'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function reverseRelatedElements()
    {
        return $this->belongsToMany(
            Entity::class,
            'elements_entity_properties',
            'unsigned_integer_value',
            'entity_id'
        );
    }

    /**
     * @return MorphTo
     */
    public function bindable()
    {
        return $this->morphTo();
    }

    // Methods

    /**
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws BindingResolutionException
     */
    public function toElement()
    {
        $attributes = $this->getPropertyValues();

        return elements()->factory(
            $this->type,
            $attributes,
            $this->getMeta()
        );
    }

    /**
     * @return Property[]
     */
    protected function getPropertyValues()
    {
        $values = [];

        $this->properties->each(function (Property $property) use (&$values) {
            if (isset($values[$property->key]) && !is_array($values[$property->key])) {
                $values[$property->key] = [$values[$property->key]];
            }

            if (isset($values[$property->key]) && is_array($values[$property->key])) {
                $values[$property->key][] = $property->getValue();
            } else {
                $values[$property->key] = $property->getValue();
            }
        })->all();

        return $this->properties->mapWithKeys(function (Property $property) use ($values) {
            return [$property->key => $values[$property->key]];
        })->all();
    }

    /**
     * @param string $property
     * @return bool
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotRegisteredException
     */
    protected function isManyRelationProperty(string $property)
    {
        dd('used');
        $definition = $this->getElementDefinition()->getPropertyDefinition($property);

        if ($definition->getType() !== PropertyType::RELATION) {
            return false;
        }

        return $definition->getMeta('relationType') === RelationType::MANY;
    }

    /**
     * @return ElementDefinition
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     */
    protected function getElementDefinition()
    {
        return elements()->getElementDefinition($this->type);
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
