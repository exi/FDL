<?php

namespace FDL\Parser;


class Util
{
    public static function toReferenceName($entityName, $reference)
    {
        if (null === $reference) {
            throw new \Exception('entity reference cannot be null');
        }

        if ('' === $reference) {
            throw new \Exception('entity reference cannot be ""');
        }

        return md5($entityName . $reference);
    }
}
 