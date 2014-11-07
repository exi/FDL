<?php

namespace FDL\Parser;


class EntityDefinition
{
    private $entityName;
    private $className;
    /** @var ParameterDefinition[] */
    private $parameterDefinitions = [];
    private $dependantEntityDefinitions = [];

    public function __construct($entityName, $className)
    {
        $this->entityName = $entityName;
        $this->className = $className;
    }

    public function addParameter(ParameterDefinition $parameterDefinition)
    {
        $this->parameterDefinitions[] = $parameterDefinition;
        if (null !== $parameterDefinition->getEntityType()) {
            $this->dependantEntityDefinitions[] = $parameterDefinition->getEntityType();
            $this->dependantEntityDefinitions = array_unique($this->dependantEntityDefinitions);
        }
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
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

    public function getDependentEntities()
    {
        return $this->dependantEntityDefinitions;
    }

    public function dependsOn(EntityDefinition $entityDefinition)
    {
        return false !== array_search($entityDefinition->getEntityName(), $this->dependantEntityDefinitions);
    }
}
 