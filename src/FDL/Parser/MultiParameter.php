<?php

namespace FDL\Parser;


class MultiParameter
{
    private $parameters = [];

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function addEntity($parameter)
    {
        $this->parameters[] = $parameter;
    }
}
 