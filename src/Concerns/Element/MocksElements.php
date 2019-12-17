<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Types\AttributeType;
use Exception;
use Illuminate\Support\Carbon;

/**
 * @method ElementDefinition getElementDefinition()
 * @property Element $element;
 */
trait MocksElements
{
    /**
     * @param array $mergeAttributes
     * @return array
     */
    public static function mock($mergeAttributes = [])
    {
        $instance = new static;
        $attributes = array_merge($instance->fake(), $mergeAttributes);

        return $instance->setAttributes($attributes);
    }

    /**
     * @return array
     */
    protected function fake()
    {
        $properties = $this->getElementDefinition()->getAttributeDefinitions();

        return collect($properties)->map(function (AttributeDefinition $definition) {
            return $this->fakeAttribute($definition);
        })->all();
    }

    /**
     * @param AttributeDefinition $definition
     * @return array|string
     */
    protected function fakeAttribute(AttributeDefinition $definition)
    {
        $type = $definition->getType();

        switch ($type) {
            case AttributeType::STRING:
                return <<<EOL
Vestibulum id ligula porta felis euismod semper. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.
EOL;
            case AttributeType::BOOLEAN:
                return true;
            case AttributeType::INTEGER:
                return PHP_INT_MIN;
            case AttributeType::UNSIGNED_INTEGER:
                return PHP_INT_MAX;
            case AttributeType::DOUBLE:
                return (double)(rand(0, 9999) . rand(0, 99));
            case AttributeType::TEXT:
                return <<<EOL
Nullam quis risus eget urna mollis ornare vel eu leo. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lacinia bibendum nulla sed consectetur. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec ullamcorper nulla non metus auctor fringilla. Maecenas faucibus mollis interdum. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Aenean lacinia bibendum nulla sed consectetur. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam porta sem malesuada magna mollis euismod. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Cras mattis consectetur purus sit amet fermentum.
EOL;
            case AttributeType::ARRAY:
                return ['one', 'two', 'three', 'four'];
            case AttributeType::JSON:
                return ['a' => ['b', 'c' => ['d' => ['e' => 'f']]]];
            case AttributeType::RELATION:
                return 1;
            case AttributeType::TIMESTAMP:
                return Carbon::createFromDate(2000, 1, 1)->startOfDay();
            default:
                throw new Exception('missing fake attribute generator: ' . $type);
        }
    }
}
