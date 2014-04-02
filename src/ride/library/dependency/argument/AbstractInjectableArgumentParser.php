<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyInjector;

/**
 * Parser for defined dependency values.
 */
abstract class AbstractInjectableArgumentParser implements InjectableArgumentParser {

    /**
     * Name of the property for the interface of the dependency
     * @var string
     */
    const PROPERTY_INTERFACE = 'interface';

    /**
     * Name of the property for the id of the dependency
     * @var string
     */
    const PROPERTY_ID = 'id';

    /**
     * Instance of the dependency injector
     * @var \ride\library\dependency\DependencyInjector
     */
    protected $di;

    /**
     * Exclusion list for the dependency injector
     * @var array
     */
    protected $exclude;

    /**
     * Sets the dependency injector to this parser
     * @param \ride\library\dependency\DependencyInjector $di
     * @return null
     */
    public function setDependencyInjector(DependencyInjector $dependencyInjector) {
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * Sets the exclude array of the dependency injector
     * @param array $exclude
     * @return null
     */
    public function setExclude(array $exclude = null) {
        $this->exclude = $exclude;
    }

    /**
     * Gets the id of the dependency
     * @param \ride\library\dependency\DependencyCallArgument $argument
     * @return string|null
     */
    protected function getDependencyId(DependencyCallArgument $argument) {
        return $argument->getProperty(self::PROPERTY_ID);
    }

    /**
     * Gets the dependency
     * @param string $interface Name of the interface
     * @param string|null $id The id of the instance
     * @return mixed
     */
    protected function getDependency($interface, $id) {
        return $this->dependencyInjector->get($interface, $id, null, false, $this->exclude);
    }

    /**
     * Gets dependencies
     * @param string $interface Name of the interface
     * @param array $include
     * @param array $exclude
     * @return mixed
     */
    protected function getDependencies($interface, array $include = null, array $exclude = null) {
        if (!$include && !$exclude) {
            return $this->dependencyInjector->getAll($interface);
        }

        return $this->dependencyInjector->getByTag($interface, $include, $exclude);
    }

}
