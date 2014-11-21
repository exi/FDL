<?php

namespace FDL;

require_once __DIR__ . '/BasicEntity.php';
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

    public function testCustomConstructor()
    {

    }

    private function getBasicFDL()
    {
        return new FDL([__DIR__ . '/basic.fdl']);
    }

    private function getComplicatedFDL()
    {
        return new FDL([__DIR__ . '/complicated.fdl']);
    }
}
 