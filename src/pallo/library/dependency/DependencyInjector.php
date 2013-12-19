<?php

namespace pallo\library\dependency;

use pallo\library\dependency\argument\ArgumentParser;
use pallo\library\dependency\argument\ArrayArgumentParser;
use pallo\library\dependency\argument\CallArgumentParser;
use pallo\library\dependency\argument\DependencyArgumentParser;
use pallo\library\dependency\argument\DependenciesArgumentParser;
use pallo\library\dependency\argument\InjectableArgumentParser;
use pallo\library\dependency\argument\NullArgumentParser;
use pallo\library\dependency\argument\ScalarArgumentParser;
use pallo\library\dependency\exception\DependencyException;
use pallo\library\dependency\exception\DependencyNotFoundException;
use pallo\library\reflection\exception\ReflectionException;
use pallo\library\reflection\Callback;
use pallo\library\reflection\Invoker;
use pallo\library\reflection\ReflectionHelper;

use \Exception;
use \ReflectionClass;
use \ReflectionParameter;

/**
 * Implementation of a dependency injector. Load class instances dynamically
 * from a dependency container when and only when needed.
 */
class DependencyInjector implements Invoker {

    /**
     * Id for a undefined class
     * @var string
     */
    const ID_UNDEFINED = '#undefined#';

    /**
     * Call argument type
     * @var string
     */
    const TYPE_CALL = 'call';

    /**
     * Dependency argument type
     * @var string
     */
    const TYPE_DEPENDENCY = 'dependency';

    /**
     * Dependencies argument type
     * @var string
     */
    const TYPE_DEPENDENCIES = 'dependencies';

    /**
     * Null value argument type
     * @var string
     */
    const TYPE_NULL = 'null';

    /**
     * Scalar value argument type
     * @var string
     */
    const TYPE_SCALAR = 'scalar';

    /**
     * ARray value argument type
     * @var string
     */
    const TYPE_ARRAY = 'array';

    /**
     * Instance of the object factory
     * @var pallo\library\ObjectFactory
     */
    protected $objectFactory;

    /**
     * Array with the argument parsers
     * @var array
     */
    protected $argumentParsers;

    /**
     * Container of the injection dependencies
     * @var DependencyContainer
     */
    protected $container;

    /**
     * Created dependency instances
     * @var array
     */
    protected $instances;

    /**
     * Constructs a new dependency injector
     * @param pallo\library\dependency\DependencyContainer $container Container
     * with dependency definitions
     * @param pallo\library\reflection\ObjectFactory $objectFactory Creator of
     * objects
     * @return null
     */
    public function __construct(DependencyContainer $container = null, ReflectionHelper $reflectionHelper = null) {
        if (!$container) {
            $this->container = new DependencyContainer();
        } else {
            $this->container = $container;
        }

        if (!$reflectionHelper) {
            $this->reflectionHelper = new ReflectionHelper();
        } else {
            $this->reflectionHelper = $reflectionHelper;
        }

        $this->argumentParsers = array(
            self::TYPE_NULL => new NullArgumentParser(),
            self::TYPE_SCALAR => new ScalarArgumentParser(),
            self::TYPE_ARRAY => new ArrayArgumentParser(),
            self::TYPE_DEPENDENCY => new DependencyArgumentParser(),
            self::TYPE_DEPENDENCIES => new DependenciesArgumentParser(),
            self::TYPE_CALL => new CallArgumentParser(),
        );

        $this->instances = array();
    }

    /**
     * Gets the reflection helper
     * @return pallo\library\reflection\ReflectionHelper
     */
    public function getReflectionHelper() {
        return $this->reflectionHelper;
    }

    /**
     * Sets a argument parser for the provided type
     * @param string $type The name of the argument type
     * @param ArgumentParser $argumentParser The parser for this type
     * @return null
     * @throws Exception when the provided type is empty or not a string
     */
    public function setArgumentParser($type, ArgumentParser $argumentParser = null) {
        if (!is_string($type) || !$type) {
            throw new DependencyException('Provided type is empty or not a string');
        }

        if ($argumentParser) {
            $this->argumentParsers[$type] = $argumentParser;
        } elseif (isset($this->argumentParsers[$type])) {
            unset($this->argumentParsers[$type]);
        }
    }

