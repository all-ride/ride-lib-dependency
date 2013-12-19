<?php

namespace pallo\library\dependency;

use pallo\library\reflection\ReflectionHelper;

use \PHPUnit_Framework_TestCase;

class DependencyInjectorTest extends PHPUnit_Framework_TestCase {

    private $di;

    public function setUp() {
        $this->di = new DependencyInjector();
    }

    public function testGetReflectionHelper() {
        $reflectionHelper = $this->di->getReflectionHelper();

        $this->assertTrue($reflectionHelper instanceof ReflectionHelper);

        $di = new DependencyInjector(null, $reflectionHelper);

        $this->assertTrue($reflectionHelper === $di->getReflectionHelper());
    }

    public function testSetAndGetArgumentParser() {
        $parsers = $this->di->getArgumentParsers();
        $this->assertTrue(isset($parsers['null']));

        $this->di->setArgumentParser('null', null);

        $parsers = $this->di->getArgumentParsers();
        $this->assertFalse(isset($parsers['null']));

        $this->di->setArgumentParser('null', new \pallo\library\dependency\argument\NullArgumentParser());

        $parsers = $this->di->getArgumentParsers();
        $this->assertTrue(isset($parsers['null']));
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testSetArgumentParserThrowsExceptionWhenInvalidTypeProvided() {
        $this->di->setArgumentParser(null, null);
    }

    public function testGet() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);

