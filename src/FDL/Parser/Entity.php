<?php

namespace FDL\Parser;


class Entity
{
    private $entityName;

    private $parameters = [];

    private $realEntity;

    function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    public function addParameter($parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return mixed
     */
    public function getRealEntity()
    {
        return $this->realEntity;
    }

    /**
     * @param mixed $realEntity
     */
    public function setRealEntity($realEntity)
    {
        $this->realEntity = $realEntity;
    }
}
 