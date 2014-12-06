<?php

namespace FDL;


use FDL\Parser\EmptyParameter;
use FDL\Parser\Entity;
use FDL\Parser\EntityDefinition;
use FDL\Parser\ReferenceParameter;
use FDL\Parser\MultiParameter;
use FDL\Parser\Parameter;
use FDL\Parser\ParameterDefinition;
use FDL\Parser\Util;

class Parser
{
    const PARSER_PASS_DEFINITIONS = 1;
    const PARSER_PASS_ENTITIES = 2;
    const DUMMY_ENTITY_NAME = '__DUMMY_ENTITY__';
    private $dependencyOrder;
    /** @var EntityDefinition[] */
    private $entityDefinitions = [];
    private $files;
    private $lineCache = [];
    private $lines;
    private $parserPass;
    private $position = 0;
    private $referenceCounter = 0;
    /** @var Entity[] */
    private $references = [];

    public function __construct($sourceFiles = [])
    {
        $this->files = $sourceFiles;
        $this->computeFixtures();
    }

    public function computeFixtures()
    {
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            $this->lineCache[$file] = explode("\n", $content);
        }

        foreach ($this->files as $file) {
            $this->lines = $this->lineCache[$file];
            $this->position = 0;

            $this->parserPass = self::PARSER_PASS_DEFINITIONS;
            while (!$this->isEOF()) {
                $this->parseMainBlock();
            }
        }

        foreach ($this->files as $file) {
            $this->lines = $this->lineCache[$file];
            $this->position = 0;

            $this->parserPass = self::PARSER_PASS_ENTITIES;
            while (!$this->isEOF()) {
                $this->parseMainBlock();
            }
        }

        $entityDefinitions = array_values($this->getEntityDefinitions());
        usort($entityDefinitions, function ($e1, $e2) {
            /** @var EntityDefinition $e1 */
            /** @var EntityDefinition $e2 */
            if ($e1->dependsOn($e2)) {
                return 1;
            } elseif ($e2->dependsOn($e1)) {
                return -1;
            } else {
                return 0;
            }
        });

