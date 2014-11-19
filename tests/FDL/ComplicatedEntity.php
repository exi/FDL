<?php

namespace FDL;


class ComplicatedEntity
{
    private $name;

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
     * @return array
     */
    public function getBasics()
    {
        return $this->basics;
    }

    public function superSetBasics(BasicEntity $basicEntity)
    {
        $this->basics[] = $basicEntity;
    }
}
