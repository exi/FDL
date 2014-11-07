<?php

namespace FDL\Parser;


class MultiParameter
{
    private $references = [];

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    public function addReference(ReferenceParameter $reference)
    {
        $this->references[] = $reference;
    }
}
