<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

/**
 * Parser for array values
 */
class ArrayArgumentParser extends AbstractArgumentParser {

    /**
     * Gets the actual value of the argument
     * @param \ride\library\dependency\DependencyCallArgument $argument The argument definition
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        return $argument->getProperties();
    }

}
