<?php

namespace Click\Elements\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot for storing entity properties
 */
class EntityProperty extends Pivot
{
    public $incrementing = true;

    protected $table = 'elements_entity_properties';

    protected $dates = [
        'timestamp_value'
    ];

    protected $casts = [
        'boolean_value' => 'boolean',
        'integer_value' => 'integer',
        'double_value' => 'double',
        'string_value' => 'string',
        'text_value' => 'string',
        'json_value' => 'array'
    ];

    // Relationships

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Attributes

    public function getValueAttribute()
    {
//        return elements()->elements()->castProperty($this->property->type, $this->attributes);

        // get

//        switch ($this->property->type) {
//            case 'bool':
//                return boolval($this->bool_value);
//            case 'int':
//                return intval($this->int_value);
//            case 'float':
//                return floatval($this->float_value);
//            case 'string':
//                return strval($this->string_value);
//            case 'text':
//                return strval($this->text_value);
//            case 'array':
//                return $this->json_value ? json_decode($this->json_value, true) : [];
//            case 'object':
//                return $this->json_value ? json_decode($this->json_value) : new \stdClass();
//            case 'relation':
//                return $this->int_value ? elements()->getById($this->int_value) : null;
//            default:
//                return 'INVALID';
//        }


        // set

//        switch ($property->type) {
//            case 'bool':
//                $pivot = ['bool_value' => boolval($value)];
//                break;
//            case 'int':
//                $pivot = ['int_value' => intval($value)];
//                break;
//            case 'float':
//                $pivot = ['float_value' => floatval($value)];
//                break;
//            case 'text':
//            case 'string':
//            $pivot = ['string_value' => strval($value)];
//                break;
//            case 'array':
//                $pivot = ['json_value' => json_encode(array_values(is_array($value) ? $value : [$value]))];
//                break;
//            case 'object':
//                $pivot = ['json_value' => json_encode(is_array($value) ? $value : [$value])];
//                break;
//            case 'relation':
//                $pivot = ['int_value' => intval($value->id)];
//                break;
//            default:
//                throw new PropertyTypeInvalidException($property->type);
//        }
    }
}
