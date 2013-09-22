<?php

namespace pallo\library\dependency;

use \PHPUnit_Framework_TestCase;

class DependencyCallArgumentTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $name = 'number';
        $type = 'value';
        $properties = array(
            'value' => 4,
        );

        $argument = new DependencyCallArgument($name, $type, $properties);

        $this->assertEquals($name, $argument->getName());
        $this->assertEquals($type, $argument->getType());
        $this->assertEquals($properties, $argument->getProperties());
    }

    public function testToString() {
        $name = 'number';
        $type = 'value';
        $properties = array(
            'value' => 4,
        );

        $argument = new DependencyCallArgument($name, $type, $properties);

        $this->assertEquals("\$number value {\n    value: 4\n}", (string) $argument);
    }

    /**
     * @dataProvider providerSetNameThrowsExceptionWhenInvalidValuePassed
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testSetNameThrowsExceptionWhenInvalidValuePassed($name) {
        new DependencyCallArgument($name, 'type');
    }

    public function providerSetNameThrowsExceptionWhenInvalidValuePassed() {
        return array(
            array(''),
            array(null),
            array(array()),
            array($this),
        );
    }

    /**
     * @dataProvider providerSetTypeThrowsExceptionWhenInvalidValuePassed
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testSetTypeThrowsExceptionWhenInvalidValuePassed($type) {
        new DependencyCallArgument('name', $type);
    }

    public function providerSetTypeThrowsExceptionWhenInvalidValuePassed() {
        return array(
            array(''),
            array(null),
            array(array()),
            array($this),
        );
    }

    public function testGetProperty() {
        $name = 'number';
        $type = 'value';
        $properties = array(
            'value' => 4,
        );

        $argument = new DependencyCallArgument($name, $type, $properties);

        $this->assertEquals(4, $argument->getProperty('value'));
        $this->assertEquals('default', $argument->getProperty('unexistant', 'default'));
        $this->assertNull($argument->getProperty('unexistant'));
    }

}