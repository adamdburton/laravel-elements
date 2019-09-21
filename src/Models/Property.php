<?php

namespace Click\Elements\Models;

use Click\Elements\Element;
use Click\Elements\ElementType;
use Click\Elements\PropertyType;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'elements_properties';

    public $timestamps = false;

    protected $fillable = ['property', 'type'];

    // Relationships

    public function elements()
    {
        return $this->belongsToMany(Entity::class, 'elements_entity_properties')
            ->using(EntityProperty::class)
            ->withPivot('bool_value', 'int_value', 'float_value', 'string_value', 'text_value', 'json_value')
            ->withTimestamps();
    }

    // Scopes

    public function scopeProperty($query, $property)
    {
        $query->where('property', $property);
    }

    public function scopeType($query, $type)
    {
        $query->where('type', $type);
    }

    // Attributes

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
