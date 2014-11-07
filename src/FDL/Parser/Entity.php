<?php

namespace FDL\Parser;


class Entity
{
    private $entityName;

    private $data = [];

    function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    public function addParameter($parameter)
    {
        $this->data[] = $parameter;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }
}
 