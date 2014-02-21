<?php

namespace ride\library\dependency;

use ride\library\dependency\exception\DependencyException;

/**
 * Definition of a depenency callback
 */
class DependencyCall {

    /**
     * The method name for this callback
     * @var string
     */
    protected $methodName;

    /**
     * The id of this call
     * @var string
     */
    protected $id;

    /**
     * The arguments for this callback
     * @var array
     */
    protected $arguments;

    /**
     * Constructs a new dependency callback
     * @param string $methodName The method name for the callback
     * @return null
     */
    public function __construct($methodName, $id = null) {
        $this->setMethodName($methodName);
        $this->setId($id);
        $this->clearArguments();
    }

    /**
     * Sets the method name of this callback
     * @param string $methodName A method name
     * @return null
     */
    public function setMethodName($methodName) {
        if (!is_string($methodName) || !$methodName) {
            throw new DependencyException('Could not set the method of the dependency call: provided method name is empty or invalid');
        }

        $this->methodName = $methodName;
    }

    /**
     * Gets the method name of this callback
     * @return string A method name
     */
    public function getMethodName() {
        return $this->methodName;
    }

    /**
     * Sets the id of this dependency call
     * @param string $id A identifier
     * @return null
     */
    public function setId($id = null) {
        if ($id !== null && (!is_string($id) || $id == '')) {
            throw new DependencyException('Could not set the id of ' . $this->methodName . ': provided id is empty or invalid');
        }

        $this->id = $id;
    }

    /**
     * Gets the id of this dependency call
     * @return string A identifier
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Adds a argument for this callback
     * @param DependencyCallArgument $argument
     * @return null
     */
    public function addArgument(DependencyCallArgument $argument) {
        if ($this->arguments === null) {
            $this->arguments = array();
        }

        $this->arguments[$argument->getName()] = $argument;
    }

    /**
     * Gets the arguments of this callback
     * @return array Array with dependency call arguments
     * @see DependencyCallArgument
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * Clears the arguments
     * @return null
     */
    public function clearArguments() {
        $this->arguments = null;
    }

}