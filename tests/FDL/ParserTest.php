<?php
namespace FDL;

use FDL\Parser\EmptyParameter;
use FDL\Parser\Entity;
use FDL\Parser\EntityDefinition;
use FDL\Parser\MultiParameter;
use FDL\Parser\Parameter;
use FDL\Parser\ParameterDefinition;
use FDL\Parser\ReferenceParameter;
use FDL\Parser\Util;
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
        $this->assertEquals(['\FDL\BasicEntity', 'my', 'meta'], $entityDefinition->getMetaData());

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
        $this->assertEquals('set', $parameterDefinition->getPrefix());
        $this->assertFalse($parameterDefinition->isMulti());
        $this->assertFalse($parameterDefinition->isAnonymous());
        $this->assertFalse($parameterDefinition->isReference());

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
        $this->assertEquals(['\FDL\BasicEntity'], $entityDefinition->getMetaData());

        $parameterDefinitions = $entityDefinition->getParameterDefinitions();
        $this->assertCount(1, $parameterDefinitions);
        list($nameParameterDefinition) = $parameterDefinitions;
        /** @var ParameterDefinition $nameParameterDefinition */
        $this->assertEquals('Name', $nameParameterDefinition->getName());
        $this->assertEquals('set', $nameParameterDefinition->getPrefix());
        $this->assertEquals(null, $nameParameterDefinition->getEntityType());
        $this->assertFalse($nameParameterDefinition->isMulti());
        $this->assertFalse($nameParameterDefinition->isAnonymous());
        $this->assertTrue($nameParameterDefinition->isReference());

        $entityDefinition = $entityDefinitions['Compound'];
        $this->assertTrue($entityDefinition instanceof EntityDefinition);
        $this->assertEquals('Compound', $entityDefinition->getEntityName());
        $this->assertEquals(['\FDL\ComplicatedEntity'], $entityDefinition->getMetaData());

        $parameterDefinitions = $entityDefinition->getParameterDefinitions();
        $this->assertCount(3, $parameterDefinitions);
        list($referenceParameterDefinition, $nameParameterDefinition, $basicParameterDefinition) = $parameterDefinitions;
        /** @var ParameterDefinition $referenceParameterDefinition */
        /** @var ParameterDefinition $nameParameterDefinition */
        /** @var ParameterDefinition $basicParameterDefinition */
        $this->assertEquals('set', $referenceParameterDefinition->getPrefix());
        $this->assertFalse($referenceParameterDefinition->isTyped());
        $this->assertFalse($referenceParameterDefinition->isMulti());
        $this->assertTrue($referenceParameterDefinition->isAnonymous());
        $this->assertTrue($referenceParameterDefinition->isReference());

        $this->assertEquals('Name', $nameParameterDefinition->getName());
        $this->assertEquals('set', $nameParameterDefinition->getPrefix());
        $this->assertFalse($nameParameterDefinition->isTyped());
        $this->assertFalse($nameParameterDefinition->isMulti());
        $this->assertFalse($nameParameterDefinition->isAnonymous());
        $this->assertFalse($nameParameterDefinition->isReference());

        $this->assertEquals('Basic', $basicParameterDefinition->getEntityType());
        $this->assertEquals('Basic', $basicParameterDefinition->getName());
        $this->assertEquals('superSet', $basicParameterDefinition->getPrefix());
        $this->assertTrue($basicParameterDefinition->isMulti());
        $this->assertFalse($basicParameterDefinition->isAnonymous());
        $this->assertFalse($basicParameterDefinition->isReference());
        $this->assertTrue($basicParameterDefinition->isTyped());

        $entities = $parser->getEntities();
        $this->assertCount(4, $entities);
        $compound = null;
        /** @var Entity[] $basics */
        $basics = [];
        foreach ($entities as $entity) {
            $this->assertTrue($entity instanceof Entity);
            switch ($entity->getEntityName()) {
                case 'Compound':
                    $compound = $entity;
                    break;
                case 'Basic':
                    $basics[$entity->getReference()] = $entity;
                    break;
            }
        }

        $this->assertNotNull($compound);
        $this->assertCount(3, $basics);

        $this->assertCount(3, $compound->getParameters());

        $adHocReference = Util::toReferenceName('Basic', 'my-basic ad-hoc');
        $secondReference = Util::toReferenceName('Basic', 'MySecondBasicEntity');
        $thirdReference = Util::toReferenceName('Basic', 'ThirdBasicEntity');

        $this->assertArrayHasKey($adHocReference, $basics);
        $this->assertArrayHasKey($secondReference, $basics);
        $this->assertArrayHasKey($thirdReference, $basics);

        /** @var Entity $entityAdHoc */
        $entityAdHoc = $basics[$adHocReference];
        /** @var Entity $entitySecond */
        $entitySecond = $basics[$secondReference];
        /** @var Entity $entityThird */
        $entityThird = $basics[$thirdReference];

        list($nameParameter) = $entityAdHoc->getParameters();
        $this->assertTrue($nameParameter instanceof Parameter);
        /** @var $nameParameter Parameter */
        $this->assertEquals('MyBasicEntity', $nameParameter->getData());

        list($nameParameter) = $entitySecond->getParameters();
        $this->assertTrue($nameParameter instanceof Parameter);
        /** @var $nameParameter Parameter */
        $this->assertEquals('MySecondBasicEntity', $nameParameter->getData());

        list($nameParameter) = $entityThird->getParameters();
        $this->assertTrue($nameParameter instanceof Parameter);
        /** @var $nameParameter Parameter */
        $this->assertEquals('ThirdBasicEntity', $nameParameter->getData());

        list($referenceParameter, $nameParameter, $multiBasicParameter) = $compound->getParameters();
        $this->assertTrue($referenceParameter instanceof Parameter);
        /** @var $referenceParameter Parameter */
        $this->assertTrue($nameParameter instanceof Parameter);
        /** @var $nameParameter Parameter */
        $this->assertTrue($multiBasicParameter instanceof MultiParameter);
        /** @var MultiParameter $multiBasicParameter */
        $this->assertEquals('myReferenceName', $referenceParameter->getData());
        $this->assertEquals('myComplicatedEntity', $nameParameter->getData());

        $multiReferences = $multiBasicParameter->getParameters();
        $this->assertCount(3, $multiReferences);
        $multiEntities = array_map(function(ReferenceParameter $reference) use ($parser) {
            $entity = $parser->getEntityByReference($reference->getReference());
            $this->assertTrue($entity instanceof Entity);
            return $entity;
        }, $multiReferences);

        $expectedBasicNames = ['MyBasicEntity', 'MySecondBasicEntity', 'ThirdBasicEntity'];
        array_map(function(Entity $entity, $expectedName) {
            $this->assertCount(1, $entity->getParameters());
            /** @var Parameter $parameter */
            $parameter = $entity->getParameters()[0];
            $this->assertTrue($parameter instanceof Parameter);
            $this->assertEquals($expectedName, $parameter->getData());
        }, $multiEntities, $expectedBasicNames);
    }

    public function testEmptyParamParsing()
    {
        $parser = $this->getEmptyParamsParser();

        $entityDefinitions = $parser->getEntityDefinitions();
        $this->assertCount(1, $entityDefinitions);
        $this->assertArrayHasKey('Test1', $entityDefinitions);

        $entityDefinition = $entityDefinitions['Test1'];
        $this->assertTrue($entityDefinition instanceof EntityDefinition);
        $this->assertEquals('Test1', $entityDefinition->getEntityName());
        $this->assertEquals(['\FDL\MultiBasicEntity'], $entityDefinition->getMetaData());

        $entities = $parser->getEntities();
        $this->assertCount(3, $entities);
        $notEmptyEntity = $parser->getEntityByReference(Util::toReferenceName('Test1', 'not empty'));
        $this->assertNotNull($notEmptyEntity);
        $emptyEntity = $parser->getEntityByReference(Util::toReferenceName('Test1', 'empty'));
        $this->assertNotNull($emptyEntity);
        $reallyEmptyEntity = $parser->getEntityByReference(Util::toReferenceName('Test1', 'really empty'));
        $this->assertNotNull($reallyEmptyEntity);

        $this->assertCount(2, $notEmptyEntity->getParameters()[1]->getParameters());
        $this->assertEmpty($emptyEntity->getParameters()[1]->getParameters());
        $this->assertEmpty($reallyEmptyEntity->getParameters()[1]->getParameters());
        $this->assertTrue($emptyEntity->getParameters()[0] instanceof Parameter);
        $this->assertTrue($emptyEntity->getParameters()[1] instanceof MultiParameter);
        $this->assertTrue($notEmptyEntity->getParameters()[0] instanceof Parameter);
        $this->assertTrue($notEmptyEntity->getParameters()[1] instanceof MultiParameter);
        $this->assertTrue($reallyEmptyEntity->getParameters()[0] instanceof EmptyParameter);
        $this->assertTrue($reallyEmptyEntity->getParameters()[1] instanceof MultiParameter);
    }
    private function getBasicParser()
    {
        return new Parser([__DIR__ . '/fdls/basic.fdl']);
    }

    private function getComplicatedParser()
    {
        return new Parser([__DIR__ . '/fdls/complicated.fdl']);
    }

    private function getEmptyParamsParser()
    {
        return new Parser([__DIR__ . '/fdls/emptyParams.fdl']);
    }
}
