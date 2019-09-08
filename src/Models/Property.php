<?php

namespace Click\Elements\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = ['property', 'type'];

    public $timestamps = false;

    // Relationships

    public function elements()
    {
        return $this->belongsToMany(Entity::class, 'element_properties')
            ->using(EntityProperty::class)
            ->withPivot('bool_value', 'int_value', 'float_value', 'string_value', 'text_value', 'json_value');
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
}
