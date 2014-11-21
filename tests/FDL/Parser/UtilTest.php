<?php


class UtilTest extends PHPUnit_Framework_TestCase {
    public function testToReferenceName()
    {
        $this->assertEquals(md5('testtext'), \FDL\Parser\Util::toReferenceName('test', 'text'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToReferenceNameEmptyEntityName()
    {
        \FDL\Parser\Util::toReferenceName('', 'text');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToReferenceNameEmptyReference()
    {
        \FDL\Parser\Util::toReferenceName('test', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToReferenceNameNullEntityName()
    {
        \FDL\Parser\Util::toReferenceName(null, 'text');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToReferenceNameNullReference()
    {
        \FDL\Parser\Util::toReferenceName('test', null);
    }
}
 