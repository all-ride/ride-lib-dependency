<?php

namespace ride\library\dependency;

use ride\library\dependency\exception\DependencyException;

/**
 * Definition of a depenency
 */
class Dependency {

    /**
     * Full class name of this dependency
     * @var string
     */
    protected $className;

    /**
     * Id of this definition
     * @var string
     */
    protected $id;

    /**
     * Interfaces the instance implements
     * @var array
     */
    protected $interfaces;

    /**
     * Arguments for the constructor
     * @var array
     */
    protected $constructorArguments;

    /**
     * Definitions of calls to invoke when creating this dependency
     * @var array
     */
    protected $calls;

    /**
     * Tags of this dependency
     * @var array
     */
    protected $tags;

    /**
     * Constructs a new dependency
     * @param string|DependencyConstructCall $classOrCall Full class name or a
     * dependency construct call
     * @param string $id Id of this dependency
     * @return null
     */
    public function __construct($classOrCall, $id = null) {
        if ($classOrCall instanceof DependencyConstructCall) {
            $this->setConstructCall($classOrCall);
        } else {
            $this->setClassName($classOrCall);
        }
        $this->setId($id);

        $this->interfaces = array();
        $this->constructorArguments = null;
        $this->calls = null;
        $this->tags = null;
    }

    /**
     * Sets the call which constructs this dependency
     * @param DependencyConstructCall $constructCall
     * @return null
     */
    public function setConstructCall(DependencyCall $constructCall) {
        $this->constructCall = $constructCall;
        $this->className = null;
    }

    /**
     * Gets the call which constructs this dependency
     * @return DependencyConstructCall|null
     */
    public function getConstructCall() {
        return $this->constructCall;
    }

    /**
     * Sets the full class name of this dependency
     * @param string $className A full class name
     * @return null
     */
    public function setClassName($className) {
        if (!is_string($className) || !$className) {
            throw new DependencyException('Could not set the class of the dependency: provided class name is empty or invalid');
        }

        $this->className = $className;
        $this->constructCall = null;
    }

    /**
     * Gets the class of this dependency
     * @return string|null Full class name or null when a constructor call is set
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * Sets the id of this dependency
     * @param string $id A identifier
     * @return null
     */
    public function setId($id = null) {
        if ($id !== null && (!is_string($id) || $id == '')) {
            throw new DependencyException('Could not set the id of ' . $this->className . ': provided id is empty or invalid');
        }

        $this->id = $id;
    }

    /**
     * Gets the id of this dependency
     * @return string A identifier
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets the arguments for the constructor
     * @return null|array
     */
    public function getConstructorArguments() {
        return $this->constructorArguments;
    }

    /**
     * Adds a call to this dependency.
     * @param DependencyCall $call The call to add
     * @return
     */
    public function addCall(DependencyCall $call) {
        if ($call->getMethodName() == '__construct') {
            $this->constructorArguments = $call->getArguments();

            return;
        }

        if (!$this->calls) {
            $this->calls = array();
        }

        $id = $call->getId();
        if (!$id) {
            $id = 'c' . count($this->calls);

            $call->setId($id);
        }

        $this->calls[$id] = $call;
    }

    /**
     * Gets all the calls which should be invoked after the instance is created
     * @return array Array of dependency calls
     * @see DependencyCall
     */
    public function getCalls() {
        return $this->calls;
    }

    /**
     * Clears all the calls of this dependency
     * @return null
     */
    public function clearCalls() {
        $this->constructorArguments = null;
        $this->calls = null;
    }

    /**
     * Adds an interface which this dependency implements
     * @param string $interface Class name of the interface
     * @return null
     */
    public function addInterface($interface) {
        $this->interfaces[$interface] = true;
    }

    /**
     * Removes an interface
     * @param string $interface Class name of the interface
     * @return boolea,
     */
    public function removeInterface($interface) {
        if (!isset($this->interfaces[$interface])) {
            return false;
        }

        unset($this->interfaces[$interface]);

        return true;
    }

    /**
     * Sets the interfaces of this dependency
     * @param array $interfaces Array with the class name of the interface as
     * key and true as value
     * @return null
     */
    public function setInterfaces(array $interfaces) {
        $this->interfaces = $interfaces;
    }

    /**
     * Gets the interfaces of this dependency
     * @return array Array with the classname of the interface as key and true
     * as value
     */
    public function getInterfaces() {
        return $this->interfaces;
    }

    /**
     * Adds an tag
     * @param string $tag Tag to add
     * @return null
     */
    public function addTag($tag) {
        $this->tags[$tag] = $tag;
    }

    /**
     * Removes an tag
     * @param string $tag Tag to remove
     * @return boolean
     */
    public function removeTag($tag) {
        if (!isset($this->tags[$tag])) {
            return false;
        }

        unset($this->tags[$tag]);

        return true;
    }

    /**
     * Checks whether a tag is added to this dependency
     * @param string $tag Tag to check
     * @return boolean
     */
    public function hasTag($tag) {
        return isset($this->tags[$tag]);
    }

    /**
     * Gets the tags of this dependency
     * @return array Array with the tag as key and as value
     */
    public function getTags() {
        return $this->tags;
    }

}
