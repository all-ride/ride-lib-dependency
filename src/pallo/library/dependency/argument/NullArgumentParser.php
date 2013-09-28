<?php

namespace pallo\library\dependency\argument;

use pallo\library\dependency\DependencyCallArgument;

/**
 * Parser for null values
 */
class NullArgumentParser implements ArgumentParser {
    
    /**
     * Gets the actual value of the argument
     * @param pallo\library\dependency\DependencyCallArgument $argument The argument definition
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        return null;
    }
    
}