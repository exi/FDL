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

    public function addReference(EntityReference $reference)
    {
        $this->references[] = $reference;
    }
}
