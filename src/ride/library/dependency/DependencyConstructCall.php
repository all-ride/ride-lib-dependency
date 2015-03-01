<?php

namespace ride\library\dependency;

use ride\library\dependency\exception\DependencyException;

/**
 * Definition of a callback to construct a dependency
 */
class DependencyConstructCall extends DependencyCall {

    /**
     * Full class name of the factory interface
     */
    protected $interface;

    /**
     * Constructs a new dependency construct callback
     * @param string $interface Interface of the factory
     * @param string $methodName Method name for the callback
     * @param string $id Id of the dependency
     * @return null
     */
    public function __construct($interface, $methodName, $id = null) {
        parent::__construct($methodName, $id);

        $this->setInterface($interface);
    }

    /**
     * Sets the interface of the factory
     * @param string $interface Full class name of the factory interface
     * @return null
     */
    public function setInterface($interface) {
        if (!is_string($interface) || !$interface) {
            throw new DependencyException('Could not set the interface of the dependency construct call: provided interface is empty or invalid');
        }

        $this->interface = $interface;
    }

    /**
     * Gets the interface of the factory
     * @return string Full class name of the factory instance
     */
    public function getInterface() {
        return $this->interface;
    }

}
