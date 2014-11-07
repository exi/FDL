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
    private $realEntities = [];

    public function __construct(Array $sources = [])
    {
        $this->parser = new Parser($sources);
        $this->setDefaultConstructFunction(function ($type) {
            return new $type();
        });
        $this->setDefaultPersistFunction(function ($type, $entity) {
            $this->realEntities[] = $entity;
        });
    }

    public function addConstructFunction($type, $constructFunction)
    {
        $this->constructFunctions[$type] = $constructFunction;
    }

    public function setDefaultConstructFunction($constructFunction)
    {
        $this->constructFunctions[self::DEFAULT_CONSTRUCT_FUNCTION_KEY] = $constructFunction;
    }

    public function setDefaultPersistFunction($persistFunction)
    {
        $this->persistFunctions[self::DEFAULT_PERSIST_FUNCTION_KEY] = $persistFunction;
    }

    public function addPersistFunction($type, $persistFunction)
    {
        $this->persistFunctions[$type] = $persistFunction;
    }

    public function run()
    {
        foreach ($this->parser->getDependencyOrder() as $entityType) {
            $entityDefinition = $this->parser->getEntityDefinitionByName($entityType);
            $entities = $this->parser->getEntitiesByName($entityDefinition->getEntityName());
            foreach ($entities as $entity) {
                /** @var Entity $entity */
                $realEntity = $this->constructRealEntity($entityDefinition, $entity);
                $type = $entityDefinition->getClassName();
                $persister = $this->getPersistFunction($type);
                $persister($type, $realEntity);
            }
        }
        print_r($this->realEntities);
    }

    private function constructRealEntity(EntityDefinition $entityDefinition, Entity $entity)
    {
        $classType = $entityDefinition->getClassName();
        $realEntity = $this->constructEntity($classType);
        foreach ($entity->getParameters() as $idx => $parameter) {
            $parameterDefinition = $entityDefinition->getParameterDefinitionForIdx($idx);
            $this->handleParameter($parameterDefinition, $parameter, $realEntity);
        }
        $entity->setRealEntity($realEntity);

        return $realEntity;
    }

    private function handleParameter(ParameterDefinition $parameterDefinition, $parameter, $realEntity)
    {
        if (null === $parameterDefinition->getName()) {
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

    private function constructEntity($type)
    {
        $constructor = $this->getConstructFunction($type);
        return $constructor($type);
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
 