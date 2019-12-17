<?php

namespace Click\Elements\Models;

use Click\Elements\Pivots\Value;
use Click\Elements\Types\AttributeType;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for storing properties
 * @property int id
 * @property string type
 * @property string key
 * @property Value pivot
 */
class Attribute extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'elements_attributes';

    /**
     * @var array
     */
    protected $fillable = ['element', 'key', 'type'];

    // Functions

    /**
     * @return mixed
     */
    public function getEntityAttributeValue()
    {
        return $this->pivot->{$this->getEntityAttributeKey()};
    }

    /**
     * @return string
     */
    public function getEntityAttributeKey()
    {
        $type = $this->type;

        switch ($type) {
            case AttributeType::ARRAY:
                $type = AttributeType::JSON;
                break;
            case AttributeType::RELATION:
                $type = AttributeType::UNSIGNED_INTEGER;
                break;
        }

        return sprintf('%s_value', $type);
    }
}
