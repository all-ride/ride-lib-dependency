<?php

namespace pallo\library\dependency;

use pallo\library\dependency\exception\DependencyException;

/**
 * Definition of a depenency
 */
class Dependency {

    /**
     * The full class name of this dependency
     * @var string
     */
    protected $className;

    /**
     * The id of this definition
     * @var string
     */
    protected $id;

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
     * Interfaces to implement
     * @var array
     */
    protected $interfaces;

    /**
     * Constructs a new dependency
     * @param string $className A full class name
     * @return null
     */
    public function __construct($className, $id = null) {
        $this->setClassName($className);
        $this->setId($id);

        $this->constructorArguments = null;
        $this->calls = null;
        $this->interfaces = array();
    }

    /**
     * Sets the full class name of this dependency
     * @param string $className A full class name
     * @return null
     */
    public function setClassName($className) {
        if (!is_string($className) || !$className) {
            throw new DependencyException('Provided class name is empty or invalid');
        }

        $this->className = $className;
    }

    /**
     * Gets the class of this dependency
     * @return string A full class name
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
            throw new DependencyException('Provided id is empty or invalid');
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
     * @return null
     */
    public function removeInterface($interface) {
        if (isset($this->interfaces[$interface])) {
            unset($this->interfaces[$interface]);
        }
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

}