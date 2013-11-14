<?php

namespace pallo\library\dependency\argument;

use pallo\library\dependency\DependencyCallArgument;

/**
 * Parser for an array of defined dependency values.
 */
class DependenciesArgumentParser extends AbstractInjectableArgumentParser {

    /**
     * Gets the actual value of the argument
     * @param pallo\library\dependency\DependencyCallArgument $argument
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        $interface = $argument->getProperty(self::PROPERTY_INTERFACE);
        $include = $argument->getProperty('include');
        $exclude = $argument->getProperty('exclude');

        if (!$interface) {
            throw new DependencyException('Invalid argument properties, please define a interface');
        }

        if ($include && !is_array($include)) {
            $include = array($include);
        }

        if ($exclude && !is_array($exclude)) {
            $exclude = array($exclude);
        }

        return $this->getDependencies($interface, $include, $exclude);
    }

}