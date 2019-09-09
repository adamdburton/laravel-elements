<?php

namespace Click\Elements\Models;

use Illuminate\Database\Eloquent\Model;

class EntityType extends Model
{
    public $timestamps = false;
    protected $fillable = ['type', 'class'];

    // Relationships

    public function entities()
    {
        return $this->hasMany(Entity::class, 'type_id');
    }

    // Scopes

    public function scopeType($query, $type)
    {
        $query->where('type', $type);
    }
}
