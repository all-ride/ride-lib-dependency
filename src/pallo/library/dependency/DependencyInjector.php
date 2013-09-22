<?php

namespace pallo\library\dependency;

use pallo\library\dependency\argument\ArgumentParser;
use pallo\library\dependency\argument\ArrayArgumentParser;
use pallo\library\dependency\argument\CallArgumentParser;
use pallo\library\dependency\argument\DependencyArgumentParser;
use pallo\library\dependency\argument\InjectableArgumentParser;
use pallo\library\dependency\argument\NullArgumentParser;
use pallo\library\dependency\argument\ScalarArgumentParser;
use pallo\library\dependency\exception\DependencyException;
use pallo\library\dependency\exception\DependencyNotFoundException;
use pallo\library\reflection\Callback;
use pallo\library\reflection\ObjectFactory;

use \Exception;
use \ReflectionParameter;

/**
 * Implementation of a dependency injector. Load class instances dynamically
 * from a dependency container when and only when needed.
 */
class DependencyInjector {

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
    public function __construct(DependencyContainer $container = null, ObjectFactory $objectFactory = null) {
        if (!$container) {
            $container = new DependencyContainer();
        }
        if (!$objectFactory) {
            $objectFactory = new ObjectFactory();
        }

        $this->objectFactory = $objectFactory;

    	$this->argumentParsers = array(
    		self::TYPE_NULL => new NullArgumentParser(),
    		self::TYPE_SCALAR => new ScalarArgumentParser(),
    		self::TYPE_ARRAY => new ArrayArgumentParser(),
    		self::TYPE_DEPENDENCY => new DependencyArgumentParser(),
    		self::TYPE_CALL => new CallArgumentParser(),
    	);

    	$this->container = $container;

    	$this->instances = array();
    }