    /**
     * Gets the argument parsers
     * @return array Array with the type as key and the argument parser as value
     */
    public function getArgumentParsers() {
        return $this->argumentParsers;
    }

    /**
     * Sets the container of the dependencies. All created instances will be reset.
     * @param pallo\core\dependency\DependencyContainer $container The container to set
     * @param boolean $clearInstances Set to true to clear all loaded instances
     * @return null
     */
    public function setContainer(DependencyContainer $container, $clearInstances = false) {
        $this->container = $container;

        if ($clearInstances) {
            $this->instances = null;
        }
    }

    /**
     * Gets the container of the dependencies
     * @return pallo\core\dependency\InjectionDefinitionContainer
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * Overrides the container by setting an instance which will always be
     * returned by get if the provided object's class name is requested
     * @param object $instance Instance to set
     * @param string|array $interface Interface(s) to set the instance for, if
     * not provided the class name of the instance will be used as interface
     * @param string $id Id of the instance
     * @return null
     * @throws pallo\library\dependency\exception\DependencyException if the
     * provided instance is not a object
     * @throws pallo\library\dependency\exception\DependencyException if the
     * provided interface is empty or invalid
     */
    public function setInstance($instance, $interface = null, $id = null) {
        if (!is_object($instance)) {
            throw new DependencyException('Provided instance is not an object');
        }

        if ($interface === null) {
            $interfaces = array(get_class($instance));
        } elseif (!is_array($interface)) {
            $interfaces = array($interface);
        } else {
            $interfaces = $interface;
        }

        foreach ($interfaces as $interface) {
            if (!is_string($interface) || !$interface) {
                throw new DependencyException('Provided interface is empty or invalid');
            }

            if (isset($this->instances[$interface])) {
                if (is_array($this->instances[$interface])) {
                    if ($id) {
                        $this->instances[$interface][$id] = $instance;
                    } else {
                        $this->instances[$interface][0] = $instance;
                    }
                } else {
                    if ($id) {
                        $this->instances[$interface] = array(
                            $this->instances[$interface],
                            $id => $instance,
                        );
                    } else {
                        $this->instances[$interface] = $instance;
                    }
                }
            } else {
                if ($id) {
                    $this->instances[$interface] = array(
                        $id => $instance,
                    );
                } else {
                    $this->instances[$interface] = $instance;
                }
            }
        }
    }

    /**
     * Removes a set instance
     * @param string|array $interface
     * @param string $id
     * @return boolean|array True if the interface was unset, false if no
     * interface was set
     */
    public function unsetInstance($interface, $id = null) {
        $isArray = is_array($interface);
        if (!$isArray) {
            $interfaces = array($interface);
        } else {
            $interfaces = $interface;
        }

        $result = array();

        foreach ($interfaces as $index => $interface) {
            if (!is_string($interface) || !$interface) {
                throw new DependencyException('Provided interface is empty or invalid');
            }

            $result[$index] = false;

            if ($id) {
                if (isset($this->instances[$interface][$id])) {
                    unset($this->instances[$interface][$id]);

                    if (!$this->instances[$interface]) {
                        unset($this->instances[$interface]);
                    }

                    $result[$index] = true;
                }
            } else {
                if (isset($this->instances[$interface])) {
                    unset($this->instances[$interface]);

                    $result[$index] = true;
                }
            }
        }

        if ($isArray) {
            return $result;
        } else {
            return array_pop($result);
        }
    }

    /**
     * Gets all the loaded instances
     * @parameter string $interface Fitler result on interface
     * @return array
     */
    public function getInstances($interface = null) {
        if ($interface === null) {
            return $this->instances;
        } elseif (isset($this->instances[$interface])) {
            return $this->instances[$interface];
        } else {
            return array();
        }
    }