        $this->dependencyOrder = array_map(function (EntityDefinition $entityDefinition) {
            return $entityDefinition->getEntityName();
        }, $entityDefinitions);
    }

    /**
     * @return string[]
     */
    public function getDependencyOrder()
    {
        return $this->dependencyOrder;
    }

    /**
     * @return Entity[]
     */
    public function getEntities()
    {
        return array_values($this->references);
    }

    public function getEntitiesByName($name)
    {
        return array_filter($this->getEntities(), function (Entity $entity) use ($name) {
            return $entity->getEntityName() === $name;
        });
    }

    /**
     * @param $type
     * @return EntityDefinition
     */
    public function getEntityDefinitionByName($type)
    {
        return $this->entityDefinitions[$type];
    }

    /**
     * @return EntityDefinition[]
     */
    public function getEntityDefinitions()
    {
        return $this->entityDefinitions;
    }

    /**
     * @return Entity[]
     */
    public function getReferences()
    {
        return $this->references;
    }

    private function addEntityDefinition($entityName, $entityDefinition)
    {
        $this->entityDefinitions[$entityName] = $entityDefinition;
    }

    private function addReference($reference, $entity)
    {
        $this->references[$reference] = $entity;
    }

    private function saveEntity(Entity $entity)
    {
        if ($entity->hasReference()) {
            $reference = $entity->getReference();
        } else {
            $reference = $this->nextReference();
            $entity->setReference($reference);
        }

        $this->addReference($reference, $entity);
        return new ReferenceParameter($reference);
    }

    private function indent()
    {
        $matches = null;
        preg_match('/^\s*/', $this->line(), $matches);

        return mb_strlen($matches[0]);
    }

    private function isContinueMarker()
    {
        return '-' !== $this->parts()[0];
    }

    private function isEOF()
    {
        return $this->position >= count($this->lines);
    }

    private function isEmpty()
    {
        return 1 === preg_match('/^\s*$/', $this->line());
    }

    private function isEmptyMarker()
    {
        return '^' === $this->parts()[0];
    }

    private function isMultiMarker()
    {
        return ':' === $this->parts()[0];
    }

    private function line()
    {
        return $this->lines[$this->position];
    }

    private function lineValue()
    {
        return ltrim($this->line());
    }

    private function next()
    {
        $this->position++;
    }

    private function nextReference()
    {
        return Util::toReferenceName(self::DUMMY_ENTITY_NAME, (string)$this->referenceCounter++);
    }

    private function parseEntity($entityName = null, $adHocReference = null)
    {
        if (null === $entityName) {
            $line = $this->lineValue();
            $matchResult = preg_match('/^([^ ]+)( (.*)$)?/', $line, $matches);
            if (0 === $matchResult or false === $matchResult) {
                throw new \Exception('Missing entity type');
            }
            $entityName = $matches[1];

            if (4 === count($matches)) {
                $adHocReference = $matches[3];
            }

            $this->next();
        }

        $entity = new Entity($entityName);

        $this->parseEntityParameters($entity);

        if (null !== $adHocReference) {
            $entity->setReference(Util::toReferenceName($entityName, $adHocReference));
        }

        return $entity;
    }

    private function parseEntityDefinition()
    {
        $indent = $this->indent();
        $parts = $this->parts();
        $entityName = substr($parts[0], 1);
        array_shift($parts);
        $metaData = $parts;
        $entityDefinition = new EntityDefinition($entityName, $metaData);

        $this->next();
        while (!$this->isEOF() && $indent < $this->indent()) {
            $entityDefinition->addParameter($this->parseEntityParameterDefinition());
            $this->next();
        }

        return $entityDefinition;
    }

    private function parseEntityMultiParameter(ParameterDefinition $parameterDefinition)
    {

        $multiParameter = new MultiParameter();

        if ($this->isEmptyMarker()) {
            $this->next();
            return $multiParameter;
        }

        if (!$this->isMultiMarker()) {
            throw new \Exception('No multi definition found');
        }

        $this->next();
        $indent = $this->indent();

        while (!$this->isEOF() && $indent === $this->indent()) {
            if ($parameterDefinition->isTyped()) {
                $parameter = $this->parseTypedEntityParameter($parameterDefinition);
            } else {
                $parameter = $this->lineValue();
                $this->next();
            }
            $multiParameter->addParameter($parameter);

            $this->next();
            if ($this->isEOF() || !$this->isContinueMarker()) {
                break;
            }
        }

        return $multiParameter;
    }

    private function parseEntityParameter()
    {
        return new Parameter($this->lineValue());
    }

    private function parseEntityParameterDefinition()
    {
        $parts = $this->parts();
        $name = array_shift($parts);

        if ('!' === $name) {
            $parts[] = $name;
            $name = null;
        }

        return new ParameterDefinition($name, $parts);
    }

    private function parseTypedEntityParameter(ParameterDefinition $parameterDefinition)
    {
        if ($this->isMultiMarker()) {
            $line = $this->lineValue();

            $adHocReference = null;
            if (mb_strlen($line) > 2) {
                $adHocReference = substr($line, 2);
            }

            $this->next();
            $subEntity = $this->parseEntity($parameterDefinition->getEntityType(), $adHocReference);
            $parameter = $this->saveEntity($subEntity);
        } elseif ($this->isEmptyMarker()) {
            $parameter = new EmptyParameter();
            $this->next();
        } else {
            $parameter = $this->parseEntityReference($parameterDefinition);
            $this->next();
        }
        return $parameter;
    }

    private function parseEntityParameters(Entity $entity)
    {
        foreach ($this->getEntityDefinitionByName($entity->getEntityName())->getParameterDefinitions() as $parameterDefinition) {
            if ($parameterDefinition->isMulti()) {
                $multi = $this->parseEntityMultiParameter($parameterDefinition);
                $entity->addParameter($multi);
            } elseif ($parameterDefinition->isTyped()) {
                $entity->addParameter($this->parseTypedEntityParameter($parameterDefinition));
            } else {
                if ($this->isEmptyMarker()) {
                    $parameter = new EmptyParameter();
                } else {
                    $parameter = $this->parseEntityParameter();
                }
                $entity->addParameter($parameter);

                if ($parameterDefinition->isReference()) {
                    $entity->setReference(Util::toReferenceName($entity->getEntityName(), $parameter->getData()));
                }

                $this->next();
            }
        }

        return $entity;
    }

    private function parseEntityReference(ParameterDefinition $parameterDefinition)
    {
        return new ReferenceParameter(
            Util::toReferenceName($parameterDefinition->getEntityType(), $this->lineValue())
        );
    }

    private function parseMainBlock()
    {
        $this->skipBlanks();
        if ($this->isEOF()) {
            return;
        }

        $parts = $this->parts();

        switch ($parts[0][0]) {
            case '.':
                if (self::PARSER_PASS_DEFINITIONS === $this->parserPass) {
                    $entityDefinition = $this->parseEntityDefinition();
                    $this->addEntityDefinition($entityDefinition->getEntityName(), $entityDefinition);
                } else {
                    $this->skipBlock();
                }
                break;
            default:
                if (self::PARSER_PASS_ENTITIES === $this->parserPass) {
                    $entity = $this->parseEntity();
                    $this->saveEntity($entity);
                } else {
                    $this->skipBlock();
                }
                break;
        }
    }

    private function parts()
    {
        return preg_split('/\s/', $this->lineValue(), -1, PREG_SPLIT_NO_EMPTY);
    }

    private function skipBlanks()
    {
        while (!$this->isEOF() && $this->isEmpty()) {
            $this->next();
        }
    }

    private function skipBlock()
    {
        $indent = $this->indent();
        $this->next();
        while (!$this->isEOF() && $this->indent() > $indent) {
            $this->next();
        }
    }

    /**
     * @param $reference
     * @return Entity
     */
    public function getEntityByReference($reference)
    {
        return $this->references[$reference];
    }
}
 