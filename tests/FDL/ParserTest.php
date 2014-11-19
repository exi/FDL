<?php
namespace FDL;

use FDL\Parser\Entity;
use FDL\Parser\EntityDefinition;
use FDL\Parser\Parameter;
use FDL\Parser\ParameterDefinition;
use FDL\Parser\ReferenceParameter;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyParsing()
    {
        $parser = new Parser([]);
        $this->assertEmpty($parser->getReferences());
        $this->assertEmpty($parser->getEntities());
        $this->assertEmpty($parser->getDependencyOrder());
        $this->assertEmpty($parser->getEntityDefinitions());
    }

    public function testBasicEntityParsing()
    {
        $parser = $this->getBasicParser();

        $entityDefinitions = $parser->getEntityDefinitions();
        $this->assertCount(1, $entityDefinitions);
        $this->assertArrayHasKey('Test1', $entityDefinitions);

        $entityDefinition = $entityDefinitions['Test1'];
        $this->assertTrue($entityDefinition instanceof EntityDefinition);
        $this->assertEquals('Test1', $entityDefinition->getEntityName());
        $this->assertEquals('\FDL\BasicEntity', $entityDefinition->getClassName());

        $entities = $parser->getEntities();
        $this->assertCount(1, $entities);

        $entity = $entities[0];
        $this->assertTrue($entity instanceof Entity);
        $this->assertEquals('Test1', $entity->getEntityName());

        $references = $parser->getReferences();
        $this->assertCount(1, $references);

        $reference = array_values($references)[0];
        $referenceName = array_keys($references)[0];
        /* @var ReferenceParameter $reference */
        $this->assertTrue($reference instanceof Entity);
        $this->assertEquals($reference, $entity);
        $this->assertEquals($entity, $parser->getEntityByReference($referenceName));
    }

    public function testBasicEntityParameterParsing()
    {
        $parser = $this->getBasicParser();

        $entityDefinitions = $parser->getEntityDefinitions();
        $entityDefinition = $entityDefinitions['Test1'];
        $this->assertEquals(1, $entityDefinition->getParameterCount());
        $parameterDefinitions = $entityDefinition->getParameterDefinitions();
        $this->assertCount(1, $parameterDefinitions);

        $parameterDefinition = $parameterDefinitions[0];
        $this->assertTrue($parameterDefinition instanceof ParameterDefinition);
        $this->assertEquals(null, $parameterDefinition->getEntityType());
        $this->assertEquals('Name', $parameterDefinition->getName());
        $this->assertEquals([], $parameterDefinition->getMetaData());
        $this->assertEquals('set', $parameterDefinition->getPrefix());

        $entities = $parser->getEntities();
        $entity = $entities[0];

        $parameters = $entity->getParameters();
        $this->assertCount(1, $parameters);

        $parameter = $parameters[0];
        $this->assertTrue($parameter instanceof Parameter);
        /** @var Parameter $parameter */
        $this->assertEquals('myBasicEntity', $parameter->getData());

    }

    public function testComplicatedEntityParsing()
    {
        $parser = $this->getComplicatedParser();

        $entityDefinitions = $parser->getEntityDefinitions();
        $this->assertCount(2, $entityDefinitions);
        $this->assertArrayHasKey('Basic', $entityDefinitions);
        $this->assertArrayHasKey('Compound', $entityDefinitions);

        $entityDefinition = $entityDefinitions['Basic'];
        $this->assertTrue($entityDefinition instanceof EntityDefinition);
        $this->assertEquals('Basic', $entityDefinition->getEntityName());
        $this->assertEquals('\FDL\BasicEntity', $entityDefinition->getClassName());

        $entityDefinition = $entityDefinitions['Compound'];
        $this->assertTrue($entityDefinition instanceof EntityDefinition);
        $this->assertEquals('Compound', $entityDefinition->getEntityName());
        $this->assertEquals('\FDL\ComplicatedEntity', $entityDefinition->getClassName());

        $entities = $parser->getEntities();
        $this->assertCount(4, $entities);
    }

    private function getBasicParser()
    {
        return new Parser([__DIR__ . '/basic.fdl']);
    }

    private function getComplicatedParser()
    {
        return new Parser([__DIR__ . '/complicated.fdl']);
    }
}
