<?php

namespace ride\library\dependency;

use \PHPUnit_Framework_TestCase;

class DependencyConstructCallTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $interface = 'interface';
        $methodName = 'methodName';

        $call = new DependencyConstructCall($interface, $methodName);

        $this->assertEquals($interface, $call->getInterface());
        $this->assertEquals($methodName, $call->getMethodName());
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testConstructThrowsExceptionWithInvalidInterfaceProvided() {
        new DependencyConstructCall($this, 'method');
    }

}
