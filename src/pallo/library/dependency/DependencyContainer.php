<?php

namespace pallo\library\dependency;

use pallo\library\dependency\exception\DependencyException;

/**
 * Container of injection dependencies
 */
class DependencyContainer {

    /**
     * Array with the injection dependencies. The class name will be stored as
     * key and an array of possible dependencies as value
     * @var array
     */
    protected $dependencies;

    /**
     * Constructs a new injection dependency container
     * @return null
     */
    public function __construct() {
        $this->dependencies = array();
    }

    /**
     * Adds a dependency for the provided class to this container
     * @param Dependency $dependency
     * @return null
     * @throws Exception when the provided interface is a invalid value
     */
    public function addDependency(Dependency $dependency) {
        $interfaces = $dependency->getInterfaces();
        if (!$interfaces) {
            throw new DependencyException('Could not add the dependency: no interfaces set in the provided dependency');
        }

        foreach ($interfaces as $interface => $null) {
            if (!isset($this->dependencies[$interface])) {
                $this->dependencies[$interface] = array();
            }

            $id = $dependency->getId();
            if (!$id) {
                $id = 'd' . count($this->dependencies[$interface]);
                $dependency->setId($id);
            }

            $this->dependencies[$interface][$id] = $dependency;
        }
    }

    /**
     * Gets the dependencies for the provided class
     * @param string $interface a full class name
     * @return array Array with the class name as key and an array of
     * injection dependencies as value if no class name provided. If a
     * $interface is provided, an plain array with injection dependencies
     * will be returned.
     * @throws Exception when the provided interface is a invalid value
     * @see Dependency
     */
    public function getDependencies($interface = null) {
        if ($interface === null) {
            return $this->dependencies;
        }

        if (!is_string($interface) || !$interface) {
            throw new DependencyException('Provided interface name is invalid');
        }

        if (isset($this->dependencies[$interface])) {
            return $this->dependencies[$interface];
        }

        return array();
    }

}