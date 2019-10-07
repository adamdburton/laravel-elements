<?php

namespace Click\Elements\Models;

use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Pivots\EntityProperty;
use Click\Elements\Scopes\ElementScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
                'json_value'
            )
            ->withTimestamps();
    }

    /**
     * @return HasManyThrough
     */
    public function relatedElements()
    {
        return $this->hasManyThrough(
            Entity::class,
            EntityProperty::class,
            'entity_id',
            'id',
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
     * @param string $type
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function toElement(string $type)
    {
        $attributes = $this->properties->mapWithKeys(function (Property $property) {
            $type = $property->pivotColumnKey();
            return [$property->key => $property->pivot->$type];
        })->all();

        $meta = [
            'id' => $this->id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        $relations = $this->relationLoaded('relatedElements') ? $this->relatedElements : null;


        return elements()->factory($type, $attributes, $meta, $relations);
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
