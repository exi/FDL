<?php

namespace FDL;

class FDL
{
    const DEFAULT_PERSIST_FUNCTION_KEY = '__default__';
    private $parser;
    private $persistFunctions = [];

    public function __construct(Array $sources = [])
    {
        $this->parser = new Parser($sources);
    }

    public function addDefaultPersistFunction($persistFunction)
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
            $entityDefinition = $this->parser->getEntityDefinitionByType($entityType);
            $entities = $this->parser->getEntitiesByName($entityDefinition->getEntityName());
            print_r($entities);
        }
    }
}
 