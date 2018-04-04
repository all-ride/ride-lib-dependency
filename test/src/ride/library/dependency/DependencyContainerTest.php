<?php

namespace ride\library\dependency;

use PHPUnit\Framework\TestCase;

class DependencyContainerTest extends TestCase {

    public function testConstruct() {
        $container = new DependencyContainer();

        $this->assertNotNull($container);
    }

    public function testGetDependencies() {
        $container = new DependencyContainer();

        $this->assertEquals(array(), $container->getDependencies());
        $this->assertEquals(array(), $container->getDependencies('interface'));
    }

    public function testRemoveDependency() {
        $container = new DependencyContainer();
        $className = 'className';
        $for = 'foo';
        $id = 'd0';
        $dependency = new Dependency($className);
        $dependency->addInterface($for);

        $container->addDependency($dependency);

        $this->assertTrue($container->removeDependency($for, $id));
    }

    public function testRemoveDependencyShouldReturnFalse() {
        $container = new DependencyContainer();
        $className = 'className';
        $for = 'foo';
        $id = 'd0';
        $dependency = new Dependency($className);
        $dependency->addInterface($for);

        $container->addDependency($dependency);

        $this->assertFalse($container->removeDependency('bar', 'd1'));    
    }

    public function testGetDependenciesByTag() {
        $container = new DependencyContainer();
        $className = 'className';
        $for = 'foo';
        $id = 'd0';

        $this->assertSame(array(), $container->getDependenciesByTag($for, 'do', 'd1'));    
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
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testAddDependencyWithoutInterfacesThrowsException() {
        $dependency = new Dependency('className');

        $container = new DependencyContainer();
        $container->addDependency($dependency);
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetDependenciesWithInvalidInterfaceThrowsException() {
        $container = new DependencyContainer();
        $container->getDependencies($this);
    }

}