    /**
     * Gets all the defined instances of the provided interface
     * @param string $interface The full class name of the interface or parent
     * class
     * @return array
     */
    public function getAll($interface) {
        $interfaceDependencies = array();

        $dependencies = $this->container->getDependencies($interface);
        foreach ($dependencies as $dependency) {
            $id = $dependency->getId();
            $interfaceDependencies[$id] = $this->get($interface, $id);
        }

        return $interfaceDependencies;
    }

    /**
     * Gets all the defined instances of the provided class
     * @param string $interface The full class name of the interface or parent
     * class
     * @return array
     */
    public function getByTag($interface = null, $include = null, $exclude = null) {
        $tagDependencies = array();

        $dependencies = $this->container->getDependenciesByTag($interface, $include, $exclude);
        foreach ($dependencies as $dependency) {
            $interfaces = $dependency->getInterfaces();
            if ($interfaces) {
                $interfaces = array_keys($interfaces);
                $interface = array_pop($interfaces);
            } else {
                $interface = $dependency->getClassName();
            }

            $tagDependencies[] = $this->get($interface, $dependency->getId());
        }

        return $tagDependencies;
    }

    /**
     * Gets a defined instance of the provided class
     * @param string $interface The full class name of the interface or parent
     * class
     * @param string $id The id of the dependency to get a specific definition.
     * If an id is provided,the exclude array will be ignored
     * @param array $arguments Array with the arguments for the constructor of
     * the interface. Passing arguments will always result in a new instance.
     * @param array $exclude Array with the interface as key and an array with
     * id's of dependencies as key to exclude from this get call. You should not
     * set this argument, this is used in recursive calls for the actual
     * dependency injection.
     * @return mixed Instance of the requested class
     * @throws pallo\library\dependency\exceptin\DependencyException if the class name
     * or the id are invalid
     * @throws pallo\library\dependency\exception\DependencyException if the dependency
     * could not be created
     */
    public function get($interface, $id = null, array $arguments = null, array $exclude = null) {
        if (!is_string($interface) || !$interface) {
            throw new DependencyException('Could not get dependency: provided interface is empty or invalid');
        }

        if (!$id && !$arguments && isset($this->instances[$interface]) && !is_array($this->instances[$interface])) {
            // an instance of this interface is manually set, return it
            return $this->instances[$interface];
        }

        $container = $this->getContainer();
        $dependencies = $container->getDependencies($interface);

        $instance = null;
        $dependency = null;

        if ($id !== null) {
            // gets a specific instance of the provided interface
            if (!is_string($id) || !$id) {
                throw new DependencyException('Could not get dependency for ' . $interface . ': provided id of the dependency is empty or invalid');
            }

            if (isset($this->instances[$interface][$id]) && $arguments === null) {
                // the instance is already created
                return $this->instances[$interface][$id];
            }

            if (!isset($dependencies[$id]) || isset($exclude[$dependencies[$id]->getClassName()][$id])) {
                throw new DependencyNotFoundException('Could not get dependency for ' . $interface . ': no injectable dependency available with id ' . $id);
            }

            $dependency = $dependencies[$id];
        } else {
            if (isset($this->instances[$interface][0])) {
                // return manually set instance
                return $this->instances[$interface][0];
            }

            // gets the last defined dependency which is not excluded
            do {
                $dependency = array_pop($dependencies);
                if (!$dependency) {
                    // no dependency found, check for undefined instance
                    $exception = null;

                    if (!isset($exclude[$interface][self::ID_UNDEFINED])) {
                        if (isset($this->instances[$interface][self::ID_UNDEFINED])) {
                            // undefined instance already created
                            $instance = $this->instances[$interface][self::ID_UNDEFINED];
                        } else {
                            // create undefined instance
                            try {
                                $instance = $this->createUndefined($interface, $arguments, $exclude);
                            } catch (Exception $e) {
                                $exception = $e;
                            }
                        }
                    }

                    if (!$instance) {
                        throw new DependencyNotFoundException('Could not get dependency for ' . $interface . ': no injectable dependency available', 0, $exception);
                    }

                    $id = self::ID_UNDEFINED;

                    break;
                }

                $id = $dependency->getId();
            } while ($dependency && isset($exclude[$dependency->getClassName()][$id]));

            if (isset($this->instances[$interface][$id]) && $arguments === null) {
                // the instance is already created
                return $this->instances[$interface][$id];
            }
        }

        // creates a new instance
        if (!$instance) {
            try {
                $instance = $this->create($interface, $dependency, $arguments, $exclude);
            } catch (Exception $exception) {
                throw new DependencyException('Could not get dependency for interface ' . $interface . ' with id ' . $id . ': instance could not be created', 0, $exception);
            }
        }

        if ($arguments !== null) {
            // arguments provided, act as factory and don't register the instance
            return $instance;
        }

        // register the instance
        if ($dependency) {
            $interfaces = $dependency->getInterfaces();
            $interfaces[$interface] = true;
        } else {
            $interfaces = array($interface => true);
        }

        foreach ($interfaces as $interface => $null) {
            if (!isset($this->instances[$interface])) {
                $this->instances[$interface] = array();
            }

            $this->instances[$interface][$id] = $instance;
        }

        // invoke defined calls
        if ($dependency) {
            $calls = $dependency->getCalls();
            if ($calls) {
                foreach ($calls as $call) {
                    $this->invokeCallback(array($instance, $call->getMethodName()), $call->getArguments(), $exclude);
                }
            }
        }

        // return instance
        return $instance;
    }

