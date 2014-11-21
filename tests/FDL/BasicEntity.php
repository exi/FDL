<?php

namespace FDL;


class BasicEntity
{
    private $name;
    private $persisted = false;
    private $constructName = null;

    public function __construct($constructName = null)
    {
        $this->constructName = $constructName;
    }

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

    public function persist()
    {
        $this->persisted = true;
    }

    /**
     * @return boolean
     */
    public function isPersisted()
    {
        return $this->persisted;
    }

    /**
     * @return null
     */
    public function getConstructName()
    {
        return $this->constructName;
    }
}
