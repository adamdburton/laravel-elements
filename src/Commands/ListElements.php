<?php

namespace Click\Elements\Commands;

use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Elements;
use Illuminate\Console\Command;

/**
 * php artisan elements:list
 */
class ListElements extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elements:list';

    /**
     * @var string
     */
    protected $description = 'Shows currently registered and installed elements and their properties.';

    /**
     * @var Elements
     */
    protected $elements;

    /**
     * @param Elements $elements
     */
    public function __construct(Elements $elements)
    {
        parent::__construct();

        $this->elements = $elements;
    }

    public function handle()
    {
        $headers = [
            'Alias',
            'Class',
            'Attributes',
            'Installed?'
        ];

        $rows = [];

        foreach ($this->elements->getElementDefinitions() as $definition) {
            $rows[$definition->getAlias()] = [
                $definition->getAlias(),
                $definition->getClass(),
                collect($definition->getAttributeDefinitions())->map(function (AttributeDefinition $attributeDefinition) {
                    return sprintf('%s (%s)', $attributeDefinition->getKey(), $attributeDefinition->getType());
                })->join("\n"),
                $definition->isInstalled() ? 'Yes' : 'No'
            ];
        }

        ksort($rows);

        $this->table($headers, $rows);
    }
}