    /**
     * Creates an instance of the provided dependency
     * @param string $interface Full class name of the interface or parent class
     * @param Dependency $dependency Definition of the class to create
     * @param array $arguments Arguments for the constructor of the instance
     * @param array $exclude Array with the interface as key and an array with
     * id's of dependencies as key to exclude from the get calls.
     * @return mixed Instance of the dependency
     * @throws Exception when the dependency could not be created
     */
    protected function create($interface, Dependency $dependency, array $arguments = null, array $exclude = null) {
        $className = $dependency->getClassName();

        $this->addExclude($className, $dependency->getId(), $exclude);

        $reflectionArguments = $this->reflectionHelper->getArguments('__construct', $className);

        $constructorArguments = $dependency->getConstructorArguments();
        if ($constructorArguments === null) {
            $constructorArguments = array();
        }

        if ($arguments !== null) {
            foreach ($arguments as $name => $value) {
                $constructorArguments[$name] = $value;
            }
        }

        $arguments = $this->parseArguments($constructorArguments, $reflectionArguments, $exclude);

        return $this->reflectionHelper->createObject($className, $arguments, $interface);
    }

    /**
     * Attempts to create a undefined dependency
     * @param string $className
     * @param array $arguments
     * @param array $exclude
     * @return null|mixed Instance if succeeded, null otherwise
     */
    protected function createUndefined($className, array $arguments = null, array $exclude = null) {
        $this->addExclude($className, self::ID_UNDEFINED, $exclude);

        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isInterface()) {
            return null;
        }

        $reflectionArguments = $this->reflectionHelper->getArguments('__construct', $className);

        if ($arguments === null) {
            $arguments = array();
        }

        try {
            $arguments = $this->parseArguments($arguments, $reflectionArguments, $exclude);
        } catch (DependencyException $e) {
            throw new DependencyException('Could not create instance of ' . $className . ': arguments could not be parsed', 0, $e);
        }

