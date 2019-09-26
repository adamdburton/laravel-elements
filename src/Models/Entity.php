<?php

namespace Click\Elements\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $table = 'elements_entities';

    // Relationships

    public function parents()
    {
        return $this->belongsToMany(Entity::class, 'element_entity_relations', 'child_entity_id', 'parent_entity_id')
            ->withPivot('property_id');
    }

    public function children()
    {
        return $this->belongsToMany(Entity::class, 'element_entity_relations', 'parent_entity_id', 'child_entity_id')
            ->withPivot('property_id');
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'elements_entity_properties')
            ->using(EntityProperty::class)
            ->withPivot('boolean_value', 'integer_value', 'double_value', 'string_value', 'text_value', 'json_value')
            ->withTimestamps();
    }

    // Scopes

    public function scopeWhereHasProperty($query, Property $property, $operator = '', $value = null)
    {
        $query->whereHas('properties', function ($query) use ($property, $operator, $value) {
            $query
                ->where('property_id', $property->id)
                ->where($property->type . '_value', $value ? $operator : '=', $value ?? $operator);
        });
    }

    public function scopeWhereHasPropertyIn($query, $property, $values)
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
