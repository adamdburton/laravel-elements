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

    // Functions

    /**
     * @return string
     */
    public function getPivotColumnKey()
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
