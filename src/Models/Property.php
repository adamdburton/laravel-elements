<?php

namespace Click\Elements\Models;

use Click\Elements\Pivots\EntityProperty;
use Click\Elements\Types\PropertyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model for storing properties
 * @property int id
 * @property string type
 * @property string key
 * @property EntityProperty pivot
 */
class Property extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'elements_properties';

    /**
     * @var array
     */
    protected $fillable = ['element', 'key', 'type'];

    // Relationships

    /**
     * @return BelongsToMany
     */
    public function elements()
    {
        return $this->belongsToMany(Entity::class, 'elements_entity_properties')
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

    // Scopes

    public function scopeElement($query, string $element)
    {
        $query->where('element', $element);
    }

    public function scopeKey($query, string $property)
    {
        $query->where('key', $property);
    }

    public function scopeType($query, string $type)
    {
        $query->where('type', $type);
    }

    // Functions

    /**
     * @return string
     */
    public function pivotColumnKey()
    {
        $type = $this->type;

        switch ($type) {
            case PropertyType::ARRAY:
                $type = PropertyType::JSON;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::UNSIGNED_INTEGER;
        }

        return sprintf('%s_value', $type);
    }
}
