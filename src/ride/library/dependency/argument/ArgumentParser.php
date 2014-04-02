<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

/**
 * Parser for the dependency call arguments
 */
interface ArgumentParser {
    
    /**
     * Gets the actual value of the argument
     * @param \ride\library\dependency\DependencyCallArgument $argument The argument definition
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument);
    
}