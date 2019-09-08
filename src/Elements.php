<?php

namespace Click\Elements;

use Click\Elements\Services\ElementService;
use Click\Elements\Services\Entities\EntityService;
use Click\Elements\Services\Entities\PropertyService;

class Elements
{
    /** @var EntityService */
    private $entityService;

    /** @var PropertyService */
    private $propertyService;

    /** @var ElementService */
    protected $elementService;

    public function __construct(EntityService $entityService, PropertyService $propertyService)
    {
        $this->entityService = $entityService;
        $this->propertyService = $propertyService;
    }

    /**
     * @return EntityService
     */
    public function entities()
    {
        return $this->entityService;
    }

    /**
     * @return PropertyService
     */
    public function properties()
    {
        return $this->propertyService;
    }

    /**
     * @return ElementService
     */
//    public function elements()
//    {
//        return $this->elementService;
//    }
}
