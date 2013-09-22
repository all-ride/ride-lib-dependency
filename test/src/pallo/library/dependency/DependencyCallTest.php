<?php

namespace pallo\library\dependency;

use \PHPUnit_Framework_TestCase;

class DependencyCallTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $methodName = 'methodName';

        $call = new DependencyCall($methodName);

        $this->assertEquals($methodName, $call->getMethodName());

        $arguments = $call->getArguments();

        $this->assertNull($arguments);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testConstructThrowsExceptionWithInvalidMethodNameProvided() {
    	new DependencyCall($this);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testConstructThrowsExceptionWithInvalidIdProvided() {
    	new DependencyCall('strlen', $this);
    }

    public function testArguments() {
        $name = 'name';
        $argument = new DependencyCallArgument($name, 'type');
        $call = new DependencyCall('methodName');

        $call->addArgument($argument);
        $expected = array($name => $argument);

        $this->assertEquals($expected, $call->getArguments());

        $name2 = 'name2';
        $argument2 = new DependencyCallArgument($name2, 'type');

        $call->addArgument($argument2);
        $expected[$name2] = $argument2;

        $this->assertEquals($expected, $call->getArguments());

        $call->clearArguments();

        $this->assertNull($call->getArguments());
    }

}