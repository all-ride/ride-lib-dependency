<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

/**
 * Parser for scalar values
 */
class ScalarArgumentParser implements ArgumentParser {

    /**
     * Name of the property for the value
     * @var string
     */
    const PROPERTY_VALUE = 'value';

    /**
     * Gets the actual value of the argument
     * @param \ride\library\dependency\DependencyCallArgument $argument The argument definition
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        return $argument->getProperty(self::PROPERTY_VALUE);
    }

}