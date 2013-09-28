<?php

namespace pallo\library\dependency\argument;

use pallo\library\dependency\exception\DependencyException;
use pallo\library\dependency\DependencyCallArgument;
use pallo\library\reflection\Callback;

/**
 * Parser to get a value through a call.
 */
class CallArgumentParser extends AbstractInjectableArgumentParser {

    /**
     * Name of the property for the class of the call
     * @var string
     */
    const PROPERTY_CLASS = 'class';

    /**
     * Name of the property for the method of the call
     * @var string
     */
    const PROPERTY_METHOD = 'method';

    /**
     * Name of the property for the function of the call
     * @var string
     */
    const PROPERTY_FUNCTION = 'function';

    /**
     * Name of the arguments for the function or method call
     * @var array
     */
    const PROPERTY_ARGUMENTS = 'arguments';

    /**
     * Gets the actual value of the argument
     * @param pallo\library\dependency\DependencyCallArgument $argument The argument
     * definition. The extra value of the argument is optional and can be used
     * to define the id of the requested dependency
     * @return mixed The value
     */
    public function getValue(DependencyCallArgument $argument) {
        $interface = $argument->getProperty(self::PROPERTY_INTERFACE);
        $class = $argument->getProperty(self::PROPERTY_CLASS);
        $function = $argument->getProperty(self::PROPERTY_FUNCTION);

        if ($interface || $class) {
            if ($interface) {
                $id = $argument->getProperty(self::PROPERTY_ID);

                $object = $this->getDependency($interface, $id);
            } elseif ($class) {
                $object = $class;
            }

            $method = $argument->getProperty(self::PROPERTY_METHOD);
            if (!$method) {
                throw new DependencyException('Invalid argument properties, please define a method for your class or dependency');
            }

            $callback = new Callback(array($object, $method));
        } elseif ($function) {
            $callback = new Callback($function);
        } else {
            throw new DependencyException('Invalid argument properties, please define the interface, class or function property');
        }

        $arguments = $argument->getProperty(self::PROPERTY_ARGUMENTS);
        if ($arguments === null) {
            $arguments = array();
        }

        if (!is_array($arguments)) {
            throw new DependencyException('Invalid argument properties, the arguments property should be an array or empty');
        }

        return $this->dependencyInjector->invoke($callback, $arguments);
    }

}