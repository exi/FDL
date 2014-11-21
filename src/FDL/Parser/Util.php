<?php

namespace FDL\Parser;


class Util
{
    public static function toReferenceName($entityName, $reference)
    {
        if (!is_string($entityName) or '' === $entityName) {
            throw new \InvalidArgumentException('entityName must be a non zero length string');
        }

        if (!is_string($reference) or '' === $reference) {
            throw new \InvalidArgumentException('reference must be a non zero length string');
        }

        return md5($entityName . $reference);
    }
}
 