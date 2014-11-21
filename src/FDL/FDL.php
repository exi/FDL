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
    private $constructFunctions = [];
    private $parser;
    private $persistFunctions = [];
    private $persistedEntities = [];

    public function __construct(Array $sources = [])
    {
        $this->parser = new Parser($sources);
        $this->setDefaultConstructFunction(function ($entityName, $metaData) {
            if (count($metaData) < 1) {
                throw new \Exception('No class name specified for entity ' . $entityName);
            }

            return new $metaData[0]();
        });
        $this->setDefaultPersistFunction(function ($entityName, $metaData, $entity) {
            $this->persistedEntities[] = $entity;
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
            $this->handleParameter($parameterDefinition, $parameter, $realEntity);
        }
        $entity->setRealEntity($realEntity);

        return $realEntity;
    }

    private function handleParameter(ParameterDefinition $parameterDefinition, $parameter, $realEntity)
    {
        if ($parameterDefinition->isAnonymous()) {
            return;
        }

        if ($parameter instanceof Parameter) {
            /** @var Parameter $parameter */
            self::applyOnRealObject($parameterDefinition, $realEntity, $parameter->getData());
        } elseif ($parameter instanceof ReferenceParameter) {
            /** @var ReferenceParameter $parameter */
            /** @var Entity $otherEntity */
            $otherEntity = $this->parser->getEntityByReference($parameter->getReference());
            self::applyOnRealObject($parameterDefinition, $realEntity, $otherEntity->getRealEntity());
        } elseif ($parameter instanceof MultiParameter) {
            /** @var MultiParameter $parameter */
            foreach ($parameter->getReferences() as $reference) {
                $this->handleParameter($parameterDefinition, $reference, $realEntity);
            }
        } elseif ($parameter instanceOf EmptyParameter) {

        } else {
            throw new \Exception('Unknown Parameter type: ' . $parameter);
        }
    }

    private static function applyOnRealObject(ParameterDefinition $parameterDefinition, $realEntity, $data) {
        $realEntity->{$parameterDefinition->getPrefix() . $parameterDefinition->getName()}($data);
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
 