<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Types\PropertyType;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Carbon;

/**
 * @method ElementDefinition getElementDefinition()
 * @property Element $element;
 */
trait MocksElements
{
    /**
     * @param array $mergeAttributes
     * @param null $generator
     * @return array
     */
    public static function mock($mergeAttributes = [], $generator = null)
    {
        $instance = new static;
        $attributes = array_merge($instance->fake($generator ?: Factory::create()), $mergeAttributes);

        return $instance->setAttributes($attributes);
    }

    /**
     * @param Generator $faker
     * @return array
     */
    protected function fake(Generator $faker)
    {
        $properties = $this->getElementDefinition()->getPropertyDefinitions();

        return collect($properties)->map(function (PropertyDefinition $definition) use ($faker) {
            return $this->fakeProperty($definition, $faker);
        })->all();
    }

    /**
     * @param PropertyDefinition $definition
     * @param Generator $faker
     * @return array|string
     */
    protected function fakeProperty(PropertyDefinition $definition, Generator $faker)
    {
        $type = $definition->getType();

        switch ($type) {
            case PropertyType::STRING:
                return <<<EOL
Vestibulum id ligula porta felis euismod semper. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.
EOL;
            case PropertyType::BOOLEAN:
                return true;
            case PropertyType::INTEGER:
                return PHP_INT_MIN;
            case PropertyType::UNSIGNED_INTEGER:
                return PHP_INT_MAX;
            case PropertyType::DOUBLE:
                return (double)(rand(0, 9999) . rand(0, 99));
            case PropertyType::TEXT:
                return <<<EOL
Nullam quis risus eget urna mollis ornare vel eu leo. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lacinia bibendum nulla sed consectetur. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec ullamcorper nulla non metus auctor fringilla. Maecenas faucibus mollis interdum. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Aenean lacinia bibendum nulla sed consectetur. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam porta sem malesuada magna mollis euismod. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Cras mattis consectetur purus sit amet fermentum.
EOL;
            case PropertyType::ARRAY:
                return ['one', 'two', 'three', 'four'];
            case PropertyType::JSON:
                return ['a' => ['b', 'c' => ['d' => ['e' => 'f']]]];
            case PropertyType::RELATION:
                return 1;
            case PropertyType::TIMESTAMP:
                return Carbon::createFromDate(2000, 1, 1)->startOfDay();
            default:
//                throw new \Exception('missing fake property generator: ' . $type);
        }
    }
}
