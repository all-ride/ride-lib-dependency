<?php

namespace pallo\library\dependency;

use \PHPUnit_Framework_TestCase;

class DependencyContainerTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $container = new DependencyContainer();

        $this->assertNotNull($container);
    }

    public function testGetDependencies() {
        $container = new DependencyContainer();

        $this->assertEquals(array(), $container->getDependencies());
        $this->assertEquals(array(), $container->getDependencies('interface'));
    }

    public function testAddDependency() {
        $container = new DependencyContainer();

        $for = 'foo';
        $className = 'className';
        $id = 'd0';
        $dependency = new Dependency($className);
        $dependency->addInterface($for);

        $container->addDependency($dependency);
        $expected = array($for => array($id => $dependency));

        $this->assertEquals($expected, $container->getDependencies());
        $this->assertEquals($id, $dependency->getId());

        $id = 'id';
        $dependency = clone $dependency;
        $dependency->setId($id);
        $container->addDependency($dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());

        $dependency = clone $dependency;
        $dependency->removeInterface($for);
        $for = "bar";
        $dependency->addInterface($for);
        $container->addDependency($dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());

        $dependency = clone $dependency;
        $dependency->removeInterface($for);
        $for = "foo";
        $dependency->addInterface($for);
        $dependency->setId();
        $container->addDependency($dependency);
        $expected[$for]['d2'] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());
        $this->assertEquals('d2', $dependency->getId());
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testAddDependencyWithoutInterfacesThrowsException() {
    	$dependency = new Dependency('className');

    	$container = new DependencyContainer();
    	$container->addDependency($dependency);
    }

    /**
     * @expectedException pallo\library\dependency\exception\DependencyException
     */
    public function testGetDependenciesWithInvalidInterfaceThrowsException() {
    	$container = new DependencyContainer();
		$container->getDependencies($this);
    }

}