<?php

namespace Click\Elements\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable = ['type_id'];

    // Relationships

    public function entityType()
    {
        return $this->belongsTo(EntityType::class, 'type_id');
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'entity_properties')
            ->using(EntityProperty::class)
            ->withPivot('bool_value', 'int_value', 'float_value', 'string_value', 'text_value', 'json_value')
            ->withTimestamps();
    }

    // Scopes

    public function scopeType($query, $type)
    {
        $query->whereHas('entityType', function ($query) use ($type) {
            $query->where('type', $type);
        });
    }

    public function scopeWhereProperty($query, $property, $operator = '', $value = null)
    {
        $query->whereHas('properties', function ($query) use ($property, $operator, $value) {
//            $prop = elements()->properties()->getProperty($property);
//
//            $query
//                ->where('property_id', $prop->id)
//                ->where($prop->type . '_value', $value ? $operator : '=', $value ?? $operator);
        });
    }

    public function scopeWherePropertyIn($query, $property, $values)
    {
        $query->whereHas('properties', function ($query) use ($property, $values) {
//            $prop = elements()->properties()->getProperty($property);
//
//            $query
//                ->where('property_id', $prop->id)
//                ->whereIn($prop->type . '_value', $values);
        });
    }

    // Methods

    public function getProperty(string $property)
    {
        return $this->properties->where('property', $property)->first();
    }
}