        $token = $instance->getToken();

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);
        $this->assertEquals($token, $instance->getToken()); // it's the same instance
    }

    public function testGetUsesTheLastDefinedDependency() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $dependency1 = new Dependency('pallo\\library\\dependency\\Dummy');
        $dependency1->addInterface($interface);
        $dependency2 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency2->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency1);
        $container->addDependency($dependency2);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);
    }

    public function testGetWithId() {
        $interface = 'pallo\\library\\dependency\\TestInterface';
        $id = 'id';

        $dependency1 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency1->addInterface($interface);
        $dependency1->setId($id);
        $dependency2 = new Dependency('pallo\\library\\dependency\\Dummy');
        $dependency2->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency1);
        $container->addDependency($dependency2);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface, $id);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);
    }

    public function testGetCallsConstructor() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token = 'test';

        $construct = new DependencyCall('__construct');
        $construct->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token)));

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);
        $dependency->addCall($construct);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);
        $this->assertEquals($token, $instance->getToken());
    }

    public function testGetCallsMethods() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token1 = 'test1';
        $token2 = 'test2';

        $method1 = new DependencyCall('setToken');
        $method1->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token1)));
        $method2 = new DependencyCall('setToken');
        $method2->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token2)));

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);
        $dependency->addCall($method1);
        $dependency->addCall($method2);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);

        $this->assertEquals($token2, $instance->getToken());

        $history = $instance->getTokenHistory();
        $this->assertEquals(2, count($history));
        $this->assertEquals($token1, $history[1]);
    }

    public function testGetInjectsDependencies() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token = 'test';

        $construct = new DependencyCall('__construct');
        $construct->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token)));
        $method1 = new DependencyCall('setTest');
        $method1->addArgument(new DependencyCallArgument('test', 'dependency', array('interface' => $interface)));

        $dependency1 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency1->addInterface($interface);
        $dependency1->addCall($construct);
        $dependency2 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency2->addInterface($interface);
        $dependency2->addCall($method1);

        $container = new DependencyContainer();
        $container->addDependency($dependency1);
        $container->addDependency($dependency2);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);

        $test = $instance->getTest();

        $this->assertNotNull($test);
        $this->assertEquals($token, $test->getToken());
    }

    public function testGetWithRecursiveInterface() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token = 'test';

        $construct1 = new DependencyCall('__construct');
        $construct1->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_DEPENDENCY, array('interface' => $interface)));
        $construct2 = new DependencyCall('__construct');
        $construct2->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_DEPENDENCY, array('interface' => $interface . "2")));

        $dependency1 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency1->addInterface($interface);
        $dependency1->addInterface($interface . '2');
        $dependency1->addCall($construct1);
        $dependency2 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency2->addInterface($interface);
        $dependency2->addCall($construct2);
        $dependency3 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency3->addInterface($interface);
        $dependency3->addInterface($interface . '2');

        $container = new DependencyContainer();
        $container->addDependency($dependency3);
        $container->addDependency($dependency2);
        $container->addDependency($dependency1);

        $this->di->setContainer($container);

        $instance = $this->di->get($interface);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof TestObject);
        $this->assertTrue($instance->getToken() instanceof TestObject);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetThrowsExceptionWhenClassInInvalid() {
        // not implemented and unexistant interface
        $interface = 'pallo\\library\\dependency\\TestInterface3';

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container);
        $this->di->get($interface);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetThrowsExceptionWhenAllInstancesAreExcluded() {
        // not implemented and unexistant interface
        $interface = 'pallo\\library\\dependency\\TestInterface';
        $class = 'pallo\\library\\dependency\\TestObject';

        $dependency = new Dependency($class);
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $exclude = array($class => array($dependency->getId() => true));

        $this->di->setContainer($container);
        $this->di->get($interface, null, null, $exclude);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetWithIdThrowsExceptionWhenAllInstancesAreExcluded() {
        // not implemented and unexistant interface
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $exclude = array($interface => array($dependency->getId() => true));

        $this->di->setContainer($container);
        $this->di->get($interface, $dependency->getId(), null, $exclude);
    }

    public function testSetInstance() {
        $interface = 'pallo\\library\\dependency\\TestInterface';
        $interface2 = 'pallo\\library\\dependency\\TestInterface2';

        $id = 'id1';
        $id2 = 'id2';
        $id3 = 'id3';

        $instance1 = new TestObject();
        $instance2 = new TestObject();
        $instance3 = new TestObject();
        $instance4 = new TestObject();
        $instance5 = new TestObject();
        $instance6 = new TestObject();

        $this->di->setInstance($instance1);
        $this->di->setInstance($instance6);
        $this->di->setInstance($instance2, $interface);
        $this->di->setInstance($instance3, null, $id);
        $this->di->setInstance($instance4, null, $id2);
        $this->di->setInstance($instance5, $interface2, $id3);

        $expected = array(
            'pallo\\library\\dependency\\TestObject' => array(
                $instance6,
                $id => $instance3,
                $id2 => $instance4,
            ),
            $interface => $instance2,
            $interface2 => array(
                $id3 => $instance5,
            ),
        );

        $this->assertEquals($expected, $this->di->getInstances());
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testSetInstanceThrowsExceptionWhenInvalidObjectProvided() {
        $this->di->setInstance('test');
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testSetInstanceThrowsExceptionWhenInvalidInterfaceProvided() {
        $this->di->setInstance($this, $this);
    }

    public function testUnsetInstance() {
        $interface = 'pallo\\library\\dependency\\TestInterface';
        $interface2 = 'pallo\\library\\dependency\\TestInterface2';

        $id = 'id1';
        $id2 = 'id2';
        $id3 = 'id3';

        $instance1 = new TestObject();
        $instance2 = new TestObject();
        $instance3 = new TestObject();
        $instance4 = new TestObject();
        $instance5 = new TestObject();

        $this->di->setInstance($instance1);
        $this->di->setInstance($instance2, $interface);
        $this->di->setInstance($instance3, null, $id);
        $this->di->setInstance($instance4, null, $id2);
        $this->di->setInstance($instance5, $interface2, $id3);

        $this->di->unsetInstance('pallo\\library\\dependency\\TestObject', $id);
        $this->di->unsetInstance($interface);
        $result1 = $this->di->unsetInstance($interface2, $id3);
        $result2 = $this->di->unsetInstance($interface2);

        $expected = array(
            'pallo\\library\\dependency\\TestObject' => array(
                $id2 => $instance4,
                0 => $instance1,
            ),
        );

        $this->assertTrue($result1);
        $this->assertFalse($result2);
        $this->assertEquals($expected, $this->di->getInstances());
    }

    public function testGetInstances() {
        $interface = 'test';
        $instance = new TestObject();
        $id = 'id';

        $this->di->setInstance($instance, $interface, $id);

        $this->assertEquals(array(), $this->di->getInstances('unexistant'));
        $this->assertEquals(array($id => $instance), $this->di->getInstances($interface));
    }

    public function testGetWithSetInstance() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token = 'test';

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);
        $instance = new TestObject($token);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container);

        $result = $this->di->get($interface);

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof $interface);
        $this->assertNotEquals($token, $result->getToken());

        $this->di->setInstance($instance, $interface);

        $result = $this->di->get($interface);

        $this->assertEquals($instance, $result);
    }

    public function testGetActAsFactory() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token = 'test';
        $methodToken = 'called';
        $constructToken = 'construct';

        $construct = new DependencyCall('__construct');
        $construct->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $constructToken)));
        $method = new DependencyCall('setToken');
        $method->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $methodToken)));

        $dependency = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency->addInterface($interface);
        $dependency->addCall($construct);
        $dependency->addCall($method);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $this->di->setContainer($container, true);

        $result1 = $this->di->get($interface, null, array('token' => $token));

        $this->assertNotNull($result1);
        $this->assertTrue($result1 instanceof $interface);
        $this->assertEquals($token, $result1->getToken(), 'The construct argument is not used');
        $this->assertNull($this->di->getInstances());

        $result2 = $this->di->get($interface, null, array());
        $this->assertTrue($result1 !== $result2);
        $this->assertTrue($constructToken == $result2->getToken());
        $this->assertNotEquals($methodToken, $result2->getToken());
    }

    public function testGetAttemptsToCreateUndefinedClasses() {
        $result = $this->di->get('pallo\\library\\dependency\\TestObject2');

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof TestObject2);
        $this->assertTrue($result->getObject() instanceof TestObject);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetAttemptsToCreateUndefinedInterfaceThrowsException() {
        $this->di->get('pallo\\library\\dependency\\TestInterface');
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetThrowsExceptionWhenInvalidInterfaceProvided() {
        $this->di->get($this);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetThrowsExceptionWhenInvalidIdProvided() {
        $this->di->get('test', $this);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyNotFoundException
     */
    public function testGetThrowsExceptionWhenDependencyNotFound() {
        $result = $this->di->get('test');
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyNotFoundException
     */
    public function testGetWithIdThrowsExceptionWhenDependencyNotFound() {
        $this->di->get('test', 'id');
    }

    public function testGetReturnsPreviouslySetInstance() {
        $interface = 'test';
        $instance = new TestObject();

        $this->di->setInstance($instance, $interface);

        $result = $this->di->get($interface);

        $this->assertEquals($result, $instance);

        $interface = 'test2';
        $id = 'id';

        $this->di->setInstance($instance, $interface, $id);

        $result = $this->di->get($interface, $id);

        $this->assertEquals($result, $instance);
    }

    public function testGetAll() {
        $interface = 'pallo\\library\\dependency\\TestInterface';

        $token1 = 'test1';
        $token2 = 'test2';
        $id = 'id';

        $construct1 = new DependencyCall('__construct');
        $construct1->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token1)));
        $construct2 = new DependencyCall('__construct');
        $construct2->addArgument(new DependencyCallArgument('token', DependencyInjector::TYPE_SCALAR, array('value' => $token2)));

        $dependency1 = new Dependency('pallo\\library\\dependency\\TestObject', $id);
        $dependency1->addCall($construct1);
        $dependency1->addInterface($interface);
        $dependency2 = new Dependency('pallo\\library\\dependency\\TestObject');
        $dependency2->addInterface($interface);
        $dependency2->addCall($construct2);

        $container = new DependencyContainer();
        $container->addDependency($dependency1);
        $container->addDependency($dependency2);

        $this->di->setContainer($container);

        $expected = array(
            $id => new TestObject($token1),
            'd1' => new TestObject($token2),
        );

        $result = $this->di->getAll($interface);

        $this->assertEquals($expected, $result);
    }

    public function testParseArguments() {
        $reflectionHelper = new ReflectionHelper();
        $definedArguments = $reflectionHelper->getArguments('str_repeat');

        $arguments = array(
            'mult' => 3,
            'input' => 'test'
        );

        $expected = array(
            'input' => 'test',
            'mult' => 3,
        );

        $result = $this->di->parseArguments($arguments, $definedArguments);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testParseArgumentsThrowsExceptionWhenRequiredParameterNotProvided() {
        $reflectionHelper = new ReflectionHelper();
        $definedArguments = $reflectionHelper->getArguments('str_repeat');

        $arguments = array(
            'input' => 'test'
        );

        $this->di->parseArguments($arguments, $definedArguments);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testParseArgumentsThrowsExceptionWhenInvalidArgumentProvided() {
        $arguments = array(
            'test' => 'test',
        );

        $this->di->parseArguments($arguments, array());
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testParseArgumentsThrowsExceptionWhenInvalidArgumentsProvided() {
        $arguments = array(
            'test' => 'test',
            'test2' => 'test',
        );

        $this->di->parseArguments($arguments, array());
    }

    public function testParseArgumentsWithDependency() {
        $defined = array('test' => null, 'test2' => 2);

        $arguments = array(
            'test' => 'TEST',
            'test2' => new DependencyCallArgument('test2', 'null'),
        );

        $expected = array(
            'test' => 'TEST',
            'test2' => null,
        );

        $result = $this->di->parseArguments($arguments, $defined);

        $this->assertEquals($result, $expected);
    }

    public function testParseDynamicArguments() {
        $defined = array('test' => null, 'test2' => 2);

        $arguments = array(
            'test' => 'TEST',
            'test3' => 'test3',
            'test2' => new DependencyCallArgument('test2', 'null'),
            'test4' => 'test4',
        );

        $expected = array(
            'test' => 'TEST',
            'test2' => null,
            0 => 'test3',
            1 => 'test4',
        );

        $result = $this->di->parseArguments($arguments, $defined, null, true);

        $this->assertEquals($result, $expected);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testParseArgumentsThrowsExceptionWhenInvalidArgumentTypeProvided() {
        $arguments = array(
            'test' => new DependencyCallArgument('test', 'unexistantType'),
        );

        $this->di->parseArguments($arguments, $arguments);
    }

    public function testInvoke() {
        $time = time();
        $result = $this->di->invoke('time');

        $this->assertEquals($time, $result);
    }

    /**
     * @expectedException pallo\library\reflection\exception\ReflectionException
     */
    public function testInvokeThrowsExceptionWhenCallbackNotCallable() {
        $this->di->invoke(array($this, 'unexistantMethod'));
    }

    /**
     * @expectedException pallo\library\reflection\exception\ReflectionException
     */
    public function testInvokeThrowsExceptionWhenInvalidArgumentsProvided() {
        $this->di->invoke(array($this, 'testInvokeThrowsExceptionWhenInvalidArgumentsProvided'), array('test' => 'test'));
    }

}

interface TestInterface {

    public function method();

}

interface TestInterface2 {

    public function method2();

}

class TestObject implements TestInterface, TestInterface2 {

    private $token;

    private $tokenHistory = array();

    private $test;

    public function __construct($token = null) {
        if ($token) {
            $this->setToken($token);
        } else {
            $this->setToken(rand(10000, 99999));
        }
    }

    public function setToken($token) {
        if ($this->token) {
            $this->tokenHistory[] = $this->token;
        }

        $this->token = $token;
    }

    public function getToken() {
        return $this->token;
    }

    public function getTokenHistory() {
        return $this->tokenHistory;
    }

    public function setTest(TestInterface $test) {
        $this->test = $test;
    }

    public function getTest() {
        return $this->test;
    }

    public function method() {

    }

    public function method2() {

    }

}

class TestObject2 {

    public function __construct(TestObject $object) {
        $this->object = $object;
    }

    public function getObject() {
        return $this->object;
    }

}