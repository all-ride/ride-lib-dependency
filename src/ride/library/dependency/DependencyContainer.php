<?php

namespace ride\library\dependency;

use ride\library\dependency\exception\DependencyException;

/**
 * Container of injection dependencies
 */
class DependencyContainer {

    /**
     * Array with the injection dependencies. The interface name will be stored
     * as key and an array of possible dependencies as value
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
     * Removes a dependency from the container
     * @param string $interface
     * @param string $id
     * @return boolean
     */
    public function removeDependency($interface, $id) {
        if (!isset($this->dependencies[$interface][$id])) {
            return false;
        }

        unset($this->dependencies[$interface][$id]);

        return true;
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

    /**
     * Gets dependencies by tag
     * @param string $interface Interface the dependency should implement
     * @param string|array $include Tags which the resulting dependencies should have
     * @param string|array $exclude Tags which the resulting dependencies cannot have
     * @return array Array with dependencies which match the provided filters
     */
    public function getDependenciesByTag($interface = null, $include = null, $exclude = null) {
        $result = array();

        if ($include) {
            if (!is_array($include)) {
                $include = array($include);
            }
        } else {
            $include = array();
        }

        if ($exclude) {
            if (!is_array($exclude)) {
                $exclude = array($exclude);
            }
        } else {
            $exclude = array();
        }

        if ($interface) {
            if (isset($this->dependencies[$interface])) {
                $result = $this->filterDependenciesByTag($this->dependencies[$interface], $include, $exclude);
            }
        } else {
            foreach ($this->dependencies as $interface => $dependencies) {
                $result = array_merge($result, $this->filterDependenciesByTag($dependencies, $include, $exclude));
            }
        }

        return $result;
    }

    /**
     * Filters the provided dependencies by tag
     * @param array $dependencies Dependencies to filtes
     * @param array $include Tags which should be included
     * @param array $exclude Tags which should be excluded
     * @return array Dependencies with included tags and no excluded tags
     */
    protected function filterDependenciesByTag(array $dependencies, array $include, array $exclude) {
        foreach ($dependencies as $dependency) {
            foreach ($include as $tag) {
                if (!$dependency->hasTag($tag)) {
                    continue 2;
                }
            }

            foreach ($exclude as $tag) {
                if ($dependency->hasTag($tag)) {
                    continue 2;
                }
            }

            $result[] = $dependency;
        }

        return $result;
    }

}