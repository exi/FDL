<?php

namespace FDL\Parser;


class Parameter
{
    private $data;

    function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
 