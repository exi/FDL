<?php

namespace FDL\Parser;


class ReferenceParameter
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
 