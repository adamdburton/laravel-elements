<?php

namespace Click\Elements\Models;

use Click\Elements\Pivots\EntityProperty;
use Click\Elements\PropertyType;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for storing properties
 */
class Property extends Model
{
    protected $table = 'elements_properties';

    public $timestamps = false;

    protected $fillable = ['key', 'type'];

    // Relationships

    public function elements()
    {
        return $this->belongsToMany(Entity::class, 'elements_entity_properties')
            ->using(EntityProperty::class)
            ->withPivot('bool_value', 'int_value', 'float_value', 'string_value', 'text_value', 'json_value')
            ->withTimestamps();
    }

    // Scopes

    public function scopeKey($query, $property)
    {
        $query->where('key', $property);
    }

    public function scopeType($query, $type)
    {
        $query->where('type', $type);
    }

    // Attributes

    /**
     * @return string
     */
    public function getTypeColumnAttribute()
    {
        $type = $this->type;

        switch ($type) {
            case PropertyType::ARRAY:
                $type = PropertyType::JSON;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::INTEGER;
        }

        return sprintf('%s_value', $type);
    }
}
