<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyContainer;
use ride\library\dependency\DependencyInjector;
use ride\library\dependency\Dependency;

use \PHPUnit_Framework_TestCase;

class CallArgumentParserTest extends PHPUnit_Framework_TestCase {

    public function testGetValueWithFunction() {
        $dependencyInjector = new DependencyInjector();

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('function' => 'sys_get_temp_dir');
        $argument = new DependencyCallArgument('name', 'call', $data);

        $result = $parser->getValue($argument);

        $this->assertNotNull($result);
        $this->assertTrue(is_string($result));
    }

    public static function staticMethod() {
        return 'static';
    }

    public function testGetValueWithStaticMethod() {
        $dependencyInjector = new DependencyInjector();

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('class' => __CLASS__, 'method' => 'staticMethod');
        $argument = new DependencyCallArgument('name', 'call', $data);

        $result = $parser->getValue($argument);

        $this->assertEquals('static', $result);
    }

    public function testGetValueWithDependency() {
        $interface = 'ride\\library\\dependency\\argument\\TestInterface';
        $id = 'id';

        $dependency = new Dependency('ride\\library\\dependency\\argument\\TestObject');
        $dependency->setId($id);
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $dependencyInjector = new DependencyInjector($container);

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('interface' => $interface, 'id' => $id, 'method' => 'getToken');
        $argument = new DependencyCallArgument('name', 'call', $data);

        $result = $parser->getValue($argument);

        $this->assertNotNull($result);
        $this->assertTrue(is_numeric($result));
    }

    public function testGetValueWithArguments() {
        $dependencyInjector = new DependencyInjector();

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $format = 'Y-m-d';
        $time = time();

        $data = array('function' => 'date', 'arguments' => array('format' => $format, 'timestamp' => $time));
        $argument = new DependencyCallArgument('name', 'call', $data);

        $result = $parser->getValue($argument);

        $this->assertEquals(date($format, $time), $result);
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetValueThrowsExceptionWhenInvalidTypeProvided() {
        $dependencyInjector = new DependencyInjector();

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('unknown' => 'sys_get_temp_dir');
        $argument = new DependencyCallArgument('name', 'call', $data);

        $parser->getValue($argument);
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetValueThrowsExceptionWhenNoMethodProvided() {
        $interface = 'ride\\library\\dependency\\argument\\TestInterface';

        $dependency = new Dependency('ride\\library\\dependency\\argument\\TestObject');
        $dependency->addInterface($interface);

        $container = new DependencyContainer();
        $container->addDependency($dependency);

        $dependencyInjector = new DependencyInjector($container);

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('interface' => $interface);
        $argument = new DependencyCallArgument('name', 'call', $data);

        $parser->getValue($argument);
    }

    /**
     * @dataProvider providerGetValueThrowsExceptionWhenInvalidArgumentsProvided
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetValueThrowsExceptionWhenInvalidArgumentsProvided($arguments) {
        $dependencyInjector = new DependencyInjector();

        $parser = new CallArgumentParser();
        $parser->setDependencyInjector($dependencyInjector);

        $data = array('function' => 'sys_get_temp_dir', 'arguments' => $arguments);
        $argument = new DependencyCallArgument('name', 'call', $data);

        $parser->getValue($argument);
    }

    public function providerGetValueThrowsExceptionWhenInvalidArgumentsProvided() {
        return array(
            array('test'),
            array(5),
            array($this),
        );
    }

}

interface TestInterface {

    public function method();

}

class TestObject implements TestInterface {

    private $token;

    public function __construct($token = null) {
        if ($token) {
            $this->setToken($token);
        } else {
            $this->setToken(rand(10000, 99999));
        }
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getToken() {
        return $this->token;
    }

    public function method() {

    }

}