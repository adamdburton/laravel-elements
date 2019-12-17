<?php

namespace Click\Elements\Models;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Pivots\Value;
use Click\Elements\Types\AttributeType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model for storing entities
 * @property int id
 * @property string type
 * @property Collection attributeValues
 * @property Collection relatedEntities
 * @property Collection reverseRelatedEntities
 */
class Entity extends Model
{
    public $timestamps = false;

    protected $table = 'elements_entities';

    protected $fillable = ['type'];

    // Relationships

    /**
     * @return BelongsToMany
     */
    public function attributeValues() // attributes is taken :<
    {
        return $this->belongsToMany(Attribute::class, 'elements_values')
            ->using(Value::class)
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
    public function relatedEntities()
    {
        return $this->belongsToMany(
            Entity::class,
            'elements_values',
            'entity_id',
            'unsigned_integer_value'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function reverseRelatedEntities()
    {
        return $this->belongsToMany(
            Entity::class,
            'elements_values',
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
     * @param ElementDefinition $definition
     * @return Attribute[]
     */
    public function getEntityAttributeValues(ElementDefinition $definition)
    {
        $d = $this->attributeValues
            ->mapToDictionary(function (Attribute $attribute) {
                return [$attribute->key => $attribute->getEntityAttributeValue()];
            })
            ->mapWithKeys(function ($values, $key) use ($definition) {
                $attribute = $definition->getAttributeDefinition($key);

                return [$key => $attribute->getType() === AttributeType::RELATION ? $values : $values[0]];
            })
            ->all();

        return $d;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'id' => $this->id,
            'type' => $this->type
        ];
    }
}
