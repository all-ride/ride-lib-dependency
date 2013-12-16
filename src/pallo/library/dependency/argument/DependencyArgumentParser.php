<?php

namespace pallo\library\dependency\argument;

use pallo\library\dependency\exception\DependencyException;
use pallo\library\dependency\DependencyCallArgument;

/**
 * Parser for defined dependency values.
 */
class DependencyArgumentParser extends AbstractInjectableArgumentParser {

    /**
     * Gets the actual value of the argument
     * @param pallo\library\dependency\DependencyCallArgument $argument
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        $interface = $argument->getProperty(self::PROPERTY_INTERFACE);
        if (!$interface) {
            throw new DependencyException('Invalid argument properties, please define a interface');
        }

        $id = $this->getDependencyId($argument);

        return $this->getDependency($interface, $id);
    }

}