<?php

namespace Click\Elements;

use Click\Elements\Services\ElementService;
use Click\Elements\Services\PropertyService;

class Elements
{
    /** @var ElementService */
    private $elementService;

    /** @var PropertyService */
    private $propertyService;

    public function __construct(ElementService $elementService, PropertyService $propertyService)
    {
        $this->elementService = $elementService;
        $this->propertyService = $propertyService;
    }

    /**
     * @return ElementService
     */
    public function elements()
    {
        return $this->elementService;
    }

    /**
     * @return PropertyService
     */
    public function properties()
    {
        return $this->propertyService;
    }
}
