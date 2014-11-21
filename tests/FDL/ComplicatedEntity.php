<?php

namespace FDL;


class ComplicatedEntity
{
    private $name;
    private $persisted = false;
    private $basics = [];

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
     * @return BasicEntity[]
     */
    public function getBasics()
    {
        return $this->basics;
    }

    public function superSetBasic(BasicEntity $basicEntity)
    {
        $this->basics[] = $basicEntity;
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
}
