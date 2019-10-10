<?php

namespace Click\Elements\Models;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
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

    protected $fillable = ['type'];

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
                'unsigned_integer_value',
                'double_value',
                'string_value',
                'text_value',
                'json_value',
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
     * @throws PropertyNotRegisteredException
     * @throws BindingResolutionException
     */
    public function toElement()
    {
        // Relationships are stored as rows, so sometimes the same property will come up.
        // We need to store these to map back as an array later.

        $properties = [];
        $manyRelations = [];

        foreach ($this->getProperties() as $property) {
            $key = $property->key;
            $column = $property->getPivotColumnKey();

            if ($this->isManyRelationProperty($key)) {
                if (!isset($manyRelations[$key])) {
                    $manyRelations[$key] = [];
                }

                $manyRelations[$key][] = $property->pivot->$column;
                $properties[$key] = $manyRelations[$key];
            } else {
                $properties[$key] = $property->pivot->$column;
            }
        }

        $attributes = collect(array_keys($properties))->mapWithKeys(function ($property) use ($properties) {
            $value = $properties[$property];

            return [$property => $value];
        })->all();

        $meta = $this->getMeta();

        $relations = $this->relationLoaded('relatedElements') ? $this->relatedElements : null;

        return elements()->factory($this->type, $attributes, $meta, $relations);
    }

    /**
     * @return Property[]
     */
    protected function getProperties()
    {
        return $this->properties->keyBy('key')->all();
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

    /**
     * @param string $property
     * @return mixed
     */
    public function getProperty(string $property)
    {
        return $this->properties->where('property', $property)->first();
    }
}
