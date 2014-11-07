<?php

namespace FDL\Parser;


class ParameterDefinition
{
    private $name;
    private $metaData;

    public function __construct($name, $metaData = [])
    {
        $this->name = $name;
        $this->metaData = $metaData;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function isMulti()
    {
        return false !== array_search('multi', $this->metaData);
    }

    public function getEntityType()
    {
        foreach ($this->metaData as $data) {
            if ('.' === substr($data, 0, 1)) {
                return substr($data, 1);
            }
        }

        return null;
    }

    public function isReference()
    {
        return false !== array_search('!', $this->metaData);
    }
}
 