        return $this->reflectionHelper->createObject($className, !$arguments ? null : $arguments);
    }

    /**
     * Invokes the provided callback
     * @param mixed $callback Callback to invoke
     * @param array|null $arguments Arguments for the callback
     * @param boolean $isDynamic Set to true if the callback has arguments
     * which are not in the signature
     * @return mixed Return value of the callback
     */
    public function invoke($callback, array $arguments = null, $isDynamic = false) {
        return $this->invokeCallback($callback, $arguments, null, $isDynamic);
    }

    /**
     * Invokes the provided callback in the dependency container
     * @param mixed $callback Callback to invoke
     * @param array|null $arguments Arguments for the callback
     * @param boolean $isDynamic Set to true if the callback has arguments
     * which are not in the signature
     * @return mixed Return value of the callback
     */
    protected function invokeCallback($callback, array $arguments = null, array $exclude = null, $isDynamic = false) {
        $this->setExclude($exclude);

        $callback = new Callback($callback);
        if (!$callback->isCallable()) {
            throw new ReflectionException('Could not invoke ' . $callback . ': callback not callable');
        }

        if ($arguments === null) {
            $arguments = array();
        }

        $callbackArguments = $this->reflectionHelper->getArguments($callback);

        try {
            $arguments = $this->parseArguments($arguments, $callbackArguments, $exclude, $isDynamic);
        } catch (DependencyException $exception) {
            throw new ReflectionException('Could not invoke ' . ($isDynamic ? 'dynamic ' : '') . $callback . ': could not parse arguments', 0, $exception);
        }

        return $callback->invokeWithArguments($arguments);
    }

    /**
     * Parses the provided arguments into the argument definition
     * @param array $arguments Provided arguments
     * @param array $definedArguments Argument definition
     * @return array Argument array ready for invokation
     */
    public function parseArguments(array $arguments, array $definedArguments, array $exclude = null, $isDynamic = false) {
        foreach ($definedArguments as $name => $argument) {
            if (isset($arguments[$name]) || array_key_exists($name, $arguments) !== false) {
                $argument = $arguments[$name];

                if ($argument instanceof DependencyCallArgument) {
                    $type = $argument->getType();
                    if (!isset($this->argumentParsers[$type])) {
                        throw new DependencyException('No argument parser set for type ' . $type);
                    }

                    $definedArguments[$name] = $this->argumentParsers[$type]->getValue($argument);
                } else {
                    $definedArguments[$name] = $argument;
                }

                unset($arguments[$name]);
            } elseif ($argument instanceof ReflectionParameter) {
                $definedArguments[$name] = $this->parseReflectionParameter($argument, $exclude);
            }
        }

        if ($arguments) {
            if ($isDynamic) {
                foreach ($arguments as $value) {
                    $definedArguments[] = $value;
                }
            } else {
                // more arguments provided then defined, throw exception
                $argumentNames = array();
                $argumentCount = 0;
                foreach ($arguments as $name => $value) {
                    $argumentNames[] = $name;
                    $argumentCount++;
                }

                $message = implode(', ', $argumentNames);
                if ($argumentCount == 1) {
                    $message .= ' is';
                } else {
                    $message .= ' are';
                }

                throw new DependencyException($message . ' not defined in the method signature');
            }
        }

        return $definedArguments;
    }

    /**
     * Parses a PHP reflection parameter argument
     * @param ReflectionParameter $argument
     * @param array $exclude
     * @return mixed Value for the argument
     * @throws DependencyException when the value could not be retrieved
     */
    protected function parseReflectionParameter(ReflectionParameter $argument, array $exclude = null) {
        if ($argument->isOptional()) {
            return $argument->getDefaultValue();
        }

        $exception = null;

        $argumentClass = $argument->getClass();
        if ($argumentClass) {
            try {
                return $this->get($argumentClass->getName(), null, null, $exclude);
            } catch (DependencyException $e) {
                $exception = $e;
            }
        }

        throw new DependencyException('Mandatory parameter ' . $argument->getName() . ' is not provided and could not be injected', 0, $exception);
    }

    /**
     * Adds a id to the exclude array
     * @param array $exclude Exclude list
     * @param string $interface Interface of the id
     * @param string $id Id to add
     * @return null
     */
    protected function addExclude($interface, $id, array &$exclude = null) {
        if (!$exclude) {
            $exclude = array($interface => array($id => true));
        } elseif (!isset($exclude[$interface])) {
            $exclude[$interface] = array($id => true);
        } else {
            $exclude[$interface][$id] = true;
        }

        $this->setExclude($exclude);
    }

    /**
     * Updates the argument parsers with the provided exclude array
     * @param array $exclude
     * @return null
     */
    protected function setExclude(array $exclude = null) {
        foreach ($this->argumentParsers as $argumentParser) {
            if ($argumentParser instanceof InjectableArgumentParser) {
                $argumentParser->setDependencyInjector($this);
                $argumentParser->setExclude($exclude);
            }
        }
    }

}