<?php

namespace FDL\Parser;


class EntityReference
{
    private $reference;

    function __construct($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }
}
 