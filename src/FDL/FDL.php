<?php

namespace FDL;

use FDL\Parser\EmptyParameter;
use FDL\Parser\Entity;
use FDL\Parser\EntityDefinition;
use FDL\Parser\MultiParameter;
use FDL\Parser\Parameter;
use FDL\Parser\ParameterDefinition;
use FDL\Parser\ReferenceParameter;

class FDL
{
    const DEFAULT_PERSIST_FUNCTION_KEY = '__default__';
    const DEFAULT_CONSTRUCT_FUNCTION_KEY = '__default__';
    const DEFAULT_PARAMETER_FUNCTION_KEY = '__default__';
    private $constructFunctions = [];
    private $parser;
    private $persistFunctions = [];
    private $parameterFunctions = [];

    public function __construct(Array $sources = [])
    {
        $this->parser = new Parser($sources);
        $this->setDefaultConstructFunction(function ($entityName, $metaData) {
            if (count($metaData) < 1) {
                throw new \Exception('No class name specified for entity ' . $entityName);
            }

            return new $metaData[0]();
        });
        $this->setDefaultPersistFunction(function ($entityName, $metaData, $entity) { });
        $this->setDefaultParameterFunction(function (
            EntityDefinition $entityDefinition,
            ParameterDefinition $parameterDefinition,
            $realEntity,
            $data
        ) {
            $this->defaultParameterFunction(
                $entityDefinition,
                $parameterDefinition,
                $realEntity,
                $data
            );
        });
    }

    public function addConstructFunction($entityName, $constructFunction)
    {
        $this->constructFunctions[$entityName] = $constructFunction;
    }

    public function setDefaultConstructFunction($constructFunction)
    {
        $this->constructFunctions[self::DEFAULT_CONSTRUCT_FUNCTION_KEY] = $constructFunction;
    }

    public function addParameterFunction($entityName, $parameterName, $parameterFunction)
    {
        $this->parameterFunctions[$entityName][$parameterName] = $parameterFunction;
    }

    public function setDefaultParameterFunction($parameterFunction)
    {
        $this->parameterFunctions[self::DEFAULT_PARAMETER_FUNCTION_KEY] = $parameterFunction;
    }

    public function setDefaultPersistFunction($persistFunction)
    {
        $this->persistFunctions[self::DEFAULT_PERSIST_FUNCTION_KEY] = $persistFunction;
    }

    public function addPersistFunction($entityName, $persistFunction)
    {
        $this->persistFunctions[$entityName] = $persistFunction;
    }

    public function run()
    {
        foreach ($this->parser->getDependencyOrder() as $entityType) {
            $entityDefinition = $this->parser->getEntityDefinitionByName($entityType);
            $entities = $this->parser->getEntitiesByName($entityDefinition->getEntityName());
            foreach ($entities as $entity) {
                /** @var Entity $entity */
                $realEntity = $this->constructRealEntity($entityDefinition, $entity);
                $metaData = $entityDefinition->getMetaData();
                $persister = $this->getPersistFunction($entityDefinition->getEntityName(), $metaData);
                $persister($entityDefinition->getEntityName(), $metaData, $realEntity);
            }
        }

        return array_map(function(Entity $entity) {
            return $entity->getRealEntity();
        }, $this->parser->getEntities());
    }

    private function constructRealEntity(EntityDefinition $entityDefinition, Entity $entity)
    {
        $entityName = $entityDefinition->getEntityName();
        $metaData = $entityDefinition->getMetaData();
        $realEntity = $this->constructEntity($entityName, $metaData);
        foreach ($entity->getParameters() as $idx => $parameter) {
            $parameterDefinition = $entityDefinition->getParameterDefinitionForIdx($idx);
            $this->handleParameter($entityDefinition, $parameterDefinition, $parameter, $realEntity);
        }
        $entity->setRealEntity($realEntity);

        return $realEntity;
    }

    private function handleParameter(
        EntityDefinition $entityDefinition,
        ParameterDefinition $parameterDefinition,
        $parameter,
        $realEntity
    ) {
        if ($parameterDefinition->isAnonymous()) {
            return;
        }

        if ($parameter instanceof Parameter) {
            /** @var Parameter $parameter */
            $this->applyOnRealObject($entityDefinition, $parameterDefinition, $realEntity, $parameter->getData());
        } elseif ($parameter instanceof ReferenceParameter) {
            /** @var ReferenceParameter $parameter */
            /** @var Entity $otherEntity */
            $otherEntity = $this->parser->getEntityByReference($parameter->getReference());
            $this->applyOnRealObject($entityDefinition, $parameterDefinition, $realEntity, $otherEntity->getRealEntity());
        } elseif ($parameter instanceof MultiParameter) {
            /** @var MultiParameter $parameter */
            foreach ($parameter->getParameters() as $reference) {
                $this->handleParameter($entityDefinition, $parameterDefinition, $reference, $realEntity);
            }
        } elseif ($parameter instanceOf EmptyParameter) {
            /** Ignore it */
        } else {
            throw new \Exception('Unknown Parameter type: ' . $parameter);
        }
    }

    private function applyOnRealObject(
        EntityDefinition $entityDefinition,
        ParameterDefinition $parameterDefinition,
        $realEntity,
        $data
    ) {
        $entityName = $entityDefinition->getEntityName();
        $parameterName = $parameterDefinition->getName();
        if (
            array_key_exists($entityName, $this->parameterFunctions) &&
            array_key_exists($parameterName, $this->parameterFunctions[$entityName])
        ) {
            $this->parameterFunctions[$entityName][$parameterName](
                $entityDefinition,
                $parameterDefinition,
                $realEntity,
                $data
            );
        } else {
            $this->parameterFunctions[self::DEFAULT_PARAMETER_FUNCTION_KEY](
                $entityDefinition,
                $parameterDefinition,
                $realEntity,
                $data
            );
        }
    }

    private function defaultParameterFunction(
        EntityDefinition $entityDefinition,
        ParameterDefinition $parameterDefinition,
        $realEntity,
        $data
    ) {
        $realData = $data;
        if (is_string($data)) {
            eval('$realData = ' . $data . ';');
        }
        $realEntity->{$parameterDefinition->getPrefix() . $parameterDefinition->getName()}($realData);
    }

    private function constructEntity($entityName, $metaData)
    {
        $constructor = $this->getConstructFunction($entityName);
        return $constructor($entityName, $metaData);
    }

    private function getConstructFunction($type)
    {
        if (array_key_exists($type, $this->constructFunctions)) {
            return $this->constructFunctions[$type];
        }

        return $this->constructFunctions[self::DEFAULT_CONSTRUCT_FUNCTION_KEY];
    }

    private function getPersistFunction($type)
    {
        if (array_key_exists($type, $this->persistFunctions)) {
            return $this->persistFunctions[$type];
        }

        return $this->persistFunctions[self::DEFAULT_PERSIST_FUNCTION_KEY];
    }
}
 