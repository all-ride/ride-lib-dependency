<?php

namespace pallo\library\dependency\argument;

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
        $id = $argument->getProperty(self::PROPERTY_ID);

        return $this->getDependency($interface, $id);
    }

}