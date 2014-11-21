<?php

namespace FDL\Parser;


class ParameterDefinition
{
    const TYPE_CHAR = '.';
    const PREFIX_CHAR = '<';
    const DEFAULT_PREFIX = 'set';
    private $name;
    private $metaData;

    public function __construct($name, $metaData = [])
    {
        $this->name = $name;
        $this->metaData = $metaData;
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
            if (self::TYPE_CHAR === substr($data, 0, 1)) {
                return substr($data, 1);
            }
        }

        return null;
    }

    public function isTyped()
    {
        return null !== $this->getEntityType();
    }

    public function isReference()
    {
        return false !== array_search('!', $this->metaData);
    }

    public function isAnonymous()
    {
        return null === $this->name;
    }

    public function getPrefix()
    {
        $prefix = self::DEFAULT_PREFIX;
        foreach ($this->metaData as $data) {
            if (self::PREFIX_CHAR === substr($data, 0, 1)) {
                $prefix = substr($data, 1);
            }
        }

        return $prefix;
    }
}
