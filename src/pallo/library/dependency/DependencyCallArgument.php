<?php

namespace pallo\library\dependency;

use pallo\library\dependency\exception\DependencyException;

/**
 * Definition of a argument for a depenency callback
 */
class DependencyCallArgument {

    /**
     * The name of the argument
     * @var string
     */
    protected $name;

    /**
     * The type of this argument
     * @var string
     */
    protected $type;

    /**
     * The properties of this argument
     * @var array
     */
    protected $properties;

    /**
     * Constructs a new dependency callback argument
     * @param string $name The name of this argument
     * @param string $type The type of this argument
     * @param array $properties The properties of the argument
     * provided type
     * @return null
     */
    public function __construct($name, $type, array $properties = array()) {
        $this->setName($name);
        $this->setValue($type, $properties);
    }

    /**
     * Gets a string representation of this argument
     * @return string
     */
    public function __toString() {
        $properties = array();
        foreach ($this->properties as $key => $value) {
            $properties[] = $key . ': ' . $value;
        }

        $string = '$' . $this->name . ' ' . $this->type . " {\n    ";
        $string .= implode(",\n    ", $properties);
        $string .= "\n}";

        return $string;
    }

    /**
     * Sets the name of the argument
     * @param string $name
     * @return null
     */
    public function setName($name) {
        if (!is_string($name) || !$name) {
            throw new DependencyException('Provided name is invalid or empty');
        }

        $this->name = $name;
    }

    /**
     * Gets the name of the argument
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the value for this argument
     * @param string $type The type of the value
     * @param array $properties The properties of the argument
     * @return null
     * @throws Exception when invalid arguments provided
     */
    public function setValue($type, array $properties) {
        if (!is_string($type) || !$type) {
            throw new DependencyException('Invalid argument type provided');
        }

        $this->type = $type;
        $this->properties = $properties;
    }

    /**
     * Gets the type of this argument
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Gets a property of this argument
     * @param string $name The name of the property
     * @param mixed $default The default value for when the property is not set
     * @return mixed
     */
    public function getProperty($name, $default = null) {
        if (!isset($this->properties[$name])) {
            return $default;
        }

        return $this->properties[$name];
    }

    /**
     * Gets all the properties of this argument
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

}