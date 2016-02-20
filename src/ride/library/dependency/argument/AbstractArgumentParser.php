<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

/**
 * Abstract implementation of an ArgumentParser
 */
abstract class AbstractArgumentParser implements ArgumentParser {

    /**
     * Checks if this argument is scalar or needs intelligence
     * @return boolean True if this argument needs intelligence, false otherwise
     */
    public function needsIntelligence() {
        return false;
    }

    /**
     * Gets the intelligence of this argument
     * @param \ride\library\dependency\DependencyCallArgument $argument Argument
     * definition
     * @return \ride\library\dependency\DependencyCallArgument
     */
    public function getIntelligence(DependencyCallArgument $argument) {
        return null;
    }

}
