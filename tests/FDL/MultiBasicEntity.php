<?php

namespace FDL;


class MultiBasicEntity
{
    private $name;
    private $names = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @param array $name
     */
    public function addName($name)
    {
        $this->names[] = $name;
    }

}
