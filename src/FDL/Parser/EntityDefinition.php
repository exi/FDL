<?php

namespace FDL\Parser;


class EntityDefinition
{
    private $entityName;
    private $metaData;
    /** @var ParameterDefinition[] */
    private $parameterDefinitions = [];
    private $dependantEntityTypes = [];

    public function __construct($entityName, Array $metaData = [])
    {
        $this->entityName = $entityName;
        $this->metaData = $metaData;
    }

    public function addParameter(ParameterDefinition $parameterDefinition)
    {
        $this->parameterDefinitions[] = $parameterDefinition;
        if (null !== $parameterDefinition->getEntityType()) {
            $this->dependantEntityTypes[] = $parameterDefinition->getEntityType();
            $this->dependantEntityTypes = array_unique($this->dependantEntityTypes);
        }
    }

    /**
     * @return mixed
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return ParameterDefinition[]
     */
    public function getParameterDefinitions()
    {
        return $this->parameterDefinitions;
    }

    /**
     * @param $idx
     * @return ParameterDefinition
     */
    public function getParameterDefinitionForIdx($idx)
    {
        return $this->parameterDefinitions[$idx];
    }

    public function getParameterCount()
    {
        return count($this->parameterDefinitions);
    }

    /**
     * @return array
     */
    public function getDependantEntityTypes()
    {
        return $this->dependantEntityTypes;
    }
}