    /**
     * Gets the object factory
     * @return pallo\library\reflection\ObjectFactory
     */
    public function getObjectFactory() {
    	return $this->objectFactory;
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
     * @param string $interface Interface to set the instance for, if not provided
     * the class name of the instance will be used as interface
     * @param string $id Id of the instance
     * @return null
     * @throws Exception if the provided instance is not a object
     * @throws Exception if the provided interface is empty or invalid
     */
    public function setInstance($instance, $interface = null, $id = null) {
        if (!is_object($instance)) {
            throw new DependencyException('Provided instance is not an object');
        }

        if ($interface !== null) {
            if (!is_string($interface) || !$interface) {
                throw new DependencyException('Provided interface is empty or invalid');
            }
        } else {
            $interface = get_class($instance);
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
     * Gets all the defined instances of the provided class
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

            if (!isset($dependencies[$id])) {
                throw new DependencyNotFoundException('Could not get dependency for ' . $interface . ': no injectable dependency set for ' . $interface . ' with id ' . $id);
            }

            $dependency = $dependencies[$id];
        } else {
            if ($arguments === null && isset($this->instances[$interface])) {
                // already a instance of the interface set
                $instances = array_reverse($this->instances[$interface]);

                // gets the last created dependency which is not excluded
                do {
                    $instance = each($instances);
                    if (!$instance) {
                        break;
                    }

                    $id = $instance[0];
                    $instance = $instance[1];
                } while (isset($exclude[$interface][$id]));

                if ($instance) {
                    // there is a dependency created which is not excluded
                    return $instance;
                }
            }

            // gets the last defined dependency which is not excluded
            do {
                $dependency = array_pop($dependencies);
                if (!$dependency) {
                    throw new DependencyNotFoundException('Could not get dependency for ' . $interface . ': no injectable dependency available');
                }

                $id = $dependency->getId();
            } while (isset($exclude[$interface][$id]));
        }

        // creates a new instance
        try {
            $instance = $this->create($interface, $dependency, $arguments, $exclude);
        } catch (Exception $exception) {
            throw new DependencyException('Could not get dependency for interface ' . $interface . ' with id ' . $id . ': instance could not be created', 0, $exception);
        }

        if ($arguments !== null) {
            // arguments provided, act as factory and don't store the instance
            return $instance;
        }

        $interfaces = $dependency->getInterfaces();
        $interfaces[$interface] = true;

        foreach ($interfaces as $interface => $null) {
            // index this interface
            if (!isset($this->instances[$interface])) {
                $this->instances[$interface] = array();
            }

            $this->instances[$interface][$id] = $instance;
        }

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
        if (!$exclude) {
            $exclude = array($interface => array($dependency->getId() => true));
        } elseif (!isset($exclude[$interface])) {
            $exclude[$interface] = array($dependency->getId() => true);
        } else {
            $exclude[$interface][$dependency->getId()] = true;
        }

        foreach ($this->argumentParsers as $argumentParser) {
            if ($argumentParser instanceof InjectableArgumentParser) {
                $argumentParser->setDependencyInjector($this);
                $argumentParser->setExclude($exclude);
            }
        }

        $className = $dependency->getClassName();

        $reflectionHelper = $this->objectFactory->getReflectionHelper();
        $reflectionArguments = $reflectionHelper->getArguments($className);

        $constructorArguments = $dependency->getConstructorArguments();
        $constructorArguments = $this->getCallbackArguments($constructorArguments, $exclude);
        $constructorArguments = $this->parseReflectionArguments($constructorArguments, $reflectionArguments);
        if ($arguments !== null) {
            $arguments = $this->parseReflectionArguments($arguments, $constructorArguments);
            $invokeCalls = false;
        } else {
            $arguments = $constructorArguments;
            $invokeCalls = true;
        }

        $instance = $this->objectFactory->createObject($className, $interface, !$arguments ? null : $arguments);

        if (!$invokeCalls) {
            return $instance;
        }

        $calls = $dependency->getCalls();
        if ($calls) {
            foreach ($calls as $call) {
                $callback = new Callback(array($instance, $call->getMethodName()));
                $callbackArguments = $reflectionHelper->getArguments($callback);

                $arguments = $this->getCallbackArguments($call->getArguments());
                $arguments = $this->parseReflectionArguments($arguments, $callbackArguments);

                $callback->invokeWithArrayArguments($arguments);
            }
        }

        return $instance;
    }

    /**
     * Parses the provided arguments into the argument definition
     * @param array $arguments Provided arguments
     * @param array $definedArguments Argument definition
     * @return array Argument array ready for invokation
     */
    public function parseReflectionArguments(array $arguments, array $definedArguments) {
        foreach ($definedArguments as $argumentName => $argument) {
            if (isset($arguments[$argumentName]) || array_key_exists($argumentName, $arguments) !== false) {
                $definedArguments[$argumentName] = $arguments[$argumentName];
                unset($arguments[$argumentName]);
            } elseif ($argument instanceof ReflectionParameter) {
            	if (!$argument->isOptional()) {
		            throw new DependencyException('Mandatory parameter ' . $argumentName . ' is not provided');
				} else {
					$definedArguments[$argumentName] = $argument->getDefaultValue();
	            }
            }
        }

        if ($arguments) {
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

        return $definedArguments;
    }

    /**
     * Gets the actual values of the provided arguments
     * @param array $arguments Array of dependency call arguments
     * @return array Array with the values of the call arguments
     * @see DependencyCallArgument
     */
    public function getCallbackArguments(array $arguments = null) {
        $callArguments = array();

        if ($arguments === null) {
            return $callArguments;
        }

        foreach ($arguments as $name => $argument) {
        	if ($argument instanceof DependencyCallArgument) {
	        	$type = $argument->getType();
	        	if (!isset($this->argumentParsers[$type])) {
	        		throw new DependencyException('No argument parser set for type ' . $type);
	        	}

	        	$callArguments[$name] = $this->argumentParsers[$type]->getValue($argument);
        	} else {
        		$callArguments[$name] = $argument;
        	}
        }

        return $callArguments;
    }

}