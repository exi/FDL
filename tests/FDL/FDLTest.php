<?php

namespace FDL;

require_once __DIR__ . '/BasicEntity.php';
require_once __DIR__ . '/ComplicatedEntity.php';
use PHPUnit_Framework_TestCase;

class FDLTest extends PHPUnit_Framework_TestCase
{

    public function testBasicEntityConstruction()
    {
        $fdl = $this->getBasicFDL();
        $realEntities = $fdl->run();

        $this->assertCount(1, $realEntities);
        $realEntity = $realEntities[0];
        $this->assertTrue($realEntity instanceof BasicEntity);
        /** @var BasicEntity $realEntity */
        $this->assertEquals('myBasicEntity', $realEntity->getName());
    }

    public function testDefaultEntityPersistence()
    {
        $fdl = $this->getBasicFDL();
        $fdl->setDefaultPersistFunction(function($entityName, Array $metaData, BasicEntity $entity) {
            $this->assertEquals('Test1', $entityName);
            $this->assertEquals(['\FDL\BasicEntity', 'my', 'meta'], $metaData);
            $entity->persist();
        });
        $realEntities = $fdl->run();

        $this->assertCount(1, $realEntities);
        $realEntity = $realEntities[0];
        $this->assertTrue($realEntity instanceof BasicEntity);
        /** @var BasicEntity $realEntity */
        $this->assertTrue($realEntity->isPersisted());
    }

    public function testSpecificEntityPersistence()
    {
        $fdl = $this->getBasicFDL();
        $fdl->addPersistFunction('Test1', function($entityName, Array $metaData, BasicEntity $entity) {
            $this->assertEquals('Test1', $entityName);
            $this->assertEquals(['\FDL\BasicEntity', 'my', 'meta'], $metaData);
            $entity->persist();
        });
        $realEntities = $fdl->run();

        $this->assertCount(1, $realEntities);
        $realEntity = $realEntities[0];
        $this->assertTrue($realEntity instanceof BasicEntity);
        /** @var BasicEntity $realEntity */
        $this->assertTrue($realEntity->isPersisted());
    }

    public function testDefaultEntityConstructor()
    {
        $fdl = $this->getBasicFDL();
        $fdl->setDefaultConstructFunction(function($entityName, Array $metaData) {
            $this->assertEquals('Test1', $entityName);
            $this->assertEquals(['\FDL\BasicEntity', 'my', 'meta'], $metaData);
            $restMeta = $metaData;
            array_shift($restMeta);
            return new BasicEntity(implode(' ', $restMeta));
        });
        $realEntities = $fdl->run();

        $this->assertCount(1, $realEntities);
        $realEntity = $realEntities[0];
        $this->assertTrue($realEntity instanceof BasicEntity);
        /** @var BasicEntity $realEntity */
        $this->assertEquals('my meta', $realEntity->getConstructName());
    }

    public function testSpecificEntityConstructor()
    {
        $fdl = $this->getBasicSpecificConstFDL();
        $fdl->addConstructFunction('Test2', function($entityName, Array $metaData) {
            $this->assertEquals('Test2', $entityName);
            $this->assertEquals(['my', 'meta'], $metaData);
            return new BasicEntity(implode(' ', $metaData));
        });
        $realEntities = $fdl->run();

        $this->assertCount(1, $realEntities);
        $realEntity = $realEntities[0];
        $this->assertTrue($realEntity instanceof BasicEntity);
        /** @var BasicEntity $realEntity */
        $this->assertEquals('my meta', $realEntity->getConstructName());
    }

    public function testComplicatedConstructionAndPersistence()
    {
        $fdl = $this->getComplicatedFDL();
        $fdl->setDefaultPersistFunction(function($entityName, Array $metaData, $entity) {
            if ($entity instanceof ComplicatedEntity) {
                $this->assertCount(3, $entity->getBasics());
                foreach ($entity->getBasics() as $basic) {
                    /** @var BasicEntity $basic */
                    $this->assertTrue($basic instanceof BasicEntity);
                    $this->assertTrue($basic->isPersisted());
                }
                $this->assertEquals(['\FDL\ComplicatedEntity'], $metaData);
            }

            $this->assertTrue($entity instanceof BasicEntity or $entity instanceof ComplicatedEntity);
            $this->assertTrue($entityName === 'Basic' or $entityName === 'Compound');
            $entity->persist();
        });
        $realEntities = $fdl->run();

        $this->assertCount(4, $realEntities);
        $basicNames = ['MyBasicEntity' => true, 'MySecondBasicEntity' => true, 'ThirdBasicEntity' => true];
        $basicNames2 = $basicNames;
        $compound = null;
        foreach ($realEntities as $entity) {
            if ($entity instanceof BasicEntity) {
                $this->assertArrayHasKey($entity->getName(), $basicNames);
                unset($basicNames[$entity->getName()]);
            } else if ($entity instanceof ComplicatedEntity) {
                $compound = $entity;
                $this->assertCount(3, $compound->getBasics());
                foreach ($compound->getBasics() as $basic) {
                    $this->assertArrayHasKey($basic->getName(), $basicNames2);
                    unset($basicNames2[$entity->getName()]);
                }
            } else {
                $this->fail('Wrong entity type ' . get_class($entity));
            }
        }
    }

    private function getBasicFDL()
    {
        return new FDL([__DIR__ . '/basic.fdl']);
    }

    private function getBasicSpecificConstFDL()
    {
        return new FDL([__DIR__ . '/basicSpecificConst.fdl']);
    }

    private function getComplicatedFDL()
    {
        return new FDL([__DIR__ . '/complicated.fdl']);
    }
}